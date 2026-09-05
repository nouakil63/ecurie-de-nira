<?php
/**
 * Endpoints AJAX publics utilisés par le widget de réservation.
 * Protégés par nonce `nira_booking`.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Ajax {

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $routes = [
            'nira_get_calendar'  => 'get_calendar',
            'nira_get_quote'     => 'get_quote',
            'nira_create_hold'   => 'create_hold',
            'nira_refresh_nonce' => 'refresh_nonce',
        ];
        foreach ( $routes as $action => $method ) {
            add_action( "wp_ajax_{$action}",        [ $this, $method ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, $method ] );
        }
    }

    private function verify() {
        if ( ! check_ajax_referer( 'nira_booking', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => __( 'Jeton de sécurité invalide.', 'nira-booking' ) ], 403 );
        }
        // Empêche tout proxy/CDN/cache de stocker les réponses AJAX
        // (sinon le calendrier affiche d'anciens prix après ajout d'une règle).
        nocache_headers();
        if ( ! headers_sent() ) {
            header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
            header( 'Pragma: no-cache' );
        }
    }

    /**
     * Renvoie un nonce frais. Public et — par nature — NON protégé par nonce.
     * Permet au widget de récupérer un jeton valide même lorsque la page HTML
     * est servie depuis un cache (CDN, plugin de cache) et embarque un nonce
     * expiré. Réponse jamais mise en cache.
     */
    public function refresh_nonce() {
        nocache_headers();
        if ( ! headers_sent() ) {
            header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
            header( 'Pragma: no-cache' );
        }
        wp_send_json_success( [ 'nonce' => wp_create_nonce( 'nira_booking' ) ] );
    }

    public function get_calendar() {
        $this->verify();
        $slug   = sanitize_text_field( $_POST['slug'] ?? '' );
        $month  = sanitize_text_field( $_POST['month'] ?? date( 'Y-m' ) );
        $months = max( 1, min( 4, (int) ( $_POST['months'] ?? 2 ) ) );

        $property = Nira_Properties::instance()->get_by_slug( $slug );
        if ( ! $property || 'active' !== $property->status ) {
            wp_send_json_error( [ 'message' => __( 'Hébergement introuvable.', 'nira-booking' ) ], 404 );
        }

        wp_send_json_success( [
            'property' => [
                'id'            => (int) $property->id,
                'slug'          => $property->slug,
                'name'          => $property->name,
                'base_price'    => (float) $property->base_price,
                'cleaning_fee'  => (float) $property->cleaning_fee,
                'capacity'      => (int) $property->capacity,
                'bedrooms'      => (int) $property->bedrooms,
                'min_nights'    => (int) $property->min_nights,
                'max_nights'    => (int) $property->max_nights,
                'deposit_pct'   => (int) $property->deposit_pct,
                'checkin_time'  => $property->checkin_time,
                'checkout_time' => $property->checkout_time,
            ],
            'months'   => Nira_Availability::calendar_payload( $property, $month, $months ),
        ] );
    }

    public function get_quote() {
        $this->verify();
        $slug      = sanitize_text_field( $_POST['slug'] ?? '' );
        $check_in  = sanitize_text_field( $_POST['check_in'] ?? '' );
        $check_out = sanitize_text_field( $_POST['check_out'] ?? '' );
        $guests    = max( 1, (int) ( $_POST['guests'] ?? 1 ) );

        $property = Nira_Properties::instance()->get_by_slug( $slug );
        if ( ! $property ) {
            wp_send_json_error( [ 'message' => __( 'Hébergement introuvable.', 'nira-booking' ) ], 404 );
        }
        if ( ! Nira_Booking::valid_date( $check_in ) || ! Nira_Booking::valid_date( $check_out ) ) {
            wp_send_json_error( [ 'message' => __( 'Dates invalides.', 'nira-booking' ) ], 400 );
        }
        if ( $check_in >= $check_out ) {
            wp_send_json_error( [ 'message' => __( 'Plage de dates invalide.', 'nira-booking' ) ], 400 );
        }
        if ( ! Nira_Availability::is_range_available( (int) $property->id, $check_in, $check_out ) ) {
            wp_send_json_error( [ 'message' => __( 'Ces dates ne sont pas disponibles.', 'nira-booking' ) ], 409 );
        }
        wp_send_json_success( Nira_Pricing::quote( $property, $check_in, $check_out, $guests ) );
    }

    public function create_hold() {
        $this->verify();

        $payload = [
            'property_id' => (int) ( $_POST['property_id'] ?? 0 ),
            'check_in'    => sanitize_text_field( $_POST['check_in'] ?? '' ),
            'check_out'   => sanitize_text_field( $_POST['check_out'] ?? '' ),
            'guest_name'  => sanitize_text_field( $_POST['guest_name'] ?? '' ),
            'guest_email' => sanitize_email( $_POST['guest_email'] ?? '' ),
            'guest_phone' => sanitize_text_field( $_POST['guest_phone'] ?? '' ),
            'guest_count' => (int) ( $_POST['guest_count'] ?? 1 ),
            'notes'       => sanitize_textarea_field( $_POST['notes'] ?? '' ),
            'source'      => 'direct',
        ];

        if ( empty( $payload['guest_email'] ) || empty( $payload['guest_name'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Nom et e-mail obligatoires.', 'nira-booking' ) ], 400 );
        }

        $booking_id = Nira_Booking::create( $payload );
        if ( is_wp_error( $booking_id ) ) {
            wp_send_json_error( [ 'message' => $booking_id->get_error_message() ], 400 );
        }

        $booking = Nira_Booking::get( $booking_id );
        $intent  = Nira_Stripe::create_payment_intent( $booking, Nira_Settings::get( 'charge_mode', 'deposit' ) );

        if ( is_wp_error( $intent ) ) {
            Nira_Booking::delete( $booking_id );
            wp_send_json_error( [ 'message' => $intent->get_error_message() ], 500 );
        }

        wp_send_json_success( [
            'booking_id'    => $booking_id,
            'reference'     => $booking->reference,
            'amount'        => $intent['amount'],
            'client_secret' => $intent['client_secret'],
        ] );
    }
}

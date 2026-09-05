<?php
/**
 * Intégration Stripe via REST (pas de SDK).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Stripe {

    const API_BASE = 'https://api.stripe.com/v1/';

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_webhook' ] );
    }

    public static function secret_key() {
        return trim( (string) Nira_Settings::get( 'stripe_sk' ) );
    }
    public static function public_key() {
        return trim( (string) Nira_Settings::get( 'stripe_pk' ) );
    }
    public static function webhook_secret() {
        return trim( (string) Nira_Settings::get( 'stripe_webhook_secret' ) );
    }

    private static function request( $method, $endpoint, $body = [] ) {
        $key = self::secret_key();
        if ( ! $key ) {
            return new WP_Error( 'nira_stripe_key', __( 'Clé secrète Stripe non configurée.', 'nira-booking' ) );
        }
        $args = [
            'method'  => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'timeout' => 20,
        ];
        if ( ! empty( $body ) ) {
            $args['body'] = http_build_query( $body );
        }
        $response = wp_remote_request( self::API_BASE . $endpoint, $args );
        if ( is_wp_error( $response ) ) {
            return $response;
        }
        $code = wp_remote_retrieve_response_code( $response );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( $code >= 400 ) {
            $msg = $data['error']['message'] ?? 'Stripe error';
            return new WP_Error( 'nira_stripe_error', $msg, $data );
        }
        return $data;
    }

    public static function create_payment_intent( $booking, $mode = 'deposit' ) {
        $amount = 'full' === $mode ? (float) $booking->total : (float) $booking->deposit;
        if ( $amount <= 0 ) {
            $amount = (float) $booking->total;
        }
        $body = [
            'amount'                              => (int) round( $amount * 100 ),
            'currency'                            => strtolower( $booking->currency ),
            'automatic_payment_methods[enabled]'  => 'true',
            'metadata[booking_id]'                => $booking->id,
            'metadata[reference]'                 => $booking->reference,
            'metadata[property_id]'               => $booking->property_id,
            'description'                         => sprintf( 'Écuries de Nira — %s', $booking->reference ),
            'receipt_email'                       => $booking->guest_email,
        ];

        $res = self::request( 'POST', 'payment_intents', $body );

        // Fallback : si le dashboard Stripe live n'a aucune méthode activée
        // (erreur "No valid payment method types"), on retente en spécifiant
        // explicitement la carte bancaire — qui marche partout.
        if ( is_wp_error( $res ) ) {
            $msg = $res->get_error_message();
            if ( false !== stripos( $msg, 'no valid payment method' )
              || false !== stripos( $msg, 'payment_method_types' ) ) {
                unset( $body['automatic_payment_methods[enabled]'] );
                $body['payment_method_types[]'] = 'card';
                $res = self::request( 'POST', 'payment_intents', $body );
            }
        }

        if ( is_wp_error( $res ) ) {
            return $res;
        }

        return [
            'client_secret'     => $res['client_secret'],
            'payment_intent_id' => $res['id'],
            'amount'            => $amount,
        ];
    }

    /**
     * Crée un PaymentIntent pour un montant arbitraire (utilisé pour le solde).
     */
    public static function create_intent_for_amount( $booking, $amount, $purpose = 'balance' ) {
        $amount = (float) $amount;
        if ( $amount <= 0 ) {
            return new WP_Error( 'nira_zero_amount', __( 'Montant invalide.', 'nira-booking' ) );
        }
        $body = [
            'amount'                              => (int) round( $amount * 100 ),
            'currency'                            => strtolower( $booking->currency ),
            'automatic_payment_methods[enabled]'  => 'true',
            'metadata[booking_id]'                => $booking->id,
            'metadata[reference]'                 => $booking->reference,
            'metadata[property_id]'               => $booking->property_id,
            'metadata[purpose]'                   => $purpose,
            'description'                         => sprintf( 'Écuries de Nira — %s (%s)', $booking->reference, $purpose ),
            'receipt_email'                       => $booking->guest_email,
        ];
        $res = self::request( 'POST', 'payment_intents', $body );
        if ( is_wp_error( $res ) ) {
            $msg = $res->get_error_message();
            if ( false !== stripos( $msg, 'no valid payment method' )
              || false !== stripos( $msg, 'payment_method_types' ) ) {
                unset( $body['automatic_payment_methods[enabled]'] );
                $body['payment_method_types[]'] = 'card';
                $res = self::request( 'POST', 'payment_intents', $body );
            }
        }
        if ( is_wp_error( $res ) ) return $res;
        return [
            'client_secret'     => $res['client_secret'],
            'payment_intent_id' => $res['id'],
            'amount'            => $amount,
        ];
    }

    /**
     * Récupère un PaymentIntent depuis l'API Stripe (source de vérité).
     * Sert à confirmer un paiement côté serveur sans dépendre du webhook.
     */
    public static function retrieve_payment_intent( $intent_id ) {
        $intent_id = sanitize_text_field( (string) $intent_id );
        if ( ! preg_match( '/^pi_[A-Za-z0-9]+$/', $intent_id ) ) {
            return new WP_Error( 'nira_bad_intent', __( 'Identifiant de paiement invalide.', 'nira-booking' ) );
        }
        return self::request( 'GET', 'payment_intents/' . rawurlencode( $intent_id ) );
    }

    public static function refund( $payment_intent, $amount, $currency = 'EUR' ) {
        if ( empty( $payment_intent ) ) {
            return new WP_Error( 'nira_no_intent', __( 'Aucun paiement à rembourser.', 'nira-booking' ) );
        }
        return self::request( 'POST', 'refunds', [
            'payment_intent' => $payment_intent,
            'amount'         => (int) round( $amount * 100 ),
        ] );
    }

    public function register_webhook() {
        register_rest_route( 'nira/v1', '/stripe-webhook', [
            'methods'             => 'POST',
            'permission_callback' => '__return_true',
            'callback'            => [ $this, 'handle_webhook' ],
        ] );
    }

    public function handle_webhook( WP_REST_Request $request ) {
        $payload = $request->get_body();
        $sig     = $request->get_header( 'stripe_signature' );
        $secret  = self::webhook_secret();

        // Signature OBLIGATOIRE : sans secret configuré ou sans en-tête signé,
        // on refuse tout — sinon n'importe qui peut forger un
        // payment_intent.succeeded et confirmer une réservation sans payer.
        if ( ! $secret || ! $sig || ! $this->verify_signature( $payload, $sig, $secret ) ) {
            error_log( '[Nira Stripe] Webhook rejeté : signature absente ou invalide' . ( $secret ? '' : ' (secret non configuré)' ) );
            return new WP_REST_Response( [ 'error' => 'signature' ], 400 );
        }

        $event = json_decode( $payload, true );
        if ( empty( $event['type'] ) ) {
            return new WP_REST_Response( [ 'error' => 'bad_payload' ], 400 );
        }

        $type = $event['type'];
        $obj  = $event['data']['object'] ?? [];

        if ( 'payment_intent.succeeded' === $type ) {
            $booking_id = (int) ( $obj['metadata']['booking_id'] ?? 0 );
            $amount     = (float) ( $obj['amount_received'] ?? 0 ) / 100;
            if ( $booking_id ) {
                $result = Nira_Booking::mark_paid( $booking_id, $obj['id'], $amount );
                if ( true === $result ) {
                    // Email uniquement au premier traitement (les webhooks Stripe
                    // peuvent être livrés plusieurs fois).
                    Nira_Email::send_confirmation( $booking_id );
                } elseif ( false === $result ) {
                    // Paiement encaissé mais réservation introuvable : alerte admin.
                    error_log( '[Nira Stripe] Paiement reçu pour réservation introuvable #' . $booking_id . ' (' . ( $obj['id'] ?? '?' ) . ')' );
                    wp_mail(
                        get_option( 'admin_email' ),
                        __( '[Nira] Paiement reçu sans réservation', 'nira-booking' ),
                        sprintf( "Un paiement Stripe a été reçu pour la réservation #%d mais celle-ci est introuvable.\nPaymentIntent : %s\nMontant : %s\nVérifiez le dashboard Stripe et recontactez le client.", $booking_id, $obj['id'] ?? '?', $amount )
                    );
                }
            }
        } elseif ( 'charge.refunded' === $type ) {
            // Remboursement fait depuis le dashboard Stripe : on répercute
            // sur la réservation — un remboursement total libère les dates.
            $pi      = sanitize_text_field( $obj['payment_intent'] ?? '' );
            $booking = $pi ? Nira_Booking::get_by_payment_intent( $pi ) : null;
            if ( $booking ) {
                $refunded = (float) ( $obj['amount_refunded'] ?? 0 ) / 100;
                $charged  = (float) ( $obj['amount'] ?? 0 ) / 100;
                $fully    = ! empty( $obj['refunded'] ) || ( $charged > 0 && $refunded + 0.001 >= $charged );
                Nira_Booking::record_external_refund( $booking, $refunded, $fully );
            }
            do_action( 'nira_stripe_refund_webhook', $obj );
        }

        return new WP_REST_Response( [ 'ok' => true ], 200 );
    }

    private function verify_signature( $payload, $header, $secret ) {
        $parts = [];
        foreach ( explode( ',', $header ) as $piece ) {
            $kv = explode( '=', $piece, 2 );
            if ( 2 === count( $kv ) ) {
                $parts[ trim( $kv[0] ) ] = trim( $kv[1] );
            }
        }
        if ( empty( $parts['t'] ) || empty( $parts['v1'] ) ) {
            return false;
        }
        // Anti-rejeu : tolérance de 5 minutes sur l'horodatage signé
        // (recommandation Stripe). Un payload capturé ne peut pas être
        // rejoué indéfiniment.
        if ( abs( time() - (int) $parts['t'] ) > 5 * MINUTE_IN_SECONDS ) {
            return false;
        }
        $signed   = $parts['t'] . '.' . $payload;
        $expected = hash_hmac( 'sha256', $signed, $secret );
        return hash_equals( $expected, $parts['v1'] );
    }
}

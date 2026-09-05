<?php
/**
 * Endpoints REST utilisés par le calendrier admin (widgets en lecture/écriture).
 * Tout est protégé par `manage_options`.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Rest {

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', [ $this, 'routes' ] );
    }

    public function permission() {
        return current_user_can( 'manage_options' );
    }

    public function routes() {
        $ns = 'nira/v1';

        register_rest_route( $ns, '/calendar', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'calendar' ],
            'permission_callback' => [ $this, 'permission' ],
        ] );

        register_rest_route( $ns, '/bookings', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'list_bookings' ],
            'permission_callback' => [ $this, 'permission' ],
        ] );

        register_rest_route( $ns, '/stats', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'stats' ],
            'permission_callback' => [ $this, 'permission' ],
        ] );
    }

    public function calendar( $req ) {
        $pid   = (int) $req->get_param( 'property_id' );
        $month = sanitize_text_field( $req->get_param( 'month' ) ?: date( 'Y-m' ) );
        $prop  = Nira_Properties::instance()->get( $pid );
        if ( ! $prop ) {
            return new WP_Error( 'nira_not_found', 'Not found', [ 'status' => 404 ] );
        }
        return rest_ensure_response( [
            'months'   => Nira_Availability::calendar_payload( $prop, $month, (int) ( $req->get_param( 'months' ) ?: 2 ) ),
            'bookings' => Nira_Availability::get_blocking_bookings(
                $pid,
                $month . '-01',
                date( 'Y-m-d', strtotime( $month . '-01 +3 months' ) )
            ),
        ] );
    }

    public function list_bookings( $req ) {
        return rest_ensure_response( Nira_Booking::find( [
            'property_id' => $req->get_param( 'property_id' ),
            'status'      => $req->get_param( 'status' ),
            'from'        => $req->get_param( 'from' ),
            'to'          => $req->get_param( 'to' ),
            'search'      => $req->get_param( 'search' ),
        ] ) );
    }

    public function stats() {
        return rest_ensure_response( Nira_Booking::stats() );
    }
}

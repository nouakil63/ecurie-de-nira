<?php
/**
 * Disponibilités et génération des données de calendrier.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Availability {

    /**
     * Réservations bloquantes qui chevauchent la fenêtre.
     */
    public static function get_blocking_bookings( $property_id, $from, $to ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'bookings' );
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT id, reference, check_in, check_out, status, source, guest_name
             FROM {$table}
             WHERE property_id = %d
               AND status IN ('confirmed','pending','blocked','airbnb')
               AND check_out > %s AND check_in < %s
             ORDER BY check_in ASC",
            $property_id, $from, $to
        ) );
    }

    public static function unavailable_dates( $property_id, $from, $to ) {
        $blocked = [];
        foreach ( self::get_blocking_bookings( $property_id, $from, $to ) as $b ) {
            $start = max( $b->check_in, $from );
            $end   = min( $b->check_out, $to );
            $d = new DateTime( $start );
            $e = new DateTime( $end );
            while ( $d < $e ) {
                $blocked[ $d->format( 'Y-m-d' ) ] = true;
                $d->modify( '+1 day' );
            }
        }
        return array_keys( $blocked );
    }

    public static function is_range_available( $property_id, $check_in, $check_out, $ignore_booking_id = 0 ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'bookings' );
        return 0 === (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table}
             WHERE property_id = %d
               AND status IN ('confirmed','pending','blocked','airbnb')
               AND check_out > %s AND check_in < %s
               AND id != %d",
            $property_id, $check_in, $check_out, (int) $ignore_booking_id
        ) );
    }

    /**
     * Données consommées par le calendrier frontend.
     */
    public static function calendar_payload( $property, $start_month, $months = 2 ) {
        $start = new DateTime( $start_month . '-01' );
        $end   = clone $start;
        $end->modify( "+{$months} months" );

        $unavailable = array_flip( self::unavailable_dates(
            (int) $property->id,
            $start->format( 'Y-m-d' ),
            $end->format( 'Y-m-d' )
        ) );

        $today = date( 'Y-m-d' );
        $out   = [];

        for ( $i = 0; $i < $months; $i++ ) {
            $cursor = clone $start;
            $cursor->modify( "+{$i} months" );
            $y = (int) $cursor->format( 'Y' );
            $m = (int) $cursor->format( 'n' );
            $days_in_month = (int) $cursor->format( 't' );

            $days = [];
            for ( $d = 1; $d <= $days_in_month; $d++ ) {
                $date = sprintf( '%04d-%02d-%02d', $y, $m, $d );
                $is_past = $date < $today;
                $days[] = [
                    'date'      => $date,
                    'price'     => Nira_Pricing::price_for_date( $property, $date ),
                    'available' => ! $is_past && ! isset( $unavailable[ $date ] ),
                    'minNights' => Nira_Pricing::min_nights_for_date( $property, $date ),
                    'weekday'   => (int) date( 'w', strtotime( $date ) ),
                    'past'      => $is_past,
                ];
            }

            $out[] = [
                'year'  => $y,
                'month' => $m,
                'days'  => $days,
                'first_weekday' => (int) date( 'w', strtotime( sprintf( '%04d-%02d-01', $y, $m ) ) ),
            ];
        }
        return $out;
    }
}

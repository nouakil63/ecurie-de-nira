<?php
/**
 * Cycle de vie des réservations : create, confirm, cancel, refund.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Booking {

    /**
     * Crée une réservation "pending". L'appelant gère le paiement ensuite.
     */
    public static function create( $args ) {
        global $wpdb;

        // 1) Nettoyage immédiat des holds expirés (sans attendre le cron quotidien)
        self::cleanup_expired_holds();

        $property = Nira_Properties::instance()->get( (int) ( $args['property_id'] ?? 0 ) );
        if ( ! $property ) {
            return new WP_Error( 'nira_no_property', __( 'Hébergement introuvable.', 'nira-booking' ) );
        }

        $check_in  = sanitize_text_field( $args['check_in'] ?? '' );
        $check_out = sanitize_text_field( $args['check_out'] ?? '' );

        if ( ! self::valid_date( $check_in ) || ! self::valid_date( $check_out ) ) {
            return new WP_Error( 'nira_bad_dates', __( 'Dates invalides.', 'nira-booking' ) );
        }
        if ( $check_in >= $check_out ) {
            return new WP_Error( 'nira_bad_range', __( "La date de départ doit être après la date d'arrivée.", 'nira-booking' ) );
        }

        $nights = Nira_Pricing::count_nights( $check_in, $check_out );
        $min    = Nira_Pricing::min_nights_for_date( $property, $check_in );
        if ( $nights < $min ) {
            return new WP_Error( 'nira_min_nights', sprintf(
                __( 'Séjour minimum de %d nuits.', 'nira-booking' ),
                $min
            ) );
        }
        if ( $nights > (int) $property->max_nights ) {
            return new WP_Error( 'nira_max_nights', __( 'Séjour trop long.', 'nira-booking' ) );
        }

        // 2) Libère tout hold précédent du même email pour cette propriété
        //    (évite de se bloquer soi-même quand on retente une réservation).
        //    ANNULATION et non suppression : si le client avait déjà lancé un
        //    paiement Stripe sur l'ancien hold, mark_paid() doit pouvoir
        //    retrouver la ligne et la confirmer — sinon client débité sans
        //    réservation en base.
        $email = sanitize_email( $args['guest_email'] ?? '' );
        if ( $email ) {
            $wpdb->query( $wpdb->prepare(
                "UPDATE " . Nira_DB::tbl( 'bookings' ) . "
                 SET status = 'cancelled', updated_at = %s
                 WHERE property_id = %d
                   AND guest_email = %s
                   AND status = 'pending'
                   AND payment_status = 'unpaid'",
                current_time( 'mysql' ), (int) $property->id, $email
            ) );
        }

        if ( ! Nira_Availability::is_range_available( (int) $property->id, $check_in, $check_out ) ) {
            return new WP_Error( 'nira_unavailable', __( 'Les dates sélectionnées ne sont plus disponibles.', 'nira-booking' ) );
        }

        $guest_count = (int) ( $args['guest_count'] ?? 1 );
        if ( $guest_count < 1 || $guest_count > (int) $property->capacity ) {
            return new WP_Error( 'nira_capacity', __( 'Nombre de voyageurs invalide.', 'nira-booking' ) );
        }

        $quote = Nira_Pricing::quote( $property, $check_in, $check_out, $guest_count );

        $now    = current_time( 'mysql' );
        $hold   = (int) Nira_Settings::get( 'hold_minutes', 30 );
        // Même référentiel horaire (heure locale WP) que les comparaisons
        // de cleanup_expired_holds() — gmdate() ici décalait l'expiration
        // du décalage horaire du site.
        $expire = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) + ( $hold * 60 ) );

        $row = [
            'reference'      => self::generate_reference(),
            'property_id'    => (int) $property->id,
            'guest_name'     => sanitize_text_field( $args['guest_name'] ?? '' ),
            'guest_email'    => sanitize_email( $args['guest_email'] ?? '' ),
            'guest_phone'    => sanitize_text_field( $args['guest_phone'] ?? '' ),
            'guest_count'    => $guest_count,
            'check_in'       => $check_in,
            'check_out'      => $check_out,
            'nights'         => $nights,
            'subtotal'       => $quote['subtotal'],
            'cleaning_fee'   => $quote['cleaning_fee'],
            'taxes'          => $quote['taxes'],
            'total'          => $quote['total'],
            'deposit'        => $quote['deposit'],
            'amount_paid'    => 0,
            'currency'       => Nira_Settings::get( 'currency', 'EUR' ),
            'source'         => sanitize_text_field( $args['source'] ?? 'direct' ),
            'status'         => 'pending',
            'payment_status' => 'unpaid',
            'notes'          => sanitize_textarea_field( $args['notes'] ?? '' ),
            'created_at'     => $now,
            'updated_at'     => $now,
            'expires_at'     => $expire,
        ];

        $wpdb->insert( Nira_DB::tbl( 'bookings' ), $row );
        $id = (int) $wpdb->insert_id;
        if ( ! $id ) {
            return new WP_Error( 'nira_db_error', __( "Impossible d'enregistrer la réservation.", 'nira-booking' ) );
        }

        do_action( 'nira_booking_created', $id );
        return $id;
    }

    public static function update_status( $id, $status, $extra = [] ) {
        global $wpdb;
        $allowed = [ 'pending', 'confirmed', 'cancelled', 'refunded', 'blocked', 'airbnb' ];
        if ( ! in_array( $status, $allowed, true ) ) {
            return false;
        }
        $data = array_merge( $extra, [
            'status'     => $status,
            'updated_at' => current_time( 'mysql' ),
        ] );
        $r = $wpdb->update( Nira_DB::tbl( 'bookings' ), $data, [ 'id' => (int) $id ] );
        do_action( 'nira_booking_status_changed', (int) $id, $status );
        return false !== $r;
    }

    /**
     * Enregistre un paiement reçu et confirme la réservation.
     *
     * Idempotent : un même PaymentIntent déjà comptabilisé (webhook rejoué,
     * ou webhook + confirmation AJAX en doublon) renvoie 'already' sans
     * modifier les montants. Fonctionne aussi sur un hold expiré/annulé :
     * l'argent a été encaissé, la réservation doit exister.
     *
     * @return true|'already'|false true = paiement enregistré,
     *         'already' = déjà traité, false = réservation introuvable.
     */
    public static function mark_paid( $id, $payment_intent, $amount ) {
        global $wpdb;
        $booking = self::get( $id );
        if ( ! $booking ) return false;

        $payment_intent = sanitize_text_field( $payment_intent );
        if ( $payment_intent && $booking->stripe_payment_intent === $payment_intent && (float) $booking->amount_paid > 0 ) {
            return 'already';
        }

        $paid       = (float) $booking->amount_paid + (float) $amount;
        $pay_status = $paid + 0.01 >= (float) $booking->total ? 'paid' : 'partial';

        $data = [
            'amount_paid'           => $paid,
            'payment_status'        => $pay_status,
            'status'                => 'confirmed',
            'stripe_payment_intent' => $payment_intent,
            'expires_at'            => null,
            'updated_at'            => current_time( 'mysql' ),
        ];

        // Hold annulé/expiré entre-temps : on confirme quand même (l'argent
        // est encaissé) mais on signale un éventuel conflit de dates apparu
        // depuis, pour que l'admin arbitre.
        if ( 'pending' !== $booking->status && 'confirmed' !== $booking->status
             && ! Nira_Availability::is_range_available( (int) $booking->property_id, $booking->check_in, $booking->check_out, (int) $id ) ) {
            $data['notes'] = trim( $booking->notes . "\n" . __( '⚠ Paiement reçu après expiration du hold : conflit de dates possible, à vérifier.', 'nira-booking' ) );
            wp_mail(
                get_option( 'admin_email' ),
                __( '[Nira] Conflit de dates possible', 'nira-booking' ),
                sprintf( "La réservation %s a été payée après l'expiration de son hold et ses dates chevauchent une autre réservation. À vérifier dans l'admin.", $booking->reference )
            );
        }

        $wpdb->update( Nira_DB::tbl( 'bookings' ), $data, [ 'id' => (int) $id ] );
        do_action( 'nira_booking_paid', (int) $id );
        return true;
    }

    public static function cancel( $id, $reason = '', $override_refund = null ) {
        $booking = self::get( $id );
        if ( ! $booking ) {
            return new WP_Error( 'nira_not_found', __( 'Réservation introuvable.', 'nira-booking' ) );
        }
        if ( in_array( $booking->status, [ 'cancelled', 'refunded' ], true ) ) {
            return new WP_Error( 'nira_already_cancelled', __( 'Réservation déjà annulée.', 'nira-booking' ) );
        }

        $property = Nira_Properties::instance()->get( (int) $booking->property_id );
        $refund   = null !== $override_refund
            ? (float) $override_refund
            : self::compute_refund( $booking, $property );
        $refund   = max( 0, min( $refund, (float) $booking->amount_paid - (float) $booking->amount_refunded ) );

        if ( $refund > 0 && ! empty( $booking->stripe_payment_intent ) ) {
            $res = Nira_Stripe::refund( $booking->stripe_payment_intent, $refund, $booking->currency );
            if ( is_wp_error( $res ) ) {
                return $res;
            }
        }

        global $wpdb;
        $wpdb->update(
            Nira_DB::tbl( 'bookings' ),
            [
                'status'          => $refund + 0.01 >= (float) $booking->amount_paid ? 'refunded' : 'cancelled',
                'amount_refunded' => (float) $booking->amount_refunded + $refund,
                'notes'           => trim( $booking->notes . "\n" . __( 'Annulation : ', 'nira-booking' ) . $reason ),
                'updated_at'      => current_time( 'mysql' ),
            ],
            [ 'id' => (int) $id ]
        );

        do_action( 'nira_booking_cancelled', (int) $id, $refund );
        return [ 'refunded' => $refund ];
    }

    public static function compute_refund( $booking, $property ) {
        $policy = $property ? $property->cancellation_policy : Nira_Settings::get( 'default_cancellation', 'flexible' );
        $days   = ( strtotime( $booking->check_in ) - time() ) / DAY_IN_SECONDS;
        $paid   = (float) $booking->amount_paid;

        switch ( $policy ) {
            case 'moderate':
                if ( $days >= 5 ) return $paid;
                if ( $days >= 1 ) return round( $paid * 0.5, 2 );
                return 0.0;
            case 'strict':
                if ( $days >= 7 ) return round( $paid * 0.5, 2 );
                return 0.0;
            case 'flexible':
            default:
                if ( $days >= 1 ) return $paid;
                return 0.0;
        }
    }

    public static function get( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . Nira_DB::tbl( 'bookings' ) . " WHERE id = %d",
            (int) $id
        ) );
    }

    public static function get_by_payment_intent( $pi ) {
        global $wpdb;
        if ( ! $pi ) return null;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . Nira_DB::tbl( 'bookings' ) . " WHERE stripe_payment_intent = %s",
            $pi
        ) );
    }

    /**
     * Enregistre un remboursement effectué DEPUIS le dashboard Stripe
     * (webhook charge.refunded). Un remboursement total libère les dates.
     */
    public static function record_external_refund( $booking, $refunded_total, $fully_refunded ) {
        global $wpdb;
        $data = [
            'amount_refunded' => max( (float) $booking->amount_refunded, (float) $refunded_total ),
            'updated_at'      => current_time( 'mysql' ),
        ];
        if ( $fully_refunded && ! in_array( $booking->status, [ 'cancelled', 'refunded' ], true ) ) {
            $data['status'] = 'refunded';
            $data['notes']  = trim( $booking->notes . "\n" . __( 'Remboursement effectué via le dashboard Stripe — dates libérées.', 'nira-booking' ) );
        }
        $wpdb->update( Nira_DB::tbl( 'bookings' ), $data, [ 'id' => (int) $booking->id ] );
        if ( isset( $data['status'] ) ) {
            do_action( 'nira_booking_status_changed', (int) $booking->id, 'refunded' );
        }
        return true;
    }

    public static function get_by_reference( $ref ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . Nira_DB::tbl( 'bookings' ) . " WHERE reference = %s",
            $ref
        ) );
    }

    public static function find( $filters = [] ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'bookings' );
        $where = [ '1=1' ];
        $args  = [];

        if ( ! empty( $filters['property_id'] ) ) {
            $where[] = 'property_id = %d';
            $args[]  = (int) $filters['property_id'];
        }
        if ( ! empty( $filters['status'] ) ) {
            $where[] = 'status = %s';
            $args[]  = $filters['status'];
        }
        if ( ! empty( $filters['exclude_status'] ) ) {
            $where[] = 'status != %s';
            $args[]  = $filters['exclude_status'];
        }
        if ( ! empty( $filters['from'] ) ) {
            $where[] = 'check_out >= %s';
            $args[]  = $filters['from'];
        }
        if ( ! empty( $filters['to'] ) ) {
            $where[] = 'check_in <= %s';
            $args[]  = $filters['to'];
        }
        if ( ! empty( $filters['search'] ) ) {
            $where[] = '(reference LIKE %s OR guest_name LIKE %s OR guest_email LIKE %s)';
            $needle  = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
            $args[]  = $needle; $args[] = $needle; $args[] = $needle;
        }

        $limit = (int) ( $filters['limit'] ?? 200 );
        $sql   = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . " ORDER BY check_in DESC LIMIT {$limit}";
        return $args
            ? $wpdb->get_results( $wpdb->prepare( $sql, ...$args ) )
            : $wpdb->get_results( $sql );
    }

    public static function delete( $id ) {
        global $wpdb;
        return $wpdb->delete( Nira_DB::tbl( 'bookings' ), [ 'id' => (int) $id ] );
    }

    public static function generate_reference() {
        return 'NIRA-' . strtoupper( wp_generate_password( 6, false, false ) );
    }

    /**
     * Token HMAC d'action sur réservation (ex. annulation, paiement solde).
     * Lié à id + reference + email + secret WP — impossible à deviner.
     */
    public static function action_token( $booking_id, $action ) {
        $b = self::get( $booking_id );
        if ( ! $b ) return '';
        $payload = $action . '|' . $booking_id . '|' . $b->reference . '|' . strtolower( $b->guest_email );
        return substr( hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) ), 0, 40 );
    }

    public static function verify_action_token( $booking_id, $action, $token ) {
        $expected = self::action_token( $booking_id, $action );
        return ! empty( $expected ) && hash_equals( $expected, (string) $token );
    }

    public static function action_url( $booking_id, $action ) {
        $token = self::action_token( $booking_id, $action );
        if ( ! $token ) return '';
        // admin-ajax.php et non home_url() : les caches de page complets
        // (IONOS, CDN…) servent la page d'accueil en cache sans exécuter
        // WordPress, donc ?nira_action=… était ignoré et le lien renvoyait
        // vers le site. admin-ajax n'est jamais mis en cache.
        return add_query_arg( [
            'action'      => 'nira_page',
            'nira_action' => $action,
            'b'           => $booking_id,
            't'           => $token,
        ], admin_url( 'admin-ajax.php' ) );
    }

    public static function balance_due( $booking ) {
        return max( 0, round( (float) $booking->total - (float) $booking->amount_paid, 2 ) );
    }

    public static function valid_date( $date ) {
        $d = DateTime::createFromFormat( 'Y-m-d', $date );
        return $d && $d->format( 'Y-m-d' ) === $date;
    }

    /**
     * Cron quotidien : envoie automatiquement la demande de solde aux clients
     * dont le check-in approche (par défaut 14 jours), si solde restant > 0
     * et email pas encore envoyé pour cette réservation.
     */
    public static function send_pending_balance_requests() {
        global $wpdb;
        $table = Nira_DB::tbl( 'bookings' );
        $days  = (int) Nira_Settings::get( 'balance_request_days', 14 );
        $threshold = date( 'Y-m-d', strtotime( "+{$days} days" ) );

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table}
             WHERE status = 'confirmed'
               AND check_in <= %s
               AND check_in >= %s
               AND amount_paid > 0
               AND amount_paid < total
               AND (balance_request_sent_at IS NULL OR balance_request_sent_at = '0000-00-00 00:00:00')",
            $threshold, date( 'Y-m-d' )
        ) );

        $sent = 0;
        foreach ( $rows as $b ) {
            if ( self::balance_due( $b ) <= 0.01 ) continue;
            if ( class_exists( 'Nira_Email' ) && Nira_Email::send_balance_request( $b->id ) ) {
                $wpdb->update(
                    $table,
                    [ 'balance_request_sent_at' => current_time( 'mysql' ) ],
                    [ 'id' => (int) $b->id ]
                );
                $sent++;
            }
        }
        return $sent;
    }

    /**
     * Nettoie les "hold" non payés qui ont expiré.
     *
     * ANNULATION et non suppression : si le webhook Stripe arrive après
     * l'expiration (paiement lancé à la 29e minute, webhook lent ou en
     * panne), mark_paid() doit encore pouvoir retrouver la ligne — sinon
     * le client est débité et sa réservation disparaît sans trace.
     * Les holds annulés jamais payés sont purgés après 30 jours.
     */
    public static function cleanup_expired_holds() {
        global $wpdb;
        $table = Nira_DB::tbl( 'bookings' );
        $now   = current_time( 'mysql' );
        $wpdb->query( $wpdb->prepare(
            "UPDATE {$table}
             SET status = 'cancelled', updated_at = %s
             WHERE status = 'pending'
               AND payment_status = 'unpaid'
               AND expires_at IS NOT NULL
               AND expires_at < %s",
            $now, $now
        ) );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table}
             WHERE status = 'cancelled'
               AND payment_status = 'unpaid'
               AND amount_paid = 0
               AND expires_at IS NOT NULL
               AND updated_at < %s",
            date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - 30 * DAY_IN_SECONDS )
        ) );
    }

    public static function stats() {
        global $wpdb;
        $t    = Nira_DB::tbl( 'bookings' );
        $year = (int) date( 'Y' );
        return [
            'total_bookings'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE status IN ('confirmed','refunded','cancelled')" ),
            'upcoming'        => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE status='confirmed' AND check_in >= %s", date( 'Y-m-d' ) ) ),
            'revenue_ytd'     => (float) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(amount_paid - amount_refunded),0) FROM {$t} WHERE YEAR(check_in) = %d", $year ) ),
            'nights_ytd'      => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(nights),0) FROM {$t} WHERE status='confirmed' AND YEAR(check_in) = %d", $year ) ),
            'pending'         => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE status='pending'" ),
            'airbnb_imported' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE status='airbnb'" ),
        ];
    }
}

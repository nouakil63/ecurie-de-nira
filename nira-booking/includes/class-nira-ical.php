<?php
/**
 * Synchronisation iCal 2 voies (Airbnb, Booking, VRBO, Abritel…).
 *
 * Réécrit en s'appuyant sur la technique éprouvée du plugin CDV iCal Sync :
 * - Fetch-then-delete : on supprime les anciennes dates UNIQUEMENT si le
 *   nouveau fetch a réussi (sinon on garde l'historique pour éviter d'avoir
 *   un calendrier vide en cas de timeout temporaire d'Airbnb).
 * - Détection automatique de la plateforme depuis l'URL.
 * - Gestion correcte des dates exclusives (DTEND;VALUE=DATE = jour APRÈS
 *   la dernière nuit = le jour de checkout, RFC 5545 — identique à notre
 *   check_out : les deux sont exclusifs, aucun décalage à appliquer).
 * - Anti-conflit : on n'importe pas une période qui chevauche une
 *   réservation directe confirmée.
 * - Export robuste : CORS, no-cache, ob_end_clean, VTIMEZONE Europe/Paris.
 *
 * L'API publique (sync_all, sync_feed, add_feed, delete_feed, feeds, get_feed,
 * register_export_route) est inchangée — l'admin et les templates ne bougent pas.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Ical {

    const SOURCE_AIRBNB  = 'airbnb';
    const SOURCE_BOOKING = 'booking';
    const SOURCE_VRBO    = 'vrbo';
    const SOURCE_ABRITEL = 'abritel';
    const SOURCE_EXPEDIA = 'expedia';
    const SOURCE_OTHER   = 'other';

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_export_route' ] );
    }

    /* ================================================================== */
    /* Plateformes                                                          */
    /* ================================================================== */

    public static function get_platforms() {
        return [
            self::SOURCE_AIRBNB  => [ 'label' => 'Airbnb',      'color' => '#FF5A5F' ],
            self::SOURCE_BOOKING => [ 'label' => 'Booking.com', 'color' => '#003580' ],
            self::SOURCE_VRBO    => [ 'label' => 'VRBO',        'color' => '#3D67FF' ],
            self::SOURCE_ABRITEL => [ 'label' => 'Abritel',     'color' => '#00A699' ],
            self::SOURCE_EXPEDIA => [ 'label' => 'Expedia',     'color' => '#FFCC00' ],
            self::SOURCE_OTHER   => [ 'label' => 'Autre',       'color' => '#666666' ],
        ];
    }

    public static function detect_source( $url ) {
        $u = strtolower( (string) $url );
        if ( strpos( $u, 'airbnb' )      !== false ) return self::SOURCE_AIRBNB;
        if ( strpos( $u, 'booking.com' ) !== false || strpos( $u, 'admin.booking' ) !== false ) return self::SOURCE_BOOKING;
        if ( strpos( $u, 'vrbo' )        !== false ) return self::SOURCE_VRBO;
        if ( strpos( $u, 'abritel' )     !== false ) return self::SOURCE_ABRITEL;
        if ( strpos( $u, 'expedia' )     !== false ) return self::SOURCE_EXPEDIA;
        return self::SOURCE_OTHER;
    }

    /* ================================================================== */
    /* Feeds CRUD                                                           */
    /* ================================================================== */

    public static function feeds( $property_id = 0 ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'ical_feeds' );
        if ( $property_id ) {
            return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE property_id = %d", $property_id ) );
        }
        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC" );
    }

    public static function get_feed( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . Nira_DB::tbl( 'ical_feeds' ) . " WHERE id = %d",
            (int) $id
        ) );
    }

    public static function add_feed( $property_id, $label, $url ) {
        global $wpdb;
        $url = esc_url_raw( $url );
        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return new WP_Error( 'invalid_url', __( 'URL invalide', 'nira-booking' ) );
        }
        $wpdb->insert( Nira_DB::tbl( 'ical_feeds' ), [
            'property_id' => (int) $property_id,
            'label'       => sanitize_text_field( $label ),
            'url'         => $url,
        ] );
        return (int) $wpdb->insert_id;
    }

    public static function delete_feed( $id ) {
        global $wpdb;
        $feed = self::get_feed( $id );
        if ( $feed ) {
            // On nettoie aussi les imports liés à ce feed pour ne pas laisser
            // de réservations fantômes dans le calendrier.
            self::remove_imported_for_feed( (int) $feed->property_id, (int) $feed->id );
        }
        return $wpdb->delete( Nira_DB::tbl( 'ical_feeds' ), [ 'id' => (int) $id ] );
    }

    /* ================================================================== */
    /* Import                                                               */
    /* ================================================================== */

    public static function sync_all() {
        // Verrou anti-overlap : si une sync tourne déjà, on saute (cron).
        if ( get_transient( 'nira_ical_sync_running' ) ) {
            return 0;
        }
        set_transient( 'nira_ical_sync_running', true, 5 * MINUTE_IN_SECONDS );

        $total = 0;
        foreach ( self::feeds() as $feed ) {
            $count = self::instance()->sync_feed( $feed );
            if ( is_int( $count ) ) $total += $count;
        }

        delete_transient( 'nira_ical_sync_running' );
        return $total;
    }

    /**
     * Synchronise un flux. CRITIQUE : on fetch d'abord, on supprime ensuite,
     * pour éviter de vider le calendrier si Airbnb timeout.
     */
    public function sync_feed( $feed ) {
        $url = $feed->url ?? '';
        if ( ! $url ) return 0;

        // 1) Fetch — wp_safe_remote_get refuse les IP privées/loopback et les
        // redirections dangereuses (anti-SSRF : le cron ne doit pas pouvoir
        // requêter le réseau interne via une URL de flux malveillante).
        $res = wp_safe_remote_get( $url, [
            'timeout'             => 30,
            'sslverify'           => true,
            'reject_unsafe_urls'  => true,
            'user-agent'          => 'Mozilla/5.0 (compatible; NiraBooking/2.0; +' . home_url() . ')',
        ] );

        $code = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
        if ( is_wp_error( $res ) || 200 !== $code ) {
            $this->update_feed_status( $feed->id, 'error', 0 );
            error_log( '[Nira iCal] Fetch échoué pour feed #' . (int) $feed->id . ' (HTTP ' . $code . ') — ' . $url );
            return 0;
        }

        $body = wp_remote_retrieve_body( $res );
        if ( stripos( $body, 'BEGIN:VCALENDAR' ) === false ) {
            $this->update_feed_status( $feed->id, 'error', 0 );
            error_log( '[Nira iCal] Réponse non-iCal pour feed #' . (int) $feed->id );
            return 0;
        }

        // 2) Parse
        $events = $this->parse_ics( $body );

        // 3) Fetch OK → on peut maintenant remplacer proprement
        $source = self::detect_source( $url );
        self::remove_imported_for_feed( (int) $feed->property_id, (int) $feed->id );

        $count = 0;
        foreach ( $events as $ev ) {
            if ( $this->insert_imported_event( (int) $feed->property_id, (int) $feed->id, $source, $url, $ev ) ) {
                $count++;
            }
        }

        $this->update_feed_status( $feed->id, 'ok', $count );
        return $count;
    }

    private function insert_imported_event( $property_id, $feed_id, $source, $source_url, $event ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'bookings' );
        $now   = current_time( 'mysql' );

        $uid = $event['uid']   ?? '';
        $in  = $event['start'] ?? '';
        $out = $event['end']   ?? '';
        if ( ! $uid || ! $in || ! $out ) return false;
        // Évite les events à zéro nuit
        if ( $in >= $out ) return false;

        // Anti-conflit avec réservations directes en cours
        if ( $this->has_real_booking_conflict( $property_id, $in, $out ) ) {
            error_log( "[Nira iCal] Conflit ignoré property #{$property_id} ({$in} → {$out})" );
            return false;
        }

        $data = [
            'check_in'       => $in,
            'check_out'      => $out,
            'status'         => 'airbnb',
            'source'         => $source,
            'guest_name'     => sanitize_text_field( $event['summary'] ?? ucfirst( $source ) ),
            'guest_email'    => $source . '@import.local',
            'nights'         => Nira_Pricing::count_nights( $in, $out ),
            'ical_uid'       => $uid,
            'ical_feed_id'   => (int) $feed_id,
            'updated_at'     => $now,
        ];

        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE property_id = %d AND ical_uid = %s",
            $property_id, $uid
        ) );

        if ( $existing ) {
            $wpdb->update( $table, $data, [ 'id' => (int) $existing->id ] );
        } else {
            $data['reference']      = Nira_Booking::generate_reference();
            $data['property_id']    = $property_id;
            $data['guest_count']    = 1;
            $data['total']          = 0;
            $data['currency']       = Nira_Settings::get( 'currency', 'EUR' );
            $data['payment_status'] = 'external';
            $data['created_at']     = $now;
            $wpdb->insert( $table, $data );
        }
        return true;
    }

    /**
     * Supprime les imports précédents de CE flux uniquement (ical_feed_id).
     * Impératif avec plusieurs flux par propriété (Airbnb + Booking…) : la
     * sync du flux A ne doit jamais effacer les blocages importés du flux B —
     * sinon, si B est en panne au moment de sa propre sync, ses dates
     * disparaissent du calendrier jusqu'à son rétablissement.
     * Les lignes historiques sans feed_id (avant migration 2.0.41) sont
     * rattachées au premier flux qui synchronise pour cette propriété.
     */
    private static function remove_imported_for_feed( $property_id, $feed_id ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'bookings' );
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table}
             WHERE property_id = %d AND status = 'airbnb'
               AND ical_uid IS NOT NULL AND ical_uid != ''
               AND (ical_feed_id = %d OR ical_feed_id IS NULL OR ical_feed_id = 0)",
            $property_id, (int) $feed_id
        ) );
    }

    private function has_real_booking_conflict( $property_id, $in, $out ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'bookings' );
        // Conflit si une réservation NON-airbnb (donc directe) chevauche
        // ET qu'elle n'est ni annulée ni remboursée.
        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table}
             WHERE property_id = %d
               AND status NOT IN ('cancelled','refunded','airbnb')
               AND check_in < %s AND check_out > %s",
            $property_id, $out, $in
        ) );
        return $count > 0;
    }

    private function update_feed_status( $id, $status, $count ) {
        global $wpdb;
        $wpdb->update(
            Nira_DB::tbl( 'ical_feeds' ),
            [
                'last_synced' => current_time( 'mysql' ),
                'last_status' => $status,
                'last_events' => (int) $count,
            ],
            [ 'id' => (int) $id ]
        );
    }

    /* ================================================================== */
    /* Parsing iCal — technique CDV                                         */
    /* ================================================================== */

    public function parse_ics( $content ) {
        // Normalisation des sauts de ligne et UNFOLDING (RFC 5545 §3.1) :
        // toute ligne qui démarre par un espace ou tab est la suite de la précédente.
        $content = str_replace( [ "\r\n", "\r" ], "\n", (string) $content );
        $content = preg_replace( "/\n[ \t]/", '', $content );

        $events = [];
        if ( preg_match_all( '/BEGIN:VEVENT.*?END:VEVENT/s', $content, $matches ) ) {
            foreach ( $matches[0] as $vevent ) {
                $e = $this->parse_vevent( $vevent );
                if ( $e ) $events[] = $e;
            }
        }
        return $events;
    }

    private function parse_vevent( $vevent ) {
        preg_match( '/^UID:(.+)$/mi',         $vevent, $m_uid );
        preg_match( '/^SUMMARY:(.+)$/mi',     $vevent, $m_sum );
        preg_match( '/^DTSTART[^:]*:(.+)$/mi', $vevent, $m_start );
        preg_match( '/^DTEND[^:]*:(.+)$/mi',   $vevent, $m_end );

        if ( empty( $m_start[1] ) ) return null;
        if ( ! preg_match( '/(\d{8})/', $m_start[1], $ms ) ) return null;

        $start = DateTime::createFromFormat( 'Ymd', $ms[1] );
        if ( ! $start ) return null;

        if ( empty( $m_end[1] ) || ! preg_match( '/(\d{8})/', $m_end[1], $me ) ) {
            // Pas de DTEND : event d'une journée selon la RFC 5545 → 1 nuit bloquée.
            $end = clone $start;
            $end->modify( '+1 day' );
        } else {
            $end = DateTime::createFromFormat( 'Ymd', $me[1] );
            // RFC 5545 : pour les events all-day, DTEND est EXCLUSIF = le jour APRÈS
            // la dernière nuit, c'est-à-dire LE jour de checkout. Notre check_out est
            // exclusif lui aussi → on stocke DTEND tel quel, sans retirer de jour.
            // Retirer 1 jour libérerait la dernière nuit de chaque réservation Airbnb
            // et ferait disparaître les séjours d'une nuit (0 nuit → rejeté à l'insert).
        }

        return [
            'uid'     => trim( $m_uid[1] ?? '' ),
            'summary' => trim( $m_sum[1] ?? '' ),
            'start'   => $start->format( 'Y-m-d' ),
            'end'     => $end->format( 'Y-m-d' ),
        ];
    }

    /* ================================================================== */
    /* Export — robuste (CORS, no-cache, VTIMEZONE)                         */
    /* ================================================================== */

    public function register_export_route() {
        register_rest_route( 'nira/v1', '/ical/(?P<slug>[a-z0-9-]+)\.ics', [
            'methods'             => 'GET',
            'permission_callback' => '__return_true',
            'callback'            => [ $this, 'export_feed' ],
        ] );
    }

    public function export_feed( WP_REST_Request $request ) {
        $property = Nira_Properties::instance()->get_by_slug( $request['slug'] );
        if ( ! $property ) {
            return new WP_REST_Response( 'Not found', 404 );
        }

        $bookings = Nira_Booking::find( [
            'property_id' => $property->id,
            'from'        => date( 'Y-m-d' ),
            'limit'       => 1000,
        ] );

        $events = [];
        foreach ( $bookings as $b ) {
            // On exporte SEULEMENT les réservations directes (pas les imports
            // OTA, sinon Airbnb verrait ses propres dates dupliquées).
            if ( ! in_array( $b->status, [ 'confirmed', 'pending', 'blocked' ], true ) ) continue;
            if ( ! empty( $b->ical_uid ) ) continue; // déjà importé d'ailleurs

            $events[] = [
                'uid'     => 'nira-' . $b->id . '@' . wp_parse_url( home_url(), PHP_URL_HOST ),
                'start'   => $b->check_in,
                'end'     => $b->check_out,
                // Libellé générique : l'URL d'export est publique et devinable,
                // on n'y expose pas les références internes de réservation.
                'summary' => 'Réservé',
                'created' => $b->created_at ?? $b->updated_at ?? null,
            ];
        }

        $ical = $this->generate_ical( $property, $events );

        // Nettoyage radical de tout buffer précédent (espaces parasites,
        // warnings PHP cachés…) — sinon Airbnb refuse le feed.
        while ( ob_get_level() ) {
            ob_end_clean();
        }

        // Headers que Airbnb / Booking attendent
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Content-Type: text/calendar; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $property->slug ) . '.ics"' );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo trim( $ical );
        exit;
    }

    private function generate_ical( $property, $events ) {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Ecuries de Nira//Nira Booking 2.0//FR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . self::escape_ical( 'Nira — ' . $property->name ),
            'X-WR-TIMEZONE:Europe/Paris',
            // Bloc VTIMEZONE : nécessaire pour que les calendriers tiers (Apple,
            // Google, Outlook) interprètent correctement les heures.
            'BEGIN:VTIMEZONE',
            'TZID:Europe/Paris',
            'BEGIN:DAYLIGHT',
            'TZOFFSETFROM:+0100',
            'TZOFFSETTO:+0200',
            'TZNAME:CEST',
            'DTSTART:19700329T020000',
            'RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU',
            'END:DAYLIGHT',
            'BEGIN:STANDARD',
            'TZOFFSETFROM:+0200',
            'TZOFFSETTO:+0100',
            'TZNAME:CET',
            'DTSTART:19701025T030000',
            'RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU',
            'END:STANDARD',
            'END:VTIMEZONE',
        ];

        $dtstamp = gmdate( 'Ymd\THis\Z' );

        foreach ( $events as $e ) {
            try {
                $start = new DateTime( $e['start'] );
                // RFC 5545 : DTEND;VALUE=DATE est EXCLUSIF (jour après la dernière
                // nuit). check_out est déjà ce jour-là (le client part le matin, la
                // nuit du check_out reste vendable) → exporté tel quel, sans +1 jour,
                // sinon Airbnb bloque une nuit de trop après chaque réservation directe.
                $end   = new DateTime( $e['end'] );
            } catch ( Exception $ex ) {
                continue;
            }

            $created = $dtstamp;
            if ( ! empty( $e['created'] ) ) {
                try {
                    $created = ( new DateTime( $e['created'], new DateTimeZone( 'UTC' ) ) )->format( 'Ymd\THis\Z' );
                } catch ( Exception $ex ) {}
            }

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $e['uid'];
            $lines[] = 'DTSTAMP:' . $dtstamp;
            $lines[] = 'DTSTART;VALUE=DATE:' . $start->format( 'Ymd' );
            $lines[] = 'DTEND;VALUE=DATE:'   . $end->format( 'Ymd' );
            $lines[] = 'SUMMARY:' . self::escape_ical( $e['summary'] ?? 'Réservé' );
            $lines[] = 'CREATED:' . $created;
            $lines[] = 'LAST-MODIFIED:' . $created;
            $lines[] = 'SEQUENCE:0';
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'TRANSP:OPAQUE';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        // RFC 5545 : CRLF entre chaque ligne
        return implode( "\r\n", $lines ) . "\r\n";
    }

    private static function escape_ical( $text ) {
        $text = str_replace(
            [ '\\',   ',',   ';',   "\n" ],
            [ '\\\\', '\\,', '\\;', '\\n' ],
            (string) $text
        );
        // Wrapping à 75 caractères avec espace de continuation (RFC 5545 §3.1)
        return wordwrap( $text, 75, "\r\n ", true );
    }
}

<?php
/**
 * Schéma et migrations de la base.
 *
 * Tables :
 *   - {prefix}nira_properties      : hébergements (gîtes)
 *   - {prefix}nira_bookings        : réservations (directes, Airbnb, blocs)
 *   - {prefix}nira_pricing_rules   : tarifs saisonniers / hebdo / nuit minimum
 *   - {prefix}nira_ical_feeds      : flux iCal entrants (Airbnb…)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_DB {

    const VERSION = '2.0.41';

    public static function tbl( $name ) {
        global $wpdb;
        return $wpdb->prefix . 'nira_' . $name;
    }

    public static function install() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();

        $properties = self::tbl( 'properties' );
        $bookings   = self::tbl( 'bookings' );
        $pricing    = self::tbl( 'pricing_rules' );
        $feeds      = self::tbl( 'ical_feeds' );

        $sql = [];

        $sql[] = "CREATE TABLE {$properties} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slug VARCHAR(60) NOT NULL,
            name VARCHAR(150) NOT NULL,
            capacity SMALLINT UNSIGNED NOT NULL DEFAULT 2,
            bedrooms TINYINT UNSIGNED NOT NULL DEFAULT 1,
            bathrooms TINYINT UNSIGNED NOT NULL DEFAULT 1,
            base_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            cleaning_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
            min_nights TINYINT UNSIGNED NOT NULL DEFAULT 2,
            max_nights SMALLINT UNSIGNED NOT NULL DEFAULT 30,
            deposit_pct TINYINT UNSIGNED NOT NULL DEFAULT 30,
            checkin_time VARCHAR(5) NOT NULL DEFAULT '16:00',
            checkout_time VARCHAR(5) NOT NULL DEFAULT '11:00',
            cancellation_policy VARCHAR(30) NOT NULL DEFAULT 'flexible',
            description TEXT NULL,
            amenities TEXT NULL,
            airbnb_url VARCHAR(255) NULL,
            cover_image VARCHAR(500) NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            sort_order SMALLINT NOT NULL DEFAULT 0,
            weekly_discount_pct TINYINT UNSIGNED NOT NULL DEFAULT 0,
            monthly_discount_pct TINYINT UNSIGNED NOT NULL DEFAULT 0,
            weekly_threshold_nights TINYINT UNSIGNED NOT NULL DEFAULT 7,
            monthly_threshold_nights TINYINT UNSIGNED NOT NULL DEFAULT 28,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$bookings} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            reference VARCHAR(20) NOT NULL,
            property_id BIGINT(20) UNSIGNED NOT NULL,
            guest_name VARCHAR(150) NOT NULL,
            guest_email VARCHAR(150) NOT NULL,
            guest_phone VARCHAR(30) NULL,
            guest_count SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            check_in DATE NOT NULL,
            check_out DATE NOT NULL,
            nights SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
            cleaning_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
            taxes DECIMAL(10,2) NOT NULL DEFAULT 0,
            total DECIMAL(10,2) NOT NULL DEFAULT 0,
            deposit DECIMAL(10,2) NOT NULL DEFAULT 0,
            amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0,
            amount_refunded DECIMAL(10,2) NOT NULL DEFAULT 0,
            currency CHAR(3) NOT NULL DEFAULT 'EUR',
            source VARCHAR(30) NOT NULL DEFAULT 'direct',
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid',
            stripe_payment_intent VARCHAR(100) NULL,
            stripe_customer VARCHAR(100) NULL,
            ical_uid VARCHAR(191) NULL,
            ical_feed_id BIGINT(20) UNSIGNED NULL,
            notes TEXT NULL,
            balance_request_sent_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            expires_at DATETIME NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY reference (reference),
            KEY property_dates (property_id, check_in, check_out),
            KEY ical_uid (ical_uid),
            KEY status (status)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$pricing} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            property_id BIGINT(20) UNSIGNED NOT NULL,
            label VARCHAR(150) NOT NULL,
            rule_type VARCHAR(20) NOT NULL DEFAULT 'season',
            date_from DATE NULL,
            date_to DATE NULL,
            weekday TINYINT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            min_nights TINYINT UNSIGNED NULL,
            priority TINYINT NOT NULL DEFAULT 10,
            PRIMARY KEY  (id),
            KEY property (property_id)
        ) {$charset};";

        $sql[] = "CREATE TABLE {$feeds} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            property_id BIGINT(20) UNSIGNED NOT NULL,
            label VARCHAR(100) NOT NULL,
            url VARCHAR(500) NOT NULL,
            last_synced DATETIME NULL,
            last_status VARCHAR(30) NULL,
            last_events INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            KEY property (property_id)
        ) {$charset};";

        foreach ( $sql as $q ) {
            dbDelta( $q );
        }

        update_option( 'nira_db_version', self::VERSION );

        self::seed_defaults();
        self::schedule_events();
    }

    private static function seed_defaults() {
        global $wpdb;
        $table = self::tbl( 'properties' );
        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        if ( $count > 0 ) {
            return;
        }

        $now = current_time( 'mysql' );
        $rows = [
            [
                'slug'         => 'appartement',
                'name'         => "L'Appartement",
                'capacity'     => 7,
                'bedrooms'     => 4,
                'bathrooms'    => 1,
                'base_price'   => 180.00,
                'cleaning_fee' => 60.00,
                'min_nights'   => 2,
                'max_nights'   => 30,
                'deposit_pct'  => 30,
                'checkin_time' => '16:00',
                'checkout_time'=> '11:00',
                'cancellation_policy' => 'flexible',
                'description'  => "Idéal pour les séjours en famille ou entre amis, vue imprenable sur le domaine.",
                'amenities'    => "3 lits doubles, 1 lit simple\nCuisine entièrement équipée\nTélévision & salon\nParking privé avec caméra\nAnimaux de compagnie acceptés\nBoxes/paddocks en option",
                'cover_image'  => 'IMG_3093-scaled.jpeg',
                'status'       => 'active',
                'sort_order'   => 1,
                'created_at'   => $now,
            ],
            [
                'slug'         => 'duplex',
                'name'         => 'Le Duplex',
                'capacity'     => 4,
                'bedrooms'     => 2,
                'bathrooms'    => 1,
                'base_price'   => 130.00,
                'cleaning_fee' => 45.00,
                'min_nights'   => 2,
                'max_nights'   => 30,
                'deposit_pct'  => 30,
                'checkin_time' => '16:00',
                'checkout_time'=> '11:00',
                'cancellation_policy' => 'flexible',
                'description'  => "Un cocon intimiste sous les toits, parfait pour un couple de cavaliers.",
                'amenities'    => "2 lits doubles\nCuisine équipée ouverte\nTélévision & coin salon\nParking privé avec caméra\nAnimaux de compagnie acceptés\nBoxes/paddocks en option",
                'cover_image'  => 'IMG_3089-2048x1536.jpeg',
                'status'       => 'active',
                'sort_order'   => 2,
                'created_at'   => $now,
            ],
        ];

        foreach ( $rows as $row ) {
            $wpdb->insert( $table, $row );
        }

        Nira_Settings::install_defaults();
    }

    private static function schedule_events() {
        if ( ! wp_next_scheduled( 'nira_ical_sync' ) ) {
            wp_schedule_event( time() + 60, 'hourly', 'nira_ical_sync' );
        }
        if ( ! wp_next_scheduled( 'nira_daily_cleanup' ) ) {
            wp_schedule_event( time() + 120, 'daily', 'nira_daily_cleanup' );
        }
    }
}

<?php
/**
 * Wrapper unique pour tous les réglages globaux stockés dans wp_options,
 * préfixés par `nira_`.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Settings {

    /**
     * Toutes les clés de réglages avec leur valeur par défaut.
     */
    public static function schema() {
        return [
            // Devise & fiscalité
            'currency'             => 'EUR',
            'tax_rate'             => 0.0,
            'tourist_tax'          => 0.0, // Taxe de séjour par nuit par personne

            // Paiement
            'charge_mode'          => 'deposit', // 'deposit' ou 'full'
            'stripe_pk'            => '',
            'stripe_sk'            => '',
            'stripe_webhook_secret'=> '',

            // Horaires (par défaut, surchargé par propriété)
            'checkin_time'         => '16:00',
            'checkout_time'        => '11:00',

            // Hold (minutes avant qu'une réservation non payée expire)
            'hold_minutes'         => 30,

            // Emails
            'from_email'           => '',
            'from_name'            => 'Écuries de Nira',
            'notification_email'   => '',
            'logo_url'             => '',

            // Identité
            'business_name'        => 'Écuries de Nira',
            'business_address'     => '609 route de Deauville, 14800 Bonneville-sur-Touques',
            'business_phone'       => '06 74 57 28 19',

            // Politique d'annulation par défaut
            'default_cancellation' => 'flexible',

            // Demande automatique du solde (jours avant check-in)
            'balance_request_days' => 14,
        ];
    }

    public static function install_defaults() {
        foreach ( self::schema() as $key => $default ) {
            if ( false === get_option( 'nira_' . $key, false ) ) {
                update_option( 'nira_' . $key, $default );
            }
        }
        if ( ! get_option( 'nira_from_email' ) ) {
            update_option( 'nira_from_email', get_option( 'admin_email' ) );
        }
        if ( ! get_option( 'nira_notification_email' ) ) {
            update_option( 'nira_notification_email', get_option( 'admin_email' ) );
        }
    }

    public static function get( $key, $fallback = null ) {
        $schema = self::schema();
        $default = $fallback !== null ? $fallback : ( $schema[ $key ] ?? '' );
        return get_option( 'nira_' . $key, $default );
    }

    public static function set( $key, $value ) {
        return update_option( 'nira_' . $key, $value );
    }

    public static function all() {
        $out = [];
        foreach ( array_keys( self::schema() ) as $key ) {
            $out[ $key ] = self::get( $key );
        }
        return $out;
    }

    public static function update_from_post( $data ) {
        $schema = self::schema();
        foreach ( $schema as $key => $default ) {
            if ( ! array_key_exists( $key, $data ) ) {
                continue;
            }
            $value = $data[ $key ];
            // Les clés secrètes masquées ne sont pas réécrites
            if ( in_array( $key, [ 'stripe_sk', 'stripe_webhook_secret' ], true ) && '••••••••' === $value ) {
                continue;
            }
            if ( is_float( $default ) ) {
                $value = (float) $value;
            } elseif ( is_int( $default ) ) {
                $value = (int) $value;
            } elseif ( is_string( $value ) ) {
                $value = sanitize_text_field( $value );
            }
            self::set( $key, $value );
        }
    }
}

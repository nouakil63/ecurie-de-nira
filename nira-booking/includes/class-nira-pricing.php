<?php
/**
 * Moteur tarifaire.
 *
 * Résolution (priorité décroissante) :
 *   1. Règle saisonnière dont [date_from, date_to] contient la date.
 *   2. Règle jour de semaine (0 = dimanche … 6 = samedi).
 *   3. Prix de base de l'hébergement.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Pricing {

    public static function get_rules( $property_id ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'pricing_rules' );
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$table} WHERE property_id = %d ORDER BY priority DESC, id DESC",
            $property_id
        ) );
    }

    public static function get_rule( $id ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'pricing_rules' );
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id ) );
    }

    public static function create_rule( $data ) {
        global $wpdb;
        $row = self::sanitize( $data );
        $wpdb->insert( Nira_DB::tbl( 'pricing_rules' ), $row );
        return (int) $wpdb->insert_id;
    }

    public static function update_rule( $id, $data ) {
        global $wpdb;
        $row = self::sanitize( $data );
        return $wpdb->update( Nira_DB::tbl( 'pricing_rules' ), $row, [ 'id' => (int) $id ] );
    }

    public static function delete_rule( $id ) {
        global $wpdb;
        return $wpdb->delete( Nira_DB::tbl( 'pricing_rules' ), [ 'id' => (int) $id ] );
    }

    private static function sanitize( $data ) {
        return [
            'property_id' => (int) ( $data['property_id'] ?? 0 ),
            'label'       => sanitize_text_field( $data['label'] ?? '' ),
            'rule_type'   => in_array( $data['rule_type'] ?? 'season', [ 'season', 'weekday' ], true ) ? $data['rule_type'] : 'season',
            'date_from'   => ! empty( $data['date_from'] ) ? sanitize_text_field( $data['date_from'] ) : null,
            'date_to'     => ! empty( $data['date_to'] ) ? sanitize_text_field( $data['date_to'] ) : null,
            'weekday'     => isset( $data['weekday'] ) && '' !== $data['weekday'] && null !== $data['weekday'] ? (int) $data['weekday'] : null,
            'price'       => (float) ( $data['price'] ?? 0 ),
            'min_nights'  => isset( $data['min_nights'] ) && '' !== $data['min_nights'] ? (int) $data['min_nights'] : null,
            'priority'    => (int) ( $data['priority'] ?? 10 ),
        ];
    }

    /**
     * Prix pour une date donnée.
     */
    public static function price_for_date( $property, $date ) {
        static $rules_cache = [];
        $pid = (int) $property->id;
        if ( ! isset( $rules_cache[ $pid ] ) ) {
            $rules_cache[ $pid ] = self::get_rules( $pid );
        }
        $rules = $rules_cache[ $pid ];

        $ts = strtotime( $date );
        $wd = (int) date( 'w', $ts );

        foreach ( $rules as $rule ) {
            if ( 'season' === $rule->rule_type
                 && $rule->date_from && $rule->date_to
                 && $date >= $rule->date_from && $date <= $rule->date_to ) {
                return (float) $rule->price;
            }
        }
        foreach ( $rules as $rule ) {
            if ( 'weekday' === $rule->rule_type
                 && null !== $rule->weekday
                 && (int) $rule->weekday === $wd ) {
                return (float) $rule->price;
            }
        }
        return (float) $property->base_price;
    }

    /**
     * Prix le plus bas applicable dans les `$months` prochains mois,
     * en tenant compte des règles saisonnières et hebdomadaires.
     * Sert pour l'affichage "À partir de X €/nuit" sur la fiche.
     */
    public static function min_nightly_rate( $property, $months = 12 ) {
        $today  = new DateTime( 'today' );
        $end    = ( clone $today )->modify( "+{$months} months" );
        $min    = (float) $property->base_price;
        $cursor = clone $today;
        while ( $cursor < $end ) {
            $price = self::price_for_date( $property, $cursor->format( 'Y-m-d' ) );
            if ( $price > 0 && $price < $min ) {
                $min = $price;
            }
            $cursor->modify( '+1 day' );
        }
        return (float) $min;
    }

    public static function min_nights_for_date( $property, $date ) {
        foreach ( self::get_rules( (int) $property->id ) as $rule ) {
            if ( null === $rule->min_nights ) continue;
            if ( 'season' === $rule->rule_type
                 && $rule->date_from && $rule->date_to
                 && $date >= $rule->date_from && $date <= $rule->date_to ) {
                return (int) $rule->min_nights;
            }
        }
        return (int) $property->min_nights;
    }

    /**
     * Devis complet pour un séjour.
     *
     * Applique automatiquement les remises long séjour si configurées
     * sur la propriété (ex. -20% à partir de 7 nuits, -75% à partir de 28).
     */
    public static function quote( $property, $check_in, $check_out, $guest_count = 1 ) {
        $nights    = self::count_nights( $check_in, $check_out );
        $subtotal_raw = 0.0;
        $breakdown = [];

        for ( $i = 0; $i < $nights; $i++ ) {
            $d = date( 'Y-m-d', strtotime( $check_in . " +{$i} days" ) );
            $p = self::price_for_date( $property, $d );
            $subtotal_raw += $p;
            $breakdown[ $d ] = $p;
        }

        // Remises long séjour (style Airbnb)
        $weekly_pct  = (int) ( $property->weekly_discount_pct  ?? 0 );
        $monthly_pct = (int) ( $property->monthly_discount_pct ?? 0 );
        $weekly_th   = max( 1, (int) ( $property->weekly_threshold_nights  ?? 7 ) );
        $monthly_th  = max( 1, (int) ( $property->monthly_threshold_nights ?? 28 ) );

        $applied_pct = 0;
        $applied_label = '';
        if ( $monthly_pct > 0 && $nights >= $monthly_th ) {
            $applied_pct = $monthly_pct;
            $applied_label = 'monthly';
        } elseif ( $weekly_pct > 0 && $nights >= $weekly_th ) {
            $applied_pct = $weekly_pct;
            $applied_label = 'weekly';
        }
        $discount_amount = $applied_pct > 0 ? round( $subtotal_raw * $applied_pct / 100, 2 ) : 0.0;
        $subtotal = round( $subtotal_raw - $discount_amount, 2 );

        $cleaning = (float) $property->cleaning_fee;
        $tax_rate = (float) Nira_Settings::get( 'tax_rate', 0 );
        $tourist  = (float) Nira_Settings::get( 'tourist_tax', 0 );
        $tourist_total = round( $tourist * $nights * max( 1, (int) $guest_count ), 2 );
        $taxes    = round( ( $subtotal + $cleaning ) * $tax_rate / 100, 2 ) + $tourist_total;
        $total    = round( $subtotal + $cleaning + $taxes, 2 );
        $deposit  = round( $total * ( (int) $property->deposit_pct ) / 100, 2 );

        return [
            'nights'       => $nights,
            'subtotal_raw' => round( $subtotal_raw, 2 ),
            'subtotal'     => $subtotal,
            'discount'     => $discount_amount,
            'discount_pct' => $applied_pct,
            'discount_type'=> $applied_label,
            'cleaning_fee' => $cleaning,
            'taxes'        => $taxes,
            'tourist_tax'  => $tourist_total,
            'total'        => $total,
            'deposit'      => $deposit,
            'breakdown'    => $breakdown,
            'avg_nightly'  => $nights > 0 ? round( $subtotal_raw / $nights, 2 ) : 0,
        ];
    }

    public static function count_nights( $check_in, $check_out ) {
        $a = new DateTime( $check_in );
        $b = new DateTime( $check_out );
        return max( 0, (int) $a->diff( $b )->days );
    }
}

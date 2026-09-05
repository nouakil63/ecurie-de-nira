<?php
/**
 * CRUD des hébergements (gîtes).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Properties {

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function all( $only_active = false ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'properties' );
        $where = $only_active ? " WHERE status = 'active'" : '';
        return $wpdb->get_results( "SELECT * FROM {$table}{$where} ORDER BY sort_order ASC, id ASC" );
    }

    public function get( $id ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'properties' );
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", (int) $id ) );
    }

    public function get_by_slug( $slug ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'properties' );
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE slug = %s", $slug ) );
    }

    public function create( $data ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'properties' );
        $row = $this->sanitize( $data );
        $row['slug'] = ! empty( $data['slug'] ) ? sanitize_title( $data['slug'] ) : sanitize_title( $data['name'] ?? 'gite' );
        $row['created_at'] = current_time( 'mysql' );
        $wpdb->insert( $table, $row );
        return (int) $wpdb->insert_id;
    }

    public function update( $id, $data ) {
        global $wpdb;
        $table = Nira_DB::tbl( 'properties' );
        $row = $this->sanitize( $data );
        if ( ! empty( $data['slug'] ) ) {
            $row['slug'] = sanitize_title( $data['slug'] );
        }
        return $wpdb->update( $table, $row, [ 'id' => (int) $id ] );
    }

    public function delete( $id ) {
        global $wpdb;
        return $wpdb->delete( Nira_DB::tbl( 'properties' ), [ 'id' => (int) $id ] );
    }

    private function sanitize( $data ) {
        $row = [];
        $map = [
            'name'                => 'text',
            'capacity'            => 'int',
            'bedrooms'            => 'int',
            'bathrooms'           => 'int',
            'base_price'          => 'float',
            'cleaning_fee'        => 'float',
            'min_nights'          => 'int',
            'max_nights'          => 'int',
            'deposit_pct'         => 'int',
            'checkin_time'        => 'text',
            'checkout_time'       => 'text',
            'cancellation_policy' => 'text',
            'description'         => 'textarea',
            'amenities'           => 'textarea',
            'airbnb_url'              => 'url',
            'cover_image'             => 'text',
            'status'                  => 'text',
            'sort_order'              => 'int',
            'weekly_discount_pct'     => 'int',
            'monthly_discount_pct'    => 'int',
            'weekly_threshold_nights' => 'int',
            'monthly_threshold_nights'=> 'int',
        ];
        foreach ( $map as $key => $type ) {
            if ( ! array_key_exists( $key, $data ) ) {
                continue;
            }
            $v = $data[ $key ];
            switch ( $type ) {
                case 'int':      $row[ $key ] = (int) $v; break;
                case 'float':    $row[ $key ] = (float) $v; break;
                case 'textarea': $row[ $key ] = sanitize_textarea_field( $v ); break;
                case 'url':      $row[ $key ] = esc_url_raw( $v ); break;
                default:         $row[ $key ] = sanitize_text_field( $v ); break;
            }
        }
        return $row;
    }

    public static function amenities_list( $property ) {
        $raw = trim( (string) $property->amenities );
        if ( '' === $raw ) return [];
        return array_values( array_filter( array_map( 'trim', preg_split( "/\r\n|\n|\r/", $raw ) ) ) );
    }
}

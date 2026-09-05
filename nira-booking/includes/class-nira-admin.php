<?php
/**
 * Interface d'administration : menu + pages en rendu serveur.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Admin {

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_menu', [ $this, 'menu' ] );
        add_action( 'admin_init', [ $this, 'handle_post' ] );
        add_action( 'admin_notices', [ $this, 'notices' ] );
    }

    public function menu() {
        $cap = 'manage_options';
        add_menu_page(
            __( 'Nira Booking', 'nira-booking' ),
            __( 'Nira Booking', 'nira-booking' ),
            $cap, 'nira-booking',
            [ $this, 'page_dashboard' ], 'dashicons-calendar-alt', 26
        );
        add_submenu_page( 'nira-booking', __( 'Tableau de bord', 'nira-booking' ), __( 'Tableau de bord', 'nira-booking' ), $cap, 'nira-booking',    [ $this, 'page_dashboard' ] );
        add_submenu_page( 'nira-booking', __( 'Réservations', 'nira-booking' ),    __( 'Réservations', 'nira-booking' ),    $cap, 'nira-bookings',   [ $this, 'page_bookings' ] );
        add_submenu_page( 'nira-booking', __( 'Calendrier', 'nira-booking' ),      __( 'Calendrier', 'nira-booking' ),      $cap, 'nira-calendar',   [ $this, 'page_calendar' ] );
        add_submenu_page( 'nira-booking', __( 'Hébergements', 'nira-booking' ),    __( 'Hébergements', 'nira-booking' ),    $cap, 'nira-properties', [ $this, 'page_properties' ] );
        add_submenu_page( 'nira-booking', __( 'Tarifs', 'nira-booking' ),          __( 'Tarifs', 'nira-booking' ),          $cap, 'nira-pricing',    [ $this, 'page_pricing' ] );
        add_submenu_page( 'nira-booking', __( 'Synchro Airbnb', 'nira-booking' ),  __( 'Synchro Airbnb', 'nira-booking' ),  $cap, 'nira-sync',       [ $this, 'page_sync' ] );
        add_submenu_page( 'nira-booking', __( 'Images des pages', 'nira-booking' ), __( 'Images des pages', 'nira-booking' ), $cap, 'nira-page-images', [ $this, 'page_images_admin' ] );
        add_submenu_page( 'nira-booking', __( 'Réglages', 'nira-booking' ),        __( 'Réglages', 'nira-booking' ),        $cap, 'nira-settings',   [ $this, 'page_settings' ] );
    }

    public function notices() {
        if ( ! empty( $_GET['nira_notice'] ) ) {
            $msg = sanitize_text_field( wp_unslash( $_GET['nira_notice'] ) );
            $type = in_array( $_GET['nira_type'] ?? 'success', [ 'success', 'error', 'warning' ], true ) ? $_GET['nira_type'] : 'success';
            echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
        }

        // Rappel permanent tant que le mode test est actif : des vrais clients
        // pourraient réserver à 1 € si on oublie de le couper.
        $tm = self::get_test_mode();
        if ( $tm ) {
            $property = Nira_Properties::instance()->get( (int) $tm['property_id'] );
            printf(
                '<div class="notice notice-warning"><p><strong>⚠ %s</strong> %s <a href="%s">%s</a></p></div>',
                esc_html__( 'MODE TEST ACTIF :', 'nira-booking' ),
                esc_html( sprintf(
                    __( '« %s » est affiché à 1 €/nuit depuis le %s. N\'oubliez pas de le désactiver après votre test.', 'nira-booking' ),
                    $property->name ?? '#' . (int) $tm['property_id'],
                    $tm['activated_at'] ?? ''
                ) ),
                esc_url( admin_url( 'admin.php?page=nira-settings' ) ),
                esc_html__( 'Désactiver et restaurer les prix', 'nira-booking' )
            );
        }
    }

    /* ---------------- POST handlers ---------------- */

    public function handle_post() {
        if ( empty( $_POST['nira_action'] ) || ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $action = sanitize_key( $_POST['nira_action'] );
        check_admin_referer( 'nira_admin_' . $action );

        switch ( $action ) {
            case 'save_settings':    $this->save_settings(); break;
            case 'save_property':    $this->save_property(); break;
            case 'delete_property':  $this->delete_property(); break;
            case 'save_pricing':     $this->save_pricing(); break;
            case 'delete_pricing':   $this->delete_pricing(); break;
            case 'save_gites_images':  $this->save_gites_images(); break;
            case 'save_page_images':   $this->save_page_images_admin(); break;
            case 'save_feed':        $this->save_feed(); break;
            case 'delete_feed':      $this->delete_feed(); break;
            case 'sync_feed':        $this->sync_feed(); break;
            case 'sync_all':         $this->sync_all(); break;
            case 'save_booking':     $this->save_booking(); break;
            case 'cancel_booking':   $this->cancel_booking(); break;
            case 'send_balance_request': $this->send_balance_request(); break;
            case 'send_confirmation_email': $this->send_confirmation_email(); break;
            case 'delete_booking':   $this->delete_booking(); break;
            case 'block_dates':      $this->block_dates(); break;
            case 'test_mode_on':     $this->test_mode_on(); break;
            case 'test_mode_off':    $this->test_mode_off(); break;
        }
    }

    /* ---------------- Mode test (1 €) ---------------- */

    /**
     * État du mode test : tableau snapshot ou null.
     * Le snapshot contient les vrais prix, stockés en option WordPress,
     * pour une restauration automatique à la désactivation.
     */
    public static function get_test_mode() {
        $tm = get_option( 'nira_test_mode', null );
        return is_array( $tm ) ? $tm : null;
    }

    private function test_mode_on() {
        global $wpdb;

        if ( self::get_test_mode() ) {
            $this->redirect( 'nira-settings', __( 'Le mode test est déjà actif. Désactivez-le avant de le relancer.', 'nira-booking' ), 'warning' );
        }

        $property_id = (int) ( $_POST['property_id'] ?? 0 );
        $property    = Nira_Properties::instance()->get( $property_id );
        if ( ! $property ) {
            $this->redirect( 'nira-settings', __( 'Hébergement introuvable.', 'nira-booking' ), 'error' );
        }

        // 1) Snapshot des valeurs actuelles (prix + règles tarifaires)
        $snapshot = [
            'property_id'  => $property_id,
            'property'     => [
                'base_price'           => $property->base_price,
                'cleaning_fee'         => $property->cleaning_fee,
                'deposit_pct'          => $property->deposit_pct,
                'min_nights'           => $property->min_nights,
                'weekly_discount_pct'  => $property->weekly_discount_pct,
                'monthly_discount_pct' => $property->monthly_discount_pct,
            ],
            'rules'        => [],
            'activated_at' => current_time( 'mysql' ),
        ];
        foreach ( Nira_Pricing::get_rules( $property_id ) as $rule ) {
            $snapshot['rules'][ (int) $rule->id ] = [
                'price'      => $rule->price,
                'min_nights' => $rule->min_nights,
            ];
        }
        update_option( 'nira_test_mode', $snapshot, false );

        // 2) Passage à 1 € : prix de base ET règles saisonnières/hebdo
        // (les règles priment sur le prix de base, il faut les écraser aussi).
        // Acompte à 100 % : 30 % de 1 € serait sous le minimum Stripe (0,50 €)
        // et le paiement échouerait.
        $wpdb->update( Nira_DB::tbl( 'properties' ), [
            'base_price'           => 1.00,
            'cleaning_fee'         => 0,
            'deposit_pct'          => 100,
            'min_nights'           => 1,
            'weekly_discount_pct'  => 0,
            'monthly_discount_pct' => 0,
        ], [ 'id' => $property_id ] );
        foreach ( array_keys( $snapshot['rules'] ) as $rule_id ) {
            $wpdb->update( Nira_DB::tbl( 'pricing_rules' ), [ 'price' => 1.00, 'min_nights' => 1 ], [ 'id' => $rule_id ] );
        }

        $this->redirect( 'nira-settings', sprintf(
            __( 'Mode test activé : « %s » est à 1 €/nuit (ménage 0 €, paiement intégral, 1 nuit min). Les vrais prix sont sauvegardés et seront restaurés à la désactivation.', 'nira-booking' ),
            $property->name
        ), 'warning' );
    }

    private function test_mode_off() {
        global $wpdb;

        $snapshot = self::get_test_mode();
        if ( ! $snapshot ) {
            $this->redirect( 'nira-settings', __( 'Aucun mode test actif.', 'nira-booking' ), 'warning' );
        }

        $wpdb->update( Nira_DB::tbl( 'properties' ), $snapshot['property'], [ 'id' => (int) $snapshot['property_id'] ] );
        foreach ( $snapshot['rules'] as $rule_id => $vals ) {
            $wpdb->update( Nira_DB::tbl( 'pricing_rules' ), [
                'price'      => $vals['price'],
                'min_nights' => $vals['min_nights'],
            ], [ 'id' => (int) $rule_id ] );
        }
        delete_option( 'nira_test_mode' );

        $this->redirect( 'nira-settings', __( 'Mode test désactivé : les vrais prix ont été restaurés.', 'nira-booking' ) );
    }

    private function send_balance_request() {
        $id = (int) ( $_POST['id'] ?? 0 );
        $sent = Nira_Email::send_balance_request( $id );
        $msg = $sent
            ? __( 'Email de demande de solde envoyé au client.', 'nira-booking' )
            : __( "Aucun solde à régler ou email indisponible.", 'nira-booking' );
        $this->redirect( 'nira-bookings', $msg, $sent ? 'success' : 'warning', [ 'edit' => $id ] );
    }

    private function send_confirmation_email() {
        $id = (int) ( $_POST['id'] ?? 0 );
        Nira_Email::send_confirmation( $id );
        $this->redirect( 'nira-bookings', __( 'Email de confirmation renvoyé.', 'nira-booking' ), 'success', [ 'edit' => $id ] );
    }

    private function redirect( $page, $notice, $type = 'success', $extra = [] ) {
        $args = array_merge( [
            'page'        => $page,
            'nira_notice' => rawurlencode( $notice ),
            'nira_type'   => $type,
        ], $extra );
        wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
        exit;
    }

    private function save_settings() {
        Nira_Settings::update_from_post( wp_unslash( $_POST ) );
        $this->redirect( 'nira-settings', __( 'Réglages sauvegardés.', 'nira-booking' ) );
    }

    private function save_gites_images() {
        foreach ( self::gites_image_fields() as $key => $_cfg ) {
            $url = isset( $_POST[ $key ] ) ? esc_url_raw( wp_unslash( $_POST[ $key ] ) ) : '';
            update_option( 'nira_gites_' . $key, $url );
        }
        $this->redirect( 'nira-page-images', __( 'Images enregistrées.', 'nira-booking' ), 'success', [ 'tpl' => 'gites' ] );
    }

    private function save_page_images_admin() {
        $tpl    = sanitize_key( $_POST['tpl'] ?? '' );
        $groups = self::all_page_image_groups();
        if ( ! isset( $groups[ $tpl ] ) ) return;
        foreach ( $groups[ $tpl ]['fields'] as $key => $_cfg ) {
            $url = isset( $_POST[ $key ] ) ? esc_url_raw( wp_unslash( $_POST[ $key ] ) ) : '';
            update_option( 'nira_' . $tpl . '_' . $key, $url );
        }
        self::purge_frontend_cache();
        $this->redirect( 'nira-page-images', __( 'Images enregistrées (cache purgé).', 'nira-booking' ), 'success', [ 'tpl' => $tpl ] );
    }

    /**
     * Purge les caches frontaux connus après mise à jour des images de page.
     */
    public static function purge_frontend_cache() {
        // WP Fastest Cache
        if ( function_exists( 'wpfc_clear_all_cache' ) ) {
            wpfc_clear_all_cache( true );
        }
        // W3 Total Cache
        if ( function_exists( 'w3tc_flush_all' ) ) {
            w3tc_flush_all();
        }
        // WP Super Cache
        if ( function_exists( 'wp_cache_clear_cache' ) ) {
            wp_cache_clear_cache();
        }
        // LiteSpeed Cache
        if ( class_exists( '\LiteSpeed\Purge' ) && method_exists( '\LiteSpeed\Purge', 'purge_all' ) ) {
            \LiteSpeed\Purge::purge_all();
        }
        // WP Rocket
        if ( function_exists( 'rocket_clean_domain' ) ) {
            rocket_clean_domain();
        }
        // WP Core
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }
    }

    public function page_images_admin() {
        wp_enqueue_media();
        self::migrate_legacy_page_image_keys();
        $groups  = self::all_page_image_groups();
        $current = sanitize_key( $_GET['tpl'] ?? array_key_first( $groups ) );
        if ( ! isset( $groups[ $current ] ) ) {
            $current = array_key_first( $groups );
        }
        $fields = $groups[ $current ]['fields'];
        $values = [];
        foreach ( $fields as $key => $_cfg ) {
            $values[ $key ] = get_option( 'nira_' . $current . '_' . $key, '' );
        }
        include NIRA_BOOKING_PATH . 'templates/admin/page-images.php';
    }

    /**
     * Migre les URLs sauvegardées sous d'anciennes clés vers les nouvelles.
     * Exécuté une seule fois (flag en option) ou à chaque consultation admin si
     * la nouvelle clé est vide alors que l'ancienne est remplie.
     */
    public static function migrate_legacy_page_image_keys() {
        // Map : [ group => [ new_key => [ legacy_keys... ] ] ]
        $map = [
            'balneo' => [
                'hero_bg'   => [ 'hero_img' ],
                'collage_1' => [ 'gallery_1' ],
                'collage_2' => [ 'pool_img', 'gallery_2' ],
                'collage_3' => [ 'gallery_3' ],
                'article_1' => [ 'atmosphere' ],
            ],
            'debourrage' => [
                'hero_bg' => [ 'hero_img' ],
                'method'  => [ 'methode' ],
            ],
            'pension_tarifs' => [
                'hero'      => [ 'hero_img' ],
                'quotidien' => [ 'atmosphere' ],
            ],
            'pension_travail' => [
                'hero'      => [ 'hero_img' ],
                'quotidien' => [ 'atmosphere' ],
                'coaching'  => [ 'facilities' ],
            ],
        ];
        foreach ( $map as $group => $pairs ) {
            foreach ( $pairs as $new_key => $legacy_keys ) {
                $new_opt = 'nira_' . $group . '_' . $new_key;
                if ( ! empty( get_option( $new_opt ) ) ) continue;
                foreach ( $legacy_keys as $legacy_key ) {
                    $legacy_opt = 'nira_' . $group . '_' . $legacy_key;
                    $legacy_val = get_option( $legacy_opt );
                    if ( ! empty( $legacy_val ) ) {
                        update_option( $new_opt, $legacy_val );
                        break;
                    }
                }
            }
        }
    }

    /**
     * Définition de tous les groupes d'images par template de page.
     * Clé de groupe = préfixe option (nira_<groupe>_<key>).
     */
    public static function all_page_image_groups() {
        return [
            'gites' => [
                'label'  => 'Gîtes & Séjours',
                'fields' => self::gites_image_fields(),
            ],
            'accueil' => [
                'label'  => 'Accueil',
                'fields' => [
                    'logo_white'      => [ 'label' => 'Logo clair (fond sombre / hero)',         'default' => 'cropped-logo-blanc-1.png' ],
                    'logo_dark'       => [ 'label' => 'Logo foncé (scroll + footer)',             'default' => 'logo-OK-VECTO.png' ],
                    'hero_bg'         => [ 'label' => 'Image de fond hero',                       'default' => 'IMG_6526-scaled.jpeg' ],
                    'ecurie_large'    => [ 'label' => 'Section présentation — grande image',      'default' => '20260305-Deauville-LucieJOUR-6-scaled.jpeg' ],
                    'ecurie_small'    => [ 'label' => 'Section présentation — petite image',      'default' => 'a-mettre-pour-valorisation-et-debourrage-1.jpg' ],
                    'pension_box'     => [ 'label' => 'Carte pension box',                        'default' => 'IMG_1872.jpeg' ],
                    'pension_travail' => [ 'label' => 'Carte pension travail',                    'default' => 'IMG_7770.jpeg' ],
                    'pension_passage' => [ 'label' => 'Carte pension passage',                    'default' => 'IMG_5156.jpeg' ],
                    'situation_large' => [ 'label' => 'Section situation — grande image',         'default' => 'IMG_5156.jpeg' ],
                    'situation_small' => [ 'label' => 'Section situation — petite image',         'default' => 'IMG_7770-scaled.jpeg' ],
                    'label_1'         => [ 'label' => 'Label / certification 1',                  'default' => 'macaron-engagement-1-150x150-1.png' ],
                    'label_2'         => [ 'label' => 'Label / certification 2',                  'default' => 'EquuRES241220-Certificat-Engagement-Les-Ecuries-de-Nira-1-300x212-1.jpg' ],
                    'label_3'         => [ 'label' => 'Label / certification 3',                  'default' => 'union-europeenne.png' ],
                    'gallery_1'       => [ 'label' => 'Galerie photo 1',                          'default' => '385320245_290035883964491_4208930849250831231_n-1024x768-1.jpg' ],
                    'gallery_2'       => [ 'label' => 'Galerie photo 2',                          'default' => '385037290_290035957297817_8425971591010244386_n.jpg' ],
                    'gallery_3'       => [ 'label' => 'Galerie photo 3',                          'default' => 'IMG_6516-scaled.jpeg' ],
                    'gallery_4'       => [ 'label' => 'Galerie photo 4',                          'default' => 'dfbbe465-6d40-4dfc-b6d0-a7aab2ed805c-600x600-2.jpeg' ],
                    'gallery_5'       => [ 'label' => 'Galerie photo 5',                          'default' => 'IMG_6527-scaled.jpeg' ],
                    'partner_1'       => [ 'label' => 'Logo partenaire 1',                        'default' => 'macaron-engagement-1-150x150-1.png' ],
                    'partner_2'       => [ 'label' => 'Logo partenaire 2',                        'default' => 'region-normandie.png' ],
                    'partner_3'       => [ 'label' => 'Logo partenaire 3',                        'default' => 'union-europeenne.png' ],
                ],
            ],
            'infrastructure' => [
                'label'  => 'Infrastructures',
                'fields' => [
                    'logo_white'  => [ 'label' => 'Logo clair',                            'default' => 'cropped-logo-blanc-1.png' ],
                    'logo_dark'   => [ 'label' => 'Logo foncé',                            'default' => 'logo-OK-VECTO.png' ],
                    'carriere'    => [ 'label' => 'Photo carrière / espaces de travail',   'default' => 'IMG_7770.jpeg' ],
                    'boxes'       => [ 'label' => 'Photo boxes / écuries',                 'default' => 'image00018.jpeg' ],
                    'paddocks'    => [ 'label' => 'Photo paddocks',                        'default' => 'IMG_5156.jpeg' ],
                    'gallery_1'   => [ 'label' => 'Galerie bas de page — photo 1',         'default' => 'image00018.jpeg' ],
                    'gallery_2'   => [ 'label' => 'Galerie bas de page — photo 2',         'default' => 'image00019.jpeg' ],
                    'gallery_3'   => [ 'label' => 'Galerie bas de page — photo 3',         'default' => '385320245_290035883964491_4208930849250831231_n-1024x768.jpg' ],
                    'partner_1'   => [ 'label' => 'Logo partenaire 1',                    'default' => 'macaron-engagement-1-150x150.png' ],
                    'partner_2'   => [ 'label' => 'Logo partenaire 2',                    'default' => 'region-normandie.png' ],
                    'partner_3'   => [ 'label' => 'Logo partenaire 3',                    'default' => 'union-europeenne.png' ],
                    'footer_logo' => [ 'label' => 'Logo footer',                           'default' => 'logo-OK-VECTO.png' ],
                ],
            ],
            'debourrage' => [
                'label'  => 'Débourrage',
                'fields' => [
                    'logo_white'  => [ 'label' => 'Logo clair',                   'default' => 'cropped-logo-blanc-1.png' ],
                    'logo_dark'   => [ 'label' => 'Logo foncé',                   'default' => 'logo-OK-VECTO.png' ],
                    'hero_bg'     => [ 'label' => 'Image de fond hero',           'default' => 'IMG_1872.jpeg' ],
                    'method'      => [ 'label' => 'Photo section méthode',        'default' => 'Capture-2026-04-11-11.30.56.jpg' ],
                    'footer_logo' => [ 'label' => 'Logo footer',                  'default' => 'logo-OK-VECTO.png' ],
                    'partner_1'   => [ 'label' => 'Logo partenaire 1',            'default' => 'macaron-engagement-1-150x150-1.png' ],
                    'partner_2'   => [ 'label' => 'Logo partenaire 2',            'default' => 'region-normandie.png' ],
                    'partner_3'   => [ 'label' => 'Logo partenaire 3',            'default' => 'union-europeenne.png' ],
                ],
            ],
            'balneo' => [
                'label'  => 'Balnéothérapie',
                'fields' => [
                    'logo_white'  => [ 'label' => 'Logo clair',                'default' => 'cropped-logo-blanc-1.png' ],
                    'logo_dark'   => [ 'label' => 'Logo foncé',                'default' => 'logo-OK-VECTO.png' ],
                    'hero_bg'     => [ 'label' => 'Image de fond hero',        'default' => 'IMG_2533-scaled.jpg' ],
                    'collage_1'   => [ 'label' => 'Collage — image 1',         'default' => 'IMG_2459-scaled.jpg' ],
                    'collage_2'   => [ 'label' => 'Collage — image 2',         'default' => 'new.jpg' ],
                    'collage_3'   => [ 'label' => 'Collage — image 3',         'default' => '20260305-Deauville-LucieJOUR-5.jpeg' ],
                    'article_1'   => [ 'label' => 'Article / section — image 1', 'default' => 'adc09e0b-95d0-4042-8d1f-f670ac7e2456v2.jpg' ],
                    'article_2'   => [ 'label' => 'Article / section — image 2', 'default' => 'dfbbe465-6d40-4dfc-b6d0-a7aab2ed805c-600x600.jpeg' ],
                    'footer_logo' => [ 'label' => 'Logo footer',               'default' => 'logo-OK-VECTO.png' ],
                    'partner_1'   => [ 'label' => 'Logo partenaire 1',         'default' => 'macaron-engagement-1-150x150-1.png' ],
                    'partner_2'   => [ 'label' => 'Logo partenaire 2',         'default' => 'region-normandie.png' ],
                    'partner_3'   => [ 'label' => 'Logo partenaire 3',         'default' => 'union-europeenne.png' ],
                ],
            ],
            'pension_tarifs' => [
                'label'  => 'Pension et tarifs',
                'fields' => [
                    'logo_white'  => [ 'label' => 'Logo clair',                    'default' => 'cropped-logo-blanc-1.png' ],
                    'logo_dark'   => [ 'label' => 'Logo foncé',                    'default' => 'logo-OK-VECTO.png' ],
                    'hero'        => [ 'label' => 'Photo hero',                    'default' => 'IMG_7770.jpeg' ],
                    'coaching'    => [ 'label' => 'Photo section coaching',        'default' => 'Capture-2026-04-10-19.55.09.jpg' ],
                    'quotidien'   => [ 'label' => 'Photo section quotidien',       'default' => 'IMG_1872.jpeg' ],
                    'competition' => [ 'label' => 'Photo section compétition',     'default' => '59AFF348-F0C6-4425-8989-B13ECEE3AC69.jpeg' ],
                    'footer_logo' => [ 'label' => 'Logo footer',                   'default' => 'logo-OK-VECTO.png' ],
                    'partner_1'   => [ 'label' => 'Logo partenaire 1',             'default' => 'macaron-engagement-1-150x150-1.png' ],
                    'partner_2'   => [ 'label' => 'Logo partenaire 2',             'default' => 'region-normandie.png' ],
                    'partner_3'   => [ 'label' => 'Logo partenaire 3',             'default' => 'union-europeenne.png' ],
                ],
            ],
            'pension_box' => [
                'label'  => 'Pension Box/Paddock',
                'fields' => [
                    'logo_white'  => [ 'label' => 'Logo clair',                    'default' => 'cropped-logo-blanc-1.png' ],
                    'logo_dark'   => [ 'label' => 'Logo foncé',                    'default' => 'logo-OK-VECTO.png' ],
                    'hero_img'    => [ 'label' => 'Photo hero (box/paddock)',       'default' => 'IMG-2991-scaled.jpg' ],
                    'atmosphere'  => [ 'label' => 'Photo fond parallaxe',          'default' => '385037290_290035957297817_8425971591010244386_n.jpg' ],
                    'facilities'  => [ 'label' => 'Photo section installations',   'default' => 'IMG_7770.jpeg' ],
                    'partner_1'   => [ 'label' => 'Logo partenaire 1',             'default' => 'macaron-engagement-1-150x150.png' ],
                    'partner_2'   => [ 'label' => 'Logo partenaire 2',             'default' => 'region-normandie.png' ],
                    'partner_3'   => [ 'label' => 'Logo partenaire 3',             'default' => 'union-europeenne.png' ],
                    'footer_logo' => [ 'label' => 'Logo footer',                   'default' => 'logo-OK-VECTO.png' ],
                ],
            ],
            'pension_travail' => [
                'label'  => 'Pension Travail',
                'fields' => [
                    'logo_white'  => [ 'label' => 'Logo clair',                    'default' => 'cropped-logo-blanc-1.png' ],
                    'logo_dark'   => [ 'label' => 'Logo foncé',                    'default' => 'logo-OK-VECTO.png' ],
                    'hero'        => [ 'label' => 'Photo hero',                    'default' => '84F201E1-7A80-43B9-805D-E3C8ACBA00F7.jpeg' ],
                    'coaching'    => [ 'label' => 'Photo section coaching',        'default' => 'IMG_3038.jpeg' ],
                    'quotidien'   => [ 'label' => 'Photo section quotidien',       'default' => 'IMG_1872.jpeg' ],
                    'competition' => [ 'label' => 'Photo section compétition',     'default' => 'image00032.jpeg' ],
                    'footer_logo' => [ 'label' => 'Logo footer',                   'default' => 'logo-OK-VECTO.png' ],
                    'partner_1'   => [ 'label' => 'Logo partenaire 1',             'default' => 'macaron-engagement-1-150x150-1.png' ],
                    'partner_2'   => [ 'label' => 'Logo partenaire 2',             'default' => 'region-normandie.png' ],
                    'partner_3'   => [ 'label' => 'Logo partenaire 3',             'default' => 'union-europeenne.png' ],
                ],
            ],
            'contact' => [
                'label'  => 'Contact',
                'fields' => [
                    'logo_white'  => [ 'label' => 'Logo clair',               'default' => 'cropped-logo-blanc-1.png' ],
                    'logo_dark'   => [ 'label' => 'Logo foncé',               'default' => 'logo-OK-VECTO.png' ],
                    'hero_bg'     => [ 'label' => 'Image de fond hero',       'default' => 'IMG_6526-scaled.jpeg' ],
                    'footer_logo' => [ 'label' => 'Logo footer',              'default' => 'logo-OK-VECTO.png' ],
                    'partner_1'   => [ 'label' => 'Logo partenaire 1',        'default' => 'macaron-engagement-1-150x150-1.png' ],
                    'partner_2'   => [ 'label' => 'Logo partenaire 2',        'default' => 'region-normandie.png' ],
                    'partner_3'   => [ 'label' => 'Logo partenaire 3',        'default' => 'union-europeenne.png' ],
                ],
            ],
        ];
    }

    private function save_property() {
        $id = (int) ( $_POST['id'] ?? 0 );
        $data = wp_unslash( $_POST );
        if ( $id ) {
            Nira_Properties::instance()->update( $id, $data );
        } else {
            $id = Nira_Properties::instance()->create( $data );
        }
        $this->redirect( 'nira-properties', __( 'Hébergement enregistré.', 'nira-booking' ), 'success', [ 'edit' => $id ] );
    }

    private function delete_property() {
        Nira_Properties::instance()->delete( (int) $_POST['id'] );
        $this->redirect( 'nira-properties', __( 'Hébergement supprimé.', 'nira-booking' ) );
    }

    private function save_pricing() {
        $id = (int) ( $_POST['id'] ?? 0 );
        $data = wp_unslash( $_POST );
        if ( $id ) {
            Nira_Pricing::update_rule( $id, $data );
        } else {
            Nira_Pricing::create_rule( $data );
        }
        $this->redirect( 'nira-pricing', __( 'Période tarifaire enregistrée.', 'nira-booking' ), 'success', [ 'property_id' => (int) $data['property_id'] ] );
    }

    private function delete_pricing() {
        Nira_Pricing::delete_rule( (int) $_POST['id'] );
        $this->redirect( 'nira-pricing', __( 'Période supprimée.', 'nira-booking' ), 'success', [ 'property_id' => (int) ( $_POST['property_id'] ?? 0 ) ] );
    }

    private function save_feed() {
        Nira_Ical::add_feed(
            (int) $_POST['property_id'],
            sanitize_text_field( wp_unslash( $_POST['label'] ?? 'Airbnb' ) ),
            esc_url_raw( wp_unslash( $_POST['url'] ?? '' ) )
        );
        $this->redirect( 'nira-sync', __( 'Flux ajouté.', 'nira-booking' ) );
    }

    private function delete_feed() {
        Nira_Ical::delete_feed( (int) $_POST['id'] );
        $this->redirect( 'nira-sync', __( 'Flux supprimé.', 'nira-booking' ) );
    }

    private function sync_feed() {
        $feed = Nira_Ical::get_feed( (int) $_POST['id'] );
        if ( $feed ) {
            $count = (int) Nira_Ical::instance()->sync_feed( $feed );
            $this->redirect( 'nira-sync', sprintf( __( 'Synchronisation OK : %d événements.', 'nira-booking' ), $count ) );
        }
        $this->redirect( 'nira-sync', __( 'Flux introuvable.', 'nira-booking' ), 'error' );
    }

    private function sync_all() {
        $count = (int) Nira_Ical::sync_all();
        $this->redirect( 'nira-sync', sprintf( __( 'Synchronisation globale : %d événements.', 'nira-booking' ), $count ) );
    }

    private function save_booking() {
        global $wpdb;
        $id   = (int) ( $_POST['id'] ?? 0 );
        $data = wp_unslash( $_POST );

        $fields = [
            'guest_name'  => sanitize_text_field( $data['guest_name'] ?? '' ),
            'guest_email' => sanitize_email( $data['guest_email'] ?? '' ),
            'guest_phone' => sanitize_text_field( $data['guest_phone'] ?? '' ),
            'guest_count' => (int) ( $data['guest_count'] ?? 1 ),
            'check_in'    => sanitize_text_field( $data['check_in'] ?? '' ),
            'check_out'   => sanitize_text_field( $data['check_out'] ?? '' ),
            'status'      => sanitize_text_field( $data['status'] ?? 'pending' ),
            'notes'       => sanitize_textarea_field( $data['notes'] ?? '' ),
            'updated_at'  => current_time( 'mysql' ),
        ];

        $allowed_statuses = [ 'pending', 'confirmed', 'cancelled', 'refunded', 'blocked', 'airbnb' ];
        if ( ! in_array( $fields['status'], $allowed_statuses, true ) ) {
            $fields['status'] = 'pending';
        }
        if ( ! Nira_Booking::valid_date( $fields['check_in'] ) || ! Nira_Booking::valid_date( $fields['check_out'] )
             || $fields['check_in'] >= $fields['check_out'] ) {
            $this->redirect( 'nira-bookings', __( 'Dates invalides.', 'nira-booking' ), 'error' );
        }

        if ( $id ) {
            $existing = Nira_Booking::get( $id );
            if ( $existing
                 && in_array( $fields['status'], [ 'pending', 'confirmed', 'blocked' ], true )
                 && ! Nira_Availability::is_range_available( (int) $existing->property_id, $fields['check_in'], $fields['check_out'], $id ) ) {
                $this->redirect( 'nira-bookings', __( 'Ces dates chevauchent une autre réservation.', 'nira-booking' ), 'error', [ 'edit' => $id ] );
            }
            $fields['nights'] = Nira_Pricing::count_nights( $fields['check_in'], $fields['check_out'] );
            $wpdb->update( Nira_DB::tbl( 'bookings' ), $fields, [ 'id' => $id ] );
        } else {
            $created = Nira_Booking::create( array_merge( $fields, [
                'property_id' => (int) $data['property_id'],
                'source'      => 'manual',
            ] ) );
            if ( is_wp_error( $created ) ) {
                $this->redirect( 'nira-bookings', $created->get_error_message(), 'error' );
            }
            $id = $created;
            if ( ! empty( $data['mark_confirmed'] ) ) {
                Nira_Booking::update_status( $id, 'confirmed' );
            }
        }
        $this->redirect( 'nira-bookings', __( 'Réservation enregistrée.', 'nira-booking' ), 'success', [ 'edit' => $id ] );
    }

    private function cancel_booking() {
        $id = (int) $_POST['id'];
        $res = Nira_Booking::cancel(
            $id,
            sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) ),
            isset( $_POST['refund'] ) && '' !== $_POST['refund'] ? (float) $_POST['refund'] : null
        );
        if ( is_wp_error( $res ) ) {
            $this->redirect( 'nira-bookings', $res->get_error_message(), 'error' );
        }
        // Email de notification au client
        if ( class_exists( 'Nira_Email' ) ) {
            Nira_Email::send_cancellation( $id, (float) $res['refunded'] );
        }
        $this->redirect( 'nira-bookings', sprintf( __( 'Réservation annulée — remboursé : %s €', 'nira-booking' ), number_format( (float) $res['refunded'], 2, ',', ' ' ) ) );
    }

    private function delete_booking() {
        Nira_Booking::delete( (int) $_POST['id'] );
        $this->redirect( 'nira-bookings', __( 'Réservation supprimée.', 'nira-booking' ) );
    }

    private function block_dates() {
        global $wpdb;
        $pid = (int) $_POST['property_id'];
        $in  = sanitize_text_field( wp_unslash( $_POST['check_in'] ?? '' ) );
        $out = sanitize_text_field( wp_unslash( $_POST['check_out'] ?? '' ) );
        if ( ! Nira_Booking::valid_date( $in ) || ! Nira_Booking::valid_date( $out ) || $in >= $out ) {
            $this->redirect( 'nira-calendar', __( 'Dates invalides.', 'nira-booking' ), 'error' );
        }
        $now = current_time( 'mysql' );
        $wpdb->insert( Nira_DB::tbl( 'bookings' ), [
            'reference'     => Nira_Booking::generate_reference(),
            'property_id'   => $pid,
            'guest_name'    => sanitize_text_field( wp_unslash( $_POST['label'] ?? 'Bloc manuel' ) ),
            'guest_email'   => 'block@internal',
            'guest_count'   => 1,
            'check_in'      => $in,
            'check_out'     => $out,
            'nights'        => Nira_Pricing::count_nights( $in, $out ),
            'currency'      => Nira_Settings::get( 'currency', 'EUR' ),
            'source'        => 'manual',
            'status'        => 'blocked',
            'payment_status'=> 'none',
            'created_at'    => $now,
            'updated_at'    => $now,
        ] );
        $this->redirect( 'nira-calendar', __( 'Période bloquée.', 'nira-booking' ), 'success', [ 'property_id' => $pid ] );
    }

    /* ---------------- Page renderers ---------------- */

    public function page_dashboard() {
        $stats = Nira_Booking::stats();
        $properties = Nira_Properties::instance()->all();
        $recent = Nira_Booking::find( [ 'limit' => 10, 'exclude_status' => 'pending' ] );
        include NIRA_BOOKING_PATH . 'templates/admin/dashboard.php';
    }

    public function page_bookings() {
        $action = $_GET['action'] ?? '';
        if ( 'edit' === $action || 'new' === $action ) {
            $booking = 'edit' === $action ? Nira_Booking::get( (int) ( $_GET['id'] ?? 0 ) ) : null;
            $properties = Nira_Properties::instance()->all();
            include NIRA_BOOKING_PATH . 'templates/admin/booking-edit.php';
            return;
        }
        $filters = [
            'status'      => $_GET['status'] ?? '',
            'property_id' => $_GET['property_id'] ?? '',
            'search'      => $_GET['s'] ?? '',
        ];
        // Par défaut, on masque les holds « en attente » : ce sont des paniers
        // en cours de paiement qui expirent seuls, pas des réservations à
        // gérer. Ils restent visibles en choisissant ce statut dans le filtre.
        if ( '' === $filters['status'] ) {
            $filters['exclude_status'] = 'pending';
        }
        $bookings = Nira_Booking::find( $filters );
        $properties = Nira_Properties::instance()->all();
        include NIRA_BOOKING_PATH . 'templates/admin/bookings.php';
    }

    public function page_calendar() {
        $properties = Nira_Properties::instance()->all();
        $pid = (int) ( $_GET['property_id'] ?? ( $properties[0]->id ?? 0 ) );
        $month = sanitize_text_field( $_GET['month'] ?? date( 'Y-m' ) );
        $property = $pid ? Nira_Properties::instance()->get( $pid ) : null;
        $calendar = $property ? Nira_Availability::calendar_payload( $property, $month, 2 ) : [];
        $bookings = $property ? Nira_Availability::get_blocking_bookings( $pid, $month . '-01', date( 'Y-m-d', strtotime( $month . '-01 +3 months' ) ) ) : [];
        include NIRA_BOOKING_PATH . 'templates/admin/calendar.php';
    }

    public function page_properties() {
        $action = $_GET['action'] ?? '';
        if ( 'edit' === $action || 'new' === $action || ! empty( $_GET['edit'] ) ) {
            $id = (int) ( $_GET['id'] ?? $_GET['edit'] ?? 0 );
            $property = $id ? Nira_Properties::instance()->get( $id ) : null;
            include NIRA_BOOKING_PATH . 'templates/admin/property-edit.php';
            return;
        }
        $properties = Nira_Properties::instance()->all();
        include NIRA_BOOKING_PATH . 'templates/admin/properties.php';
    }

    public function page_pricing() {
        $properties = Nira_Properties::instance()->all();
        $pid = (int) ( $_GET['property_id'] ?? ( $properties[0]->id ?? 0 ) );
        $property = $pid ? Nira_Properties::instance()->get( $pid ) : null;
        $rules = $pid ? Nira_Pricing::get_rules( $pid ) : [];
        $edit_rule = ! empty( $_GET['edit'] ) ? Nira_Pricing::get_rule( (int) $_GET['edit'] ) : null;
        include NIRA_BOOKING_PATH . 'templates/admin/pricing.php';
    }

    public function page_sync() {
        $properties = Nira_Properties::instance()->all();
        $feeds = Nira_Ical::feeds();
        include NIRA_BOOKING_PATH . 'templates/admin/sync.php';
    }

    public function page_settings() {
        $settings   = Nira_Settings::all();
        $properties = Nira_Properties::instance()->all();
        $test_mode  = self::get_test_mode();
        include NIRA_BOOKING_PATH . 'templates/admin/settings.php';
    }

    public function page_gites_images() {
        // Requis pour le media picker de WordPress.
        wp_enqueue_media();
        $images = self::gites_image_fields();
        $values = [];
        foreach ( $images as $key => $cfg ) {
            $values[ $key ] = get_option( 'nira_gites_' . $key, '' );
        }
        include NIRA_BOOKING_PATH . 'templates/admin/gites-page.php';
    }

    /**
     * Liste des images configurables pour la page Gîtes.
     */
    public static function gites_image_fields() {
        return [
            'hero_bg'      => [ 'label' => 'Image de fond (bandeau héros)',        'default' => 'IMG_2918-scaled.jpeg' ],
            'logo_white'   => [ 'label' => 'Logo clair (sur fond sombre)',         'default' => 'cropped-logo-blanc-1.png' ],
            'logo_dark'    => [ 'label' => 'Logo foncé (header au scroll + footer)','default' => 'logo-OK-VECTO.png' ],
            'appt_cover'   => [ 'label' => 'Appartement — photo principale',       'default' => 'IMG_3093-scaled.jpeg' ],
            'appt_1'       => [ 'label' => 'Appartement — photo 2',                'default' => 'IMG_3092-1-scaled.jpeg' ],
            'appt_2'       => [ 'label' => 'Appartement — photo 3',                'default' => 'IMG_3091-scaled.jpeg' ],
            'appt_3'       => [ 'label' => 'Appartement — photo 4',                'default' => 'IMG_3090-scaled.jpeg' ],
            'appt_4'       => [ 'label' => 'Appartement — photo 5',                'default' => 'IMG_3088-scaled.jpeg' ],
            'duplex_cover' => [ 'label' => 'Duplex — photo principale',            'default' => 'IMG_3089-2048x1536.jpeg' ],
            'duplex_1'     => [ 'label' => 'Duplex — photo 2',                     'default' => 'IMG_3092-1-scaled.jpeg' ],
            'duplex_2'     => [ 'label' => 'Duplex — photo 3',                     'default' => 'IMG_3091-scaled.jpeg' ],
            'duplex_3'     => [ 'label' => 'Duplex — photo 4',                     'default' => 'IMG_3090-scaled.jpeg' ],
            'duplex_4'     => [ 'label' => 'Duplex — photo 5',                     'default' => 'IMG_3088-scaled.jpeg' ],
            'partner_1'    => [ 'label' => 'Logo partenaire 1',                    'default' => 'macaron-engagement-1-150x150-1.png' ],
            'partner_2'    => [ 'label' => 'Logo partenaire 2',                    'default' => 'region-normandie.png' ],
            'partner_3'    => [ 'label' => 'Logo partenaire 3',                    'default' => 'union-europeenne.png' ],
        ];
    }

    /* ---------------- Helpers for templates ---------------- */

    public static function status_label( $status ) {
        $map = [
            'pending'   => [ 'label' => __( 'En attente', 'nira-booking' ), 'class' => 'pending' ],
            'confirmed' => [ 'label' => __( 'Confirmée', 'nira-booking' ),  'class' => 'confirmed' ],
            'cancelled' => [ 'label' => __( 'Annulée', 'nira-booking' ),    'class' => 'cancelled' ],
            'refunded'  => [ 'label' => __( 'Remboursée', 'nira-booking' ), 'class' => 'refunded' ],
            'blocked'   => [ 'label' => __( 'Bloquée', 'nira-booking' ),    'class' => 'blocked' ],
            'airbnb'    => [ 'label' => __( 'Airbnb', 'nira-booking' ),     'class' => 'airbnb' ],
        ];
        return $map[ $status ] ?? [ 'label' => $status, 'class' => 'default' ];
    }

    public static function money( $amount, $currency = null ) {
        $currency = $currency ?: Nira_Settings::get( 'currency', 'EUR' );
        $symbol = [ 'EUR' => '€', 'USD' => '$', 'GBP' => '£' ][ strtoupper( $currency ) ] ?? $currency;
        return number_format( (float) $amount, 2, ',', ' ' ) . ' ' . $symbol;
    }

    public static function fr_date( $date ) {
        if ( empty( $date ) ) return '—';
        $ts = strtotime( $date );
        return $ts ? wp_date( 'd/m/Y', $ts ) : esc_html( $date );
    }
}
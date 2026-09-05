<?php
/**
 * Nira Contact — formulaire de contact public.
 *
 * - Enregistre un CPT privé `nira_message` pour stocker chaque demande.
 * - Gère la soumission via admin-post (front + déconnecté).
 * - Envoie une notification mail à Margaux + accusé optionnel au visiteur.
 * - Ajoute des colonnes admin lisibles + une option pour l'email destinataire.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class Nira_Contact {

    const CPT     = 'nira_message';
    const ACTION  = 'nira_contact_submit';
    const NONCE   = 'nira_contact_nonce';
    const OPT_TO  = 'nira_contact_email_to';

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        // L'instance étant créée durant l'action `init` (via boot()), on
        // enregistre le CPT immédiatement plutôt que de re-hooker `init`.
        $this->register_cpt();

        add_action( 'admin_post_' . self::ACTION,              [ $this, 'handle_submit' ] );
        add_action( 'admin_post_nopriv_' . self::ACTION,       [ $this, 'handle_submit' ] );

        // Admin UI
        add_filter( 'manage_' . self::CPT . '_posts_columns',  [ $this, 'columns' ] );
        add_action( 'manage_' . self::CPT . '_posts_custom_column', [ $this, 'column_content' ], 10, 2 );
        add_action( 'add_meta_boxes',                          [ $this, 'metabox' ] );
        add_filter( 'post_row_actions',                        [ $this, 'row_actions' ], 10, 2 );

        // Settings registration (champ email destinataire)
        add_action( 'admin_init',                              [ $this, 'register_settings' ] );
    }

    /* ------------------------------------------------------------------ */
    /* CPT                                                                  */
    /* ------------------------------------------------------------------ */

    public function register_cpt() {
        register_post_type( self::CPT, [
            'labels' => [
                'name'               => __( 'Messages', 'nira-booking' ),
                'singular_name'      => __( 'Message', 'nira-booking' ),
                'menu_name'          => __( 'Messages', 'nira-booking' ),
                'all_items'          => __( 'Tous les messages', 'nira-booking' ),
                'view_item'          => __( 'Voir le message', 'nira-booking' ),
                'edit_item'          => __( 'Modifier', 'nira-booking' ),
                'search_items'       => __( 'Rechercher', 'nira-booking' ),
                'not_found'          => __( 'Aucun message', 'nira-booking' ),
                'not_found_in_trash' => __( 'Aucun message dans la corbeille', 'nira-booking' ),
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 27,
            'menu_icon'           => 'dashicons-email-alt',
            'capability_type'     => 'post',
            'capabilities'        => [ 'create_posts' => 'do_not_allow' ],
            'map_meta_cap'        => true,
            'supports'            => [ 'title' ],
            'has_archive'         => false,
            'rewrite'             => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
        ] );
    }

    /* ------------------------------------------------------------------ */
    /* Form handler                                                         */
    /* ------------------------------------------------------------------ */

    public function handle_submit() {
        $referer = wp_get_referer() ?: home_url( '/contact' );

        // Honeypot anti-spam : si rempli, on simule un succès silencieux
        if ( ! empty( $_POST['nira_website'] ) ) {
            wp_safe_redirect( add_query_arg( 'nira_msg', 'sent', $referer ) . '#contact-form' );
            exit;
        }

        if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::ACTION ) ) {
            wp_safe_redirect( add_query_arg( 'nira_msg', 'nonce', $referer ) . '#contact-form' );
            exit;
        }

        $name    = isset( $_POST['nira_name'] )    ? sanitize_text_field( wp_unslash( $_POST['nira_name'] ) )    : '';
        $phone   = isset( $_POST['nira_phone'] )   ? sanitize_text_field( wp_unslash( $_POST['nira_phone'] ) )   : '';
        $email   = isset( $_POST['nira_email'] )   ? sanitize_email(       wp_unslash( $_POST['nira_email'] ) )  : '';
        $subject = isset( $_POST['nira_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['nira_subject'] ) ) : '';
        $message = isset( $_POST['nira_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['nira_message'] ) ) : '';

        if ( empty( $name ) || empty( $email ) || empty( $message ) || ! is_email( $email ) ) {
            wp_safe_redirect( add_query_arg( 'nira_msg', 'invalid', $referer ) . '#contact-form' );
            exit;
        }

        // Création du post
        $post_id = wp_insert_post( [
            'post_type'   => self::CPT,
            'post_status' => 'publish',
            'post_title'  => sprintf( '%s — %s', $name, $subject ?: __( 'Sans sujet', 'nira-booking' ) ),
            'post_content' => $message,
            'meta_input'  => [
                '_nira_name'    => $name,
                '_nira_phone'   => $phone,
                '_nira_email'   => $email,
                '_nira_subject' => $subject,
                '_nira_ip'      => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '',
                '_nira_ua'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '',
            ],
        ], true );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            wp_safe_redirect( add_query_arg( 'nira_msg', 'error', $referer ) . '#contact-form' );
            exit;
        }

        // Données partagées pour les deux emails
        $payload = [
            'name'      => $name,
            'email'     => $email,
            'phone'     => $phone,
            'subject'   => $subject,
            'message'   => $message,
            'admin_url' => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
        ];

        // 1) Notification HTML brandée à Margaux (avec Reply-To = visiteur)
        if ( class_exists( 'Nira_Email' ) ) {
            Nira_Email::send_contact_notification( self::get_destination_email(), $payload );
        }

        // 2) Accusé de réception HTML brandé au visiteur
        if ( class_exists( 'Nira_Email' ) ) {
            Nira_Email::send_contact_autoreply( $email, $payload );
        }

        wp_safe_redirect( add_query_arg( 'nira_msg', 'sent', $referer ) . '#contact-form' );
        exit;
    }

    public static function get_destination_email() {
        // Priorité : option dédiée Nira_Contact > notification_email Nira > admin_email WP
        $custom = get_option( self::OPT_TO );
        if ( ! empty( $custom ) && is_email( $custom ) ) return $custom;
        if ( class_exists( 'Nira_Settings' ) ) {
            $notif = Nira_Settings::get( 'notification_email', '' );
            if ( ! empty( $notif ) && is_email( $notif ) ) return $notif;
        }
        $admin = get_option( 'admin_email' );
        return is_email( $admin ) ? $admin : 'contact@ecuriedenira.fr';
    }

    /* ------------------------------------------------------------------ */
    /* Admin — colonnes                                                     */
    /* ------------------------------------------------------------------ */

    public function columns( $cols ) {
        $new = [];
        $new['cb']        = $cols['cb'] ?? '';
        $new['title']     = __( 'Demande', 'nira-booking' );
        $new['nira_from'] = __( 'Expéditeur', 'nira-booking' );
        $new['nira_subj'] = __( 'Sujet', 'nira-booking' );
        $new['date']      = $cols['date'] ?? __( 'Date' );
        return $new;
    }

    public function column_content( $col, $post_id ) {
        switch ( $col ) {
            case 'nira_from':
                $name  = get_post_meta( $post_id, '_nira_name',  true );
                $email = get_post_meta( $post_id, '_nira_email', true );
                $phone = get_post_meta( $post_id, '_nira_phone', true );
                echo '<strong>' . esc_html( $name ) . '</strong>';
                if ( $email ) echo '<br><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
                if ( $phone ) echo '<br><span style="color:#888">' . esc_html( $phone ) . '</span>';
                break;
            case 'nira_subj':
                echo esc_html( get_post_meta( $post_id, '_nira_subject', true ) ?: '—' );
                break;
        }
    }

    public function row_actions( $actions, $post ) {
        if ( $post->post_type !== self::CPT ) return $actions;
        $email = get_post_meta( $post->ID, '_nira_email', true );
        if ( $email ) {
            $actions['nira_reply'] = '<a href="mailto:' . esc_attr( $email ) . '">' . __( 'Répondre par mail', 'nira-booking' ) . '</a>';
        }
        unset( $actions['inline hide-if-no-js'] );
        return $actions;
    }

    /* ------------------------------------------------------------------ */
    /* Admin — metabox lecture                                              */
    /* ------------------------------------------------------------------ */

    public function metabox() {
        add_meta_box(
            'nira_message_body',
            __( 'Message reçu', 'nira-booking' ),
            [ $this, 'render_message_body' ],
            self::CPT,
            'normal',
            'high'
        );
        add_meta_box(
            'nira_message_details',
            __( 'Détails de la demande', 'nira-booking' ),
            [ $this, 'render_metabox' ],
            self::CPT,
            'side',
            'high'
        );
    }

    public function render_message_body( $post ) {
        $content = $post->post_content;
        $name    = get_post_meta( $post->ID, '_nira_name',    true );
        $email   = get_post_meta( $post->ID, '_nira_email',   true );
        $phone   = get_post_meta( $post->ID, '_nira_phone',   true );
        $subject = get_post_meta( $post->ID, '_nira_subject', true );
        $sent_at = get_post_time( 'U', false, $post );
        $relative = $sent_at ? human_time_diff( $sent_at, current_time( 'timestamp' ) ) : '';
        $absolute = $sent_at ? wp_date( 'd/m/Y à H\hi', $sent_at ) : '';
        ?>
        <style>
            #nira_message_body .inside { margin: 0 !important; padding: 0 !important; }
            #nira_message_body .postbox-header { background: #FDFBF9; border-bottom: 1px solid #f0eae6; }
            #nira_message_body .postbox-header h2 { color: #2D2D2D; font-weight: 600; letter-spacing: 0.3px; }
            .nira-letter {
                background: linear-gradient(180deg, #FFFFFF 0%, #FDFBF9 100%);
                padding: 44px 48px 36px;
                position: relative;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            .nira-letter::before {
                content: "“";
                position: absolute;
                top: -10px;
                left: 28px;
                font-family: "Playfair Display", Georgia, serif;
                font-size: 130px;
                color: #A41C2B;
                opacity: 0.10;
                line-height: 1;
                pointer-events: none;
                font-weight: 700;
            }
            .nira-letter-from {
                display: flex;
                align-items: center;
                gap: 14px;
                margin-bottom: 28px;
                padding-bottom: 22px;
                border-bottom: 1px solid rgba(164, 28, 43, 0.12);
                position: relative;
                z-index: 1;
            }
            .nira-letter-avatar {
                width: 46px; height: 46px;
                border-radius: 50%;
                background: #A41C2B;
                color: #fff;
                display: flex; align-items: center; justify-content: center;
                font-weight: 700; font-size: 17px;
                flex-shrink: 0;
                letter-spacing: 0.5px;
            }
            .nira-letter-from-info { line-height: 1.4; }
            .nira-letter-from-info .name {
                font-weight: 600; color: #2D2D2D; font-size: 15px;
            }
            .nira-letter-from-info .meta {
                font-size: 12.5px; color: #888; margin-top: 3px;
            }
            .nira-letter-from-info .meta a { color: #888; text-decoration: none; }
            .nira-letter-from-info .meta a:hover { color: #A41C2B; }
            .nira-letter-from-info .meta span.sep { margin: 0 8px; opacity: 0.4; }
            .nira-letter-subject {
                display: inline-block;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 2px;
                color: #A41C2B;
                background: rgba(164, 28, 43, 0.06);
                padding: 6px 14px;
                border-radius: 100px;
                margin-bottom: 22px;
                position: relative; z-index: 1;
            }
            .nira-letter-body {
                font-size: 15.5px;
                line-height: 1.85;
                color: #2D2D2D;
                white-space: pre-wrap;
                word-wrap: break-word;
                position: relative; z-index: 1;
                font-family: Georgia, "Times New Roman", serif;
            }
            .nira-letter-footer {
                margin-top: 32px;
                padding-top: 22px;
                border-top: 1px dashed rgba(0,0,0,0.08);
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 18px;
                flex-wrap: wrap;
                position: relative; z-index: 1;
            }
            .nira-letter-actions { display: flex; gap: 10px; flex-wrap: wrap; }
            .nira-letter-actions .button {
                padding: 8px 18px !important;
                height: auto !important;
                font-size: 13px !important;
                font-weight: 500 !important;
            }
            .nira-letter-actions .button-primary {
                background: #A41C2B !important;
                border-color: #A41C2B !important;
                color: #fff !important;
                box-shadow: 0 2px 8px rgba(164, 28, 43, 0.25) !important;
            }
            .nira-letter-actions .button-primary:hover {
                background: #8a1623 !important;
                border-color: #8a1623 !important;
            }
            .nira-letter-time {
                font-size: 12px;
                color: #888;
                font-style: italic;
            }
            .nira-letter-time strong { color: #555; font-style: normal; }
        </style>

        <div class="nira-letter">
            <div class="nira-letter-from">
                <div class="nira-letter-avatar"><?php echo esc_html( strtoupper( mb_substr( $name, 0, 1 ) ) ); ?></div>
                <div class="nira-letter-from-info">
                    <div class="name"><?php echo esc_html( $name ); ?></div>
                    <div class="meta">
                        <?php if ( $email ) : ?>
                            <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                        <?php endif; ?>
                        <?php if ( $phone ) : ?>
                            <span class="sep">•</span>
                            <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ( $subject ) : ?>
                <div class="nira-letter-subject"><?php echo esc_html( $subject ); ?></div>
            <?php endif; ?>

            <div class="nira-letter-body"><?php echo esc_html( $content ); ?></div>

            <div class="nira-letter-footer">
                <div class="nira-letter-time">
                    <?php if ( $relative ) : ?>
                        Reçu il y a <strong><?php echo esc_html( $relative ); ?></strong> · <?php echo esc_html( $absolute ); ?>
                    <?php endif; ?>
                </div>
                <div class="nira-letter-actions">
                    <?php if ( $email ) : ?>
                        <a href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo rawurlencode( 'Re: ' . $subject ); ?>" class="button button-primary">
                            <?php esc_html_e( 'Répondre par mail', 'nira-booking' ); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( $phone ) : ?>
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>" class="button">
                            <?php esc_html_e( 'Appeler', 'nira-booking' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_metabox( $post ) {
        $name    = get_post_meta( $post->ID, '_nira_name',    true );
        $email   = get_post_meta( $post->ID, '_nira_email',   true );
        $phone   = get_post_meta( $post->ID, '_nira_phone',   true );
        $subject = get_post_meta( $post->ID, '_nira_subject', true );
        $ip      = get_post_meta( $post->ID, '_nira_ip',      true );
        ?>
        <p><strong><?php esc_html_e( 'Nom', 'nira-booking' ); ?></strong><br><?php echo esc_html( $name ); ?></p>
        <p><strong><?php esc_html_e( 'Email', 'nira-booking' ); ?></strong><br>
            <?php if ( $email ) : ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a><?php endif; ?>
        </p>
        <?php if ( $phone ) : ?>
            <p><strong><?php esc_html_e( 'Téléphone', 'nira-booking' ); ?></strong><br>
                <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
            </p>
        <?php endif; ?>
        <p><strong><?php esc_html_e( 'Sujet', 'nira-booking' ); ?></strong><br><?php echo esc_html( $subject ?: '—' ); ?></p>
        <?php if ( $ip ) : ?>
            <p style="color:#888"><small>IP : <?php echo esc_html( $ip ); ?></small></p>
        <?php endif; ?>
        <p>
            <a href="mailto:<?php echo esc_attr( $email ); ?>?subject=Re:%20<?php echo rawurlencode( $subject ); ?>" class="button button-primary">
                <?php esc_html_e( 'Répondre', 'nira-booking' ); ?>
            </a>
        </p>
        <?php
    }

    /* ------------------------------------------------------------------ */
    /* Settings                                                             */
    /* ------------------------------------------------------------------ */

    public function register_settings() {
        register_setting( 'nira_contact_settings', self::OPT_TO, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default'           => '',
        ] );
    }
}

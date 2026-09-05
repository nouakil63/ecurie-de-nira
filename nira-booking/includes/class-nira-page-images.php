<?php
/**
 * Meta box générique « Images Nira » disponible sur toutes les pages WP.
 *
 * Permet de choisir une image principale (hero) et une galerie d'images
 * depuis la médiathèque WordPress, sans toucher au thème.
 *
 * Utilisation côté thème / contenu :
 *  - shortcode  : [nira_hero]              ou [nira_hero size="large"]
 *  - shortcode  : [nira_gallery]           ou [nira_gallery cols="3"]
 *  - shortcode  : [nira_image slot="1"]    (récupère l'image n° 1 de la galerie)
 *  - PHP        : nira_page_hero( $post_id )
 *  - PHP        : nira_page_gallery( $post_id )
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Page_Images {

    const META_HERO    = '_nira_hero_image';     // URL
    const META_HERO_ID = '_nira_hero_image_id';  // attachment ID (optionnel)
    const META_GALLERY = '_nira_gallery';        // array of URLs
    const NONCE        = 'nira_page_images_nonce';

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'add_meta_boxes',      [ $this, 'register_meta_box' ] );
        add_action( 'save_post',           [ $this, 'save_meta_box' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Shortcodes utilisables dans l'éditeur de n'importe quelle page.
        add_shortcode( 'nira_hero',    [ __CLASS__, 'shortcode_hero' ] );
        add_shortcode( 'nira_gallery', [ __CLASS__, 'shortcode_gallery' ] );
        add_shortcode( 'nira_image',   [ __CLASS__, 'shortcode_image' ] );
    }

    public function enqueue_assets( $hook ) {
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            return;
        }
        wp_enqueue_media();
    }

    public function register_meta_box() {
        // On limite aux pages (et CPT publics hiérarchiques), pas aux réservations internes.
        $post_types = [ 'page' ];
        /**
         * Permet d'ajouter d'autres types de contenu (ex : posts, CPT perso).
         */
        $post_types = apply_filters( 'nira_page_images_post_types', $post_types );

        foreach ( $post_types as $pt ) {
            add_meta_box(
                'nira-page-images',
                __( 'Images Nira (médiathèque)', 'nira-booking' ),
                [ $this, 'render_meta_box' ],
                $pt,
                'normal',
                'high'
            );
        }
    }

    public function render_meta_box( $post ) {
        wp_nonce_field( self::NONCE, self::NONCE );

        $hero    = (string) get_post_meta( $post->ID, self::META_HERO, true );
        $gallery = get_post_meta( $post->ID, self::META_GALLERY, true );
        if ( ! is_array( $gallery ) ) {
            $gallery = [];
        }
        ?>
        <style>
            .nira-pi-field { margin-bottom: 18px; }
            .nira-pi-field > label { display:block; font-weight:600; margin-bottom:6px; }
            .nira-pi-preview {
                width: 160px; height: 100px; object-fit: cover;
                border: 1px solid #e1e1e1; border-radius: 6px; background: #f3f3f3;
                display: block;
            }
            .nira-pi-row { display:flex; gap:12px; align-items:flex-start; }
            .nira-pi-row > .nira-pi-controls { flex:1; }
            .nira-pi-url { width:100%; font-family: ui-monospace, Menlo, Consolas, monospace; font-size:12px; }
            .nira-pi-gallery { display:grid; grid-template-columns: repeat(auto-fill,minmax(140px,1fr)); gap:10px; margin-top:8px; }
            .nira-pi-gal-item { position:relative; border:1px solid #e1e1e1; border-radius:6px; overflow:hidden; background:#f9f9f9; }
            .nira-pi-gal-item img { display:block; width:100%; height:100px; object-fit:cover; }
            .nira-pi-gal-item .nira-pi-remove {
                position:absolute; top:4px; right:4px;
                background: rgba(0,0,0,.65); color:#fff; border:0;
                width:24px; height:24px; border-radius:50%; cursor:pointer; line-height:1;
            }
            .nira-pi-help { color:#646970; font-size:12px; margin-top:4px; }
            .nira-pi-actions { margin-top:8px; }
            .nira-pi-actions .button { margin-right:6px; }
        </style>

        <div class="nira-pi-field">
            <label><?php esc_html_e( 'Image principale (hero)', 'nira-booking' ); ?></label>
            <div class="nira-pi-row">
                <img id="nira-pi-hero-preview"
                     class="nira-pi-preview"
                     src="<?php echo esc_url( $hero ); ?>"
                     alt=""
                     style="<?php echo $hero ? '' : 'visibility:hidden'; ?>">
                <div class="nira-pi-controls">
                    <input type="url"
                           id="nira-pi-hero-url"
                           name="nira_hero_image"
                           value="<?php echo esc_attr( $hero ); ?>"
                           placeholder="https://…"
                           class="nira-pi-url">
                    <div class="nira-pi-actions">
                        <button type="button" class="button button-primary" id="nira-pi-hero-pick">
                            <?php esc_html_e( 'Médiathèque', 'nira-booking' ); ?>
                        </button>
                        <button type="button" class="button-link" id="nira-pi-hero-clear" style="color:#a00">
                            <?php esc_html_e( 'Vider', 'nira-booking' ); ?>
                        </button>
                    </div>
                    <p class="nira-pi-help">
                        <?php esc_html_e( 'Utilisation dans le contenu : ', 'nira-booking' ); ?>
                        <code>[nira_hero]</code>
                    </p>
                </div>
            </div>
        </div>

        <div class="nira-pi-field">
            <label><?php esc_html_e( 'Galerie d\'images', 'nira-booking' ); ?></label>

            <div class="nira-pi-actions">
                <button type="button" class="button" id="nira-pi-gal-add">
                    <?php esc_html_e( 'Ajouter depuis la médiathèque', 'nira-booking' ); ?>
                </button>
                <button type="button" class="button-link" id="nira-pi-gal-clear" style="color:#a00">
                    <?php esc_html_e( 'Tout vider', 'nira-booking' ); ?>
                </button>
            </div>

            <div class="nira-pi-gallery" id="nira-pi-gal-list">
                <?php foreach ( $gallery as $url ) : ?>
                    <div class="nira-pi-gal-item">
                        <img src="<?php echo esc_url( $url ); ?>" alt="">
                        <input type="hidden" name="nira_gallery[]" value="<?php echo esc_attr( $url ); ?>">
                        <button type="button" class="nira-pi-remove" title="<?php esc_attr_e( 'Retirer', 'nira-booking' ); ?>">&times;</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="nira-pi-help">
                <?php esc_html_e( 'Utilisation : ', 'nira-booking' ); ?>
                <code>[nira_gallery]</code>
                <?php esc_html_e( ' ou une image précise ', 'nira-booking' ); ?>
                <code>[nira_image slot="1"]</code>
                <?php esc_html_e( '(1 = première image de la galerie)', 'nira-booking' ); ?>
            </p>
        </div>

        <script>
        jQuery(function($){
            var heroFrame, galFrame;

            // ------------- Hero -------------
            $('#nira-pi-hero-pick').on('click', function(e){
                e.preventDefault();
                heroFrame = wp.media({
                    title:    <?php echo wp_json_encode( __( 'Choisir l\'image principale', 'nira-booking' ) ); ?>,
                    button:   { text: <?php echo wp_json_encode( __( 'Utiliser cette image', 'nira-booking' ) ); ?> },
                    library:  { type: 'image' },
                    multiple: false
                });
                heroFrame.on('select', function(){
                    var att = heroFrame.state().get('selection').first().toJSON();
                    var url = (att.sizes && att.sizes.large) ? att.sizes.large.url : att.url;
                    $('#nira-pi-hero-url').val(url);
                    $('#nira-pi-hero-preview').attr('src', url).css('visibility','visible');
                });
                heroFrame.open();
            });

            $('#nira-pi-hero-clear').on('click', function(e){
                e.preventDefault();
                $('#nira-pi-hero-url').val('');
                $('#nira-pi-hero-preview').attr('src','').css('visibility','hidden');
            });

            // ------------- Galerie -------------
            function addGalItem(url){
                var $item = $('<div class="nira-pi-gal-item"></div>');
                $item.append($('<img>').attr('src', url));
                $item.append($('<input type="hidden" name="nira_gallery[]">').val(url));
                $item.append($('<button type="button" class="nira-pi-remove">&times;</button>'));
                $('#nira-pi-gal-list').append($item);
            }

            $('#nira-pi-gal-add').on('click', function(e){
                e.preventDefault();
                galFrame = wp.media({
                    title:    <?php echo wp_json_encode( __( 'Ajouter à la galerie', 'nira-booking' ) ); ?>,
                    button:   { text: <?php echo wp_json_encode( __( 'Ajouter à la galerie', 'nira-booking' ) ); ?> },
                    library:  { type: 'image' },
                    multiple: true
                });
                galFrame.on('select', function(){
                    galFrame.state().get('selection').each(function(att){
                        att = att.toJSON();
                        var url = (att.sizes && att.sizes.large) ? att.sizes.large.url : att.url;
                        addGalItem(url);
                    });
                });
                galFrame.open();
            });

            $(document).on('click', '.nira-pi-remove', function(e){
                e.preventDefault();
                $(this).closest('.nira-pi-gal-item').remove();
            });

            $('#nira-pi-gal-clear').on('click', function(e){
                e.preventDefault();
                if ( confirm(<?php echo wp_json_encode( __( 'Vider toute la galerie ?', 'nira-booking' ) ); ?>) ) {
                    $('#nira-pi-gal-list').empty();
                }
            });
        });
        </script>
        <?php
    }

    public function save_meta_box( $post_id, $post ) {
        if ( ! isset( $_POST[ self::NONCE ] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE ] ) ), self::NONCE ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Hero
        $hero = isset( $_POST['nira_hero_image'] )
            ? esc_url_raw( wp_unslash( $_POST['nira_hero_image'] ) )
            : '';
        if ( '' === $hero ) {
            delete_post_meta( $post_id, self::META_HERO );
        } else {
            update_post_meta( $post_id, self::META_HERO, $hero );
        }

        // Galerie
        $gallery_raw = isset( $_POST['nira_gallery'] ) && is_array( $_POST['nira_gallery'] )
            ? (array) wp_unslash( $_POST['nira_gallery'] )
            : [];
        $gallery = [];
        foreach ( $gallery_raw as $url ) {
            $u = esc_url_raw( $url );
            if ( '' !== $u ) $gallery[] = $u;
        }
        if ( empty( $gallery ) ) {
            delete_post_meta( $post_id, self::META_GALLERY );
        } else {
            update_post_meta( $post_id, self::META_GALLERY, $gallery );
        }
    }

    /* -------------------- Shortcodes -------------------- */

    public static function shortcode_hero( $atts = [] ) {
        $atts = shortcode_atts( [
            'id'    => 0,
            'class' => 'nira-hero-img',
            'alt'   => '',
        ], $atts, 'nira_hero' );

        $post_id = (int) $atts['id'] ?: (int) get_the_ID();
        $url     = nira_page_hero( $post_id );
        if ( ! $url ) return '';

        $alt = '' !== $atts['alt'] ? $atts['alt'] : get_the_title( $post_id );

        return sprintf(
            '<img src="%s" alt="%s" class="%s" loading="lazy">',
            esc_url( $url ),
            esc_attr( $alt ),
            esc_attr( $atts['class'] )
        );
    }

    public static function shortcode_gallery( $atts = [] ) {
        $atts = shortcode_atts( [
            'id'    => 0,
            'cols'  => 3,
            'class' => 'nira-gallery',
        ], $atts, 'nira_gallery' );

        $post_id = (int) $atts['id'] ?: (int) get_the_ID();
        $items   = nira_page_gallery( $post_id );
        if ( empty( $items ) ) return '';

        $cols = max( 1, min( 6, (int) $atts['cols'] ) );
        $html  = sprintf(
            '<div class="%s" style="display:grid;grid-template-columns:repeat(%d,1fr);gap:12px">',
            esc_attr( $atts['class'] ),
            $cols
        );
        foreach ( $items as $url ) {
            $html .= sprintf(
                '<img src="%s" alt="" loading="lazy" style="width:100%%;height:100%%;object-fit:cover;border-radius:10px">',
                esc_url( $url )
            );
        }
        $html .= '</div>';
        return $html;
    }

    public static function shortcode_image( $atts = [] ) {
        $atts = shortcode_atts( [
            'id'    => 0,
            'slot'  => 1,
            'class' => 'nira-slot-img',
            'alt'   => '',
        ], $atts, 'nira_image' );

        $post_id = (int) $atts['id'] ?: (int) get_the_ID();
        $items   = nira_page_gallery( $post_id );
        $idx     = max( 1, (int) $atts['slot'] ) - 1;
        if ( ! isset( $items[ $idx ] ) ) return '';

        return sprintf(
            '<img src="%s" alt="%s" class="%s" loading="lazy">',
            esc_url( $items[ $idx ] ),
            esc_attr( $atts['alt'] ),
            esc_attr( $atts['class'] )
        );
    }
}

/* -------------------- Helpers publics -------------------- */

if ( ! function_exists( 'nira_page_hero' ) ) {
    /**
     * Retourne l'URL de l'image hero configurée pour une page.
     */
    function nira_page_hero( $post_id = null ) {
        $post_id = $post_id ?: get_the_ID();
        if ( ! $post_id ) return '';
        return (string) get_post_meta( $post_id, Nira_Page_Images::META_HERO, true );
    }
}

if ( ! function_exists( 'nira_page_gallery' ) ) {
    /**
     * Retourne la liste d'URLs de la galerie configurée pour une page.
     */
    function nira_page_gallery( $post_id = null ) {
        $post_id = $post_id ?: get_the_ID();
        if ( ! $post_id ) return [];
        $v = get_post_meta( $post_id, Nira_Page_Images::META_GALLERY, true );
        return is_array( $v ) ? $v : [];
    }
}

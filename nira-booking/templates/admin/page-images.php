<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Admin : Images des pages Nira
 * Variables disponibles : $groups, $current, $fields, $values
 */
$admin_url = admin_url( 'admin.php?page=nira-page-images' );
?>
<div class="wrap nira-wrap">
    <h1><?php esc_html_e( 'Images des pages', 'nira-booking' ); ?></h1>
    <p class="description" style="max-width:780px;margin-bottom:16px">
        <?php esc_html_e( 'Choisis les images de chaque template de page depuis la Médiathèque WordPress. Sélectionne une page dans l\'onglet correspondant, configure ses images, puis enregistre.', 'nira-booking' ); ?>
    </p>

    <!-- Onglets de navigation -->
    <nav class="nav-tab-wrapper" style="margin-bottom:0">
        <?php foreach ( $groups as $key => $group ) :
            $url = add_query_arg( 'tpl', $key, $admin_url );
            $active = $key === $current ? ' nav-tab-active' : '';
        ?>
            <a href="<?php echo esc_url( $url ); ?>" class="nav-tab<?php echo $active; ?>">
                <?php echo esc_html( $group['label'] ); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="nira-card" style="padding:20px 24px;margin-top:0;border-top-left-radius:0;max-width:1000px">
        <h2 style="margin-bottom:16px"><?php echo esc_html( $groups[ $current ]['label'] ); ?></h2>

        <form method="post">
            <?php wp_nonce_field( 'nira_admin_save_page_images' ); ?>
            <input type="hidden" name="nira_action" value="save_page_images">
            <input type="hidden" name="tpl" value="<?php echo esc_attr( $current ); ?>">

            <table class="widefat striped">
                <thead>
                    <tr>
                        <th style="width:80px"></th>
                        <th><?php esc_html_e( 'Image', 'nira-booking' ); ?></th>
                        <th style="width:42%"><?php esc_html_e( 'URL', 'nira-booking' ); ?></th>
                        <th style="width:200px"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $fields as $key => $cfg ) :
                    $val = $values[ $key ] ?? '';
                ?>
                    <tr>
                        <td>
                            <img id="preview-<?php echo esc_attr( $key ); ?>"
                                 src="<?php echo esc_url( $val ); ?>"
                                 alt=""
                                 style="width:70px;height:52px;object-fit:cover;border-radius:6px;background:#f3f3f3;border:1px solid #e5e5e5;<?php echo $val ? '' : 'visibility:hidden'; ?>">
                        </td>
                        <td>
                            <strong><?php echo esc_html( $cfg['label'] ); ?></strong><br>
                            <span class="description"><code><?php echo esc_html( $current . '_' . $key ); ?></code></span>
                            <?php if ( ! empty( $cfg['default'] ) ) : ?>
                                <br><span class="description" style="font-size:11px"><?php esc_html_e( 'Défaut :', 'nira-booking' ); ?> <em><?php echo esc_html( $cfg['default'] ); ?></em></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <input type="url"
                                   class="regular-text nira-pi-url"
                                   id="field-<?php echo esc_attr( $key ); ?>"
                                   name="<?php echo esc_attr( $key ); ?>"
                                   value="<?php echo esc_attr( $val ); ?>"
                                   placeholder="https://…"
                                   style="width:100%">
                        </td>
                        <td>
                            <button type="button"
                                    class="button nira-pi-pick"
                                    data-target="field-<?php echo esc_attr( $key ); ?>"
                                    data-preview="preview-<?php echo esc_attr( $key ); ?>">
                                <?php esc_html_e( 'Médiathèque', 'nira-booking' ); ?>
                            </button>
                            <button type="button"
                                    class="button-link nira-pi-clear"
                                    data-target="field-<?php echo esc_attr( $key ); ?>"
                                    data-preview="preview-<?php echo esc_attr( $key ); ?>"
                                    style="color:#a00;margin-left:6px">
                                <?php esc_html_e( 'Vider', 'nira-booking' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <p style="margin-top:18px">
                <button type="submit" class="button button-primary button-large">
                    <?php esc_html_e( 'Enregistrer les images', 'nira-booking' ); ?>
                </button>
            </p>
        </form>
    </div>
</div>

<script>
jQuery(function($){
    $(document).on('click', '.nira-pi-pick', function(e){
        e.preventDefault();
        var $btn    = $(this);
        var target  = '#' + $btn.data('target');
        var preview = '#' + $btn.data('preview');
        var frame = wp.media({
            title:    <?php echo wp_json_encode( __( 'Choisir une image', 'nira-booking' ) ); ?>,
            button:   { text: <?php echo wp_json_encode( __( 'Utiliser cette image', 'nira-booking' ) ); ?> },
            library:  { type: 'image' },
            multiple: false
        });
        frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            var url = (att.sizes && att.sizes.large) ? att.sizes.large.url : att.url;
            $(target).val(url);
            $(preview).attr('src', url).css('visibility', 'visible');
        });
        frame.open();
    });

    $(document).on('click', '.nira-pi-clear', function(e){
        e.preventDefault();
        var target  = '#' + $(this).data('target');
        var preview = '#' + $(this).data('preview');
        $(target).val('');
        $(preview).attr('src', '').css('visibility', 'hidden');
    });
});
</script>

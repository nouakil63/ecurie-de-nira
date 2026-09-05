<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1><?php esc_html_e( 'Page Gîtes — Images', 'nira-booking' ); ?></h1>

    <p class="description" style="max-width:780px">
        <?php esc_html_e( 'Choisis chaque image depuis la Médiathèque WordPress. Ces images sont utilisées par le template de page « Gîtes & Séjours (Nira) ». Si un champ est vide, l\'image par défaut livrée avec le plugin est utilisée.', 'nira-booking' ); ?>
    </p>

    <form method="post" class="nira-card" style="max-width:960px;padding:18px 22px;margin-top:16px">
        <?php wp_nonce_field( 'nira_admin_save_gites_images' ); ?>
        <input type="hidden" name="nira_action" value="save_gites_images">

        <table class="widefat striped nira-gites-images">
            <thead>
                <tr>
                    <th style="width:80px"></th>
                    <th><?php esc_html_e( 'Image', 'nira-booking' ); ?></th>
                    <th style="width:42%"><?php esc_html_e( 'URL', 'nira-booking' ); ?></th>
                    <th style="width:180px"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $images as $key => $cfg ) :
                $val = $values[ $key ] ?? '';
            ?>
                <tr>
                    <td>
                        <img class="nira-gites-preview"
                             id="preview-<?php echo esc_attr( $key ); ?>"
                             src="<?php echo esc_url( $val ); ?>"
                             alt=""
                             style="width:70px;height:52px;object-fit:cover;border-radius:6px;background:#f3f3f3;border:1px solid #e5e5e5;<?php echo $val ? '' : 'visibility:hidden'; ?>">
                    </td>
                    <td>
                        <strong><?php echo esc_html( $cfg['label'] ); ?></strong><br>
                        <span class="description"><code><?php echo esc_html( $key ); ?></code></span>
                    </td>
                    <td>
                        <input type="url"
                               class="regular-text nira-gites-url"
                               id="field-<?php echo esc_attr( $key ); ?>"
                               name="<?php echo esc_attr( $key ); ?>"
                               value="<?php echo esc_attr( $val ); ?>"
                               placeholder="https://…"
                               style="width:100%">
                    </td>
                    <td>
                        <button type="button"
                                class="button nira-gites-pick"
                                data-target="field-<?php echo esc_attr( $key ); ?>"
                                data-preview="preview-<?php echo esc_attr( $key ); ?>">
                            <?php esc_html_e( 'Médiathèque', 'nira-booking' ); ?>
                        </button>
                        <button type="button"
                                class="button-link nira-gites-clear"
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

<script>
jQuery(function($){
    var frame;

    $(document).on('click', '.nira-gites-pick', function(e){
        e.preventDefault();
        var $btn    = $(this);
        var target  = '#' + $btn.data('target');
        var preview = '#' + $btn.data('preview');

        frame = wp.media({
            title:    <?php echo wp_json_encode( __( 'Choisir une image', 'nira-booking' ) ); ?>,
            button:   { text: <?php echo wp_json_encode( __( 'Utiliser cette image', 'nira-booking' ) ); ?> },
            library:  { type: 'image' },
            multiple: false
        });

        frame.on('select', function(){
            var att = frame.state().get('selection').first().toJSON();
            var url = att.sizes && att.sizes.large ? att.sizes.large.url : att.url;
            $(target).val(url);
            $(preview).attr('src', url).css('visibility', 'visible');
        });

        frame.open();
    });

    $(document).on('click', '.nira-gites-clear', function(e){
        e.preventDefault();
        var target  = '#' + $(this).data('target');
        var preview = '#' + $(this).data('preview');
        $(target).val('');
        $(preview).attr('src', '').css('visibility', 'hidden');
    });
});
</script>

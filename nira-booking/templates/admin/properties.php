<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Hébergements', 'nira-booking' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=nira-properties&action=new' ) ); ?>" class="page-title-action">+ <?php esc_html_e( 'Ajouter', 'nira-booking' ); ?></a>
    <hr class="wp-header-end">

    <table class="widefat striped">
        <thead>
            <tr><th>Nom</th><th>Slug</th><th>Prix/nuit</th><th>Ménage</th><th>Capacité</th><th>Min nuits</th><th>Acompte</th><th>Statut</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ( $properties as $p ) : ?>
                <tr>
                    <td><strong><?php echo esc_html( $p->name ); ?></strong></td>
                    <td><code><?php echo esc_html( $p->slug ); ?></code></td>
                    <td><?php echo esc_html( Nira_Admin::money( $p->base_price ) ); ?></td>
                    <td><?php echo esc_html( Nira_Admin::money( $p->cleaning_fee ) ); ?></td>
                    <td><?php echo (int) $p->capacity; ?></td>
                    <td><?php echo (int) $p->min_nights; ?></td>
                    <td><?php echo (int) $p->deposit_pct; ?>%</td>
                    <td><span class="nira-pill <?php echo 'active' === $p->status ? 'confirmed' : 'cancelled'; ?>"><?php echo esc_html( $p->status ); ?></span></td>
                    <td>
                        <a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=nira-properties&action=edit&id=' . (int) $p->id ) ); ?>"><?php esc_html_e( 'Modifier', 'nira-booking' ); ?></a>
                        <a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=nira-pricing&property_id=' . (int) $p->id ) ); ?>"><?php esc_html_e( 'Tarifs', 'nira-booking' ); ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="nira-card" style="margin-top:30px">
        <h3><?php esc_html_e( 'Shortcodes prêts à copier', 'nira-booking' ); ?></h3>
        <?php foreach ( $properties as $p ) : ?>
            <p><strong><?php echo esc_html( $p->name ); ?></strong> → <code>[nira_booking slug="<?php echo esc_attr( $p->slug ); ?>"]</code></p>
        <?php endforeach; ?>
    </div>
</div>

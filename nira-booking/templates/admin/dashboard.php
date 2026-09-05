<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1><?php esc_html_e( 'Tableau de bord', 'nira-booking' ); ?></h1>

    <div class="nira-stats">
        <div class="nira-stat"><span class="nira-stat__label"><?php esc_html_e( 'Réservations à venir', 'nira-booking' ); ?></span><span class="nira-stat__value"><?php echo (int) $stats['upcoming']; ?></span></div>
        <div class="nira-stat"><span class="nira-stat__label"><?php esc_html_e( "Chiffre d'affaires (YTD)", 'nira-booking' ); ?></span><span class="nira-stat__value"><?php echo esc_html( Nira_Admin::money( $stats['revenue_ytd'] ) ); ?></span></div>
        <div class="nira-stat"><span class="nira-stat__label"><?php esc_html_e( 'Nuits vendues (YTD)', 'nira-booking' ); ?></span><span class="nira-stat__value"><?php echo (int) $stats['nights_ytd']; ?></span></div>
        <div class="nira-stat"><span class="nira-stat__label"><?php esc_html_e( 'En attente de paiement', 'nira-booking' ); ?></span><span class="nira-stat__value"><?php echo (int) $stats['pending']; ?></span></div>
        <div class="nira-stat"><span class="nira-stat__label"><?php esc_html_e( 'Imports Airbnb', 'nira-booking' ); ?></span><span class="nira-stat__value"><?php echo (int) $stats['airbnb_imported']; ?></span></div>
        <div class="nira-stat"><span class="nira-stat__label"><?php esc_html_e( 'Total confirmé', 'nira-booking' ); ?></span><span class="nira-stat__value"><?php echo (int) $stats['total_bookings']; ?></span></div>
    </div>

    <div class="nira-two-col">
        <div class="nira-card">
            <h2><?php esc_html_e( 'Hébergements', 'nira-booking' ); ?></h2>
            <table class="widefat striped">
                <thead><tr><th><?php esc_html_e( 'Nom', 'nira-booking' ); ?></th><th><?php esc_html_e( 'Prix/nuit', 'nira-booking' ); ?></th><th><?php esc_html_e( 'Capacité', 'nira-booking' ); ?></th><th><?php esc_html_e( 'Statut', 'nira-booking' ); ?></th><th></th></tr></thead>
                <tbody>
                <?php foreach ( $properties as $p ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $p->name ); ?></strong><br><code><?php echo esc_html( $p->slug ); ?></code></td>
                        <td><?php echo esc_html( Nira_Admin::money( $p->base_price ) ); ?></td>
                        <td><?php echo (int) $p->capacity; ?> pers.</td>
                        <td><span class="nira-pill <?php echo 'active' === $p->status ? 'confirmed' : 'cancelled'; ?>"><?php echo esc_html( $p->status ); ?></span></td>
                        <td><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=nira-properties&action=edit&id=' . (int) $p->id ) ); ?>"><?php esc_html_e( 'Modifier', 'nira-booking' ); ?></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=nira-properties&action=new' ) ); ?>">+ <?php esc_html_e( 'Nouvel hébergement', 'nira-booking' ); ?></a></p>
        </div>

        <div class="nira-card">
            <h2><?php esc_html_e( 'Réservations récentes', 'nira-booking' ); ?></h2>
            <?php if ( empty( $recent ) ) : ?>
                <p><?php esc_html_e( 'Aucune réservation pour le moment.', 'nira-booking' ); ?></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead><tr><th>Réf.</th><th>Client</th><th>Dates</th><th>Total</th><th>Statut</th></tr></thead>
                    <tbody>
                    <?php foreach ( $recent as $b ) : $lbl = Nira_Admin::status_label( $b->status ); ?>
                        <tr>
                            <td><a href="<?php echo esc_url( admin_url( 'admin.php?page=nira-bookings&action=edit&id=' . (int) $b->id ) ); ?>"><?php echo esc_html( $b->reference ); ?></a></td>
                            <td><?php echo esc_html( $b->guest_name ); ?></td>
                            <td><?php echo esc_html( Nira_Admin::fr_date( $b->check_in ) ); ?> → <?php echo esc_html( Nira_Admin::fr_date( $b->check_out ) ); ?></td>
                            <td><?php echo esc_html( Nira_Admin::money( $b->total ) ); ?></td>
                            <td><span class="nira-pill <?php echo esc_attr( $lbl['class'] ); ?>"><?php echo esc_html( $lbl['label'] ); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=nira-bookings' ) ); ?>"><?php esc_html_e( 'Voir toutes les réservations', 'nira-booking' ); ?> →</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="nira-card">
        <h2><?php esc_html_e( "Comment intégrer le widget", 'nira-booking' ); ?></h2>
        <p><?php esc_html_e( 'Utilisez le shortcode ci-dessous dans votre page ou article :', 'nira-booking' ); ?></p>
        <?php foreach ( $properties as $p ) : ?>
            <p><strong><?php echo esc_html( $p->name ); ?></strong> → <code>[nira_booking slug="<?php echo esc_attr( $p->slug ); ?>"]</code></p>
        <?php endforeach; ?>
    </div>
</div>

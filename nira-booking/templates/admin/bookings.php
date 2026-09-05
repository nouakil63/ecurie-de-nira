<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Réservations', 'nira-booking' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=nira-bookings&action=new' ) ); ?>" class="page-title-action"><?php esc_html_e( 'Nouvelle', 'nira-booking' ); ?></a>
    <hr class="wp-header-end">

    <form method="get" class="nira-filters">
        <input type="hidden" name="page" value="nira-bookings">
        <select name="property_id">
            <option value=""><?php esc_html_e( 'Tous les hébergements', 'nira-booking' ); ?></option>
            <?php foreach ( $properties as $p ) : ?>
                <option value="<?php echo (int) $p->id; ?>" <?php selected( (int) ( $_GET['property_id'] ?? 0 ), $p->id ); ?>><?php echo esc_html( $p->name ); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value=""><?php esc_html_e( 'Tous statuts', 'nira-booking' ); ?></option>
            <?php foreach ( [ 'pending','confirmed','cancelled','refunded','blocked','airbnb' ] as $s ) : ?>
                <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $_GET['status'] ?? '', $s ); ?>><?php echo esc_html( Nira_Admin::status_label( $s )['label'] ); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="search" name="s" value="<?php echo esc_attr( $_GET['s'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Référence, nom, email…', 'nira-booking' ); ?>">
        <button class="button" type="submit"><?php esc_html_e( 'Filtrer', 'nira-booking' ); ?></button>
    </form>

    <table class="widefat striped">
        <thead>
            <tr>
                <th>Réf.</th><th>Hébergement</th><th>Client</th><th>Arrivée</th><th>Départ</th><th>Nuits</th><th>Total</th><th>Payé</th><th>Source</th><th>Statut</th><th></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( empty( $bookings ) ) : ?>
            <tr><td colspan="11"><?php esc_html_e( 'Aucune réservation.', 'nira-booking' ); ?></td></tr>
        <?php else : foreach ( $bookings as $b ) :
            $prop = Nira_Properties::instance()->get( $b->property_id );
            $lbl  = Nira_Admin::status_label( $b->status );
        ?>
            <tr>
                <td><strong><?php echo esc_html( $b->reference ); ?></strong></td>
                <td><?php echo esc_html( $prop->name ?? '—' ); ?></td>
                <td><?php echo esc_html( $b->guest_name ); ?><br><small><?php echo esc_html( $b->guest_email ); ?></small></td>
                <td><?php echo esc_html( Nira_Admin::fr_date( $b->check_in ) ); ?></td>
                <td><?php echo esc_html( Nira_Admin::fr_date( $b->check_out ) ); ?></td>
                <td><?php echo (int) $b->nights; ?></td>
                <td><?php echo esc_html( Nira_Admin::money( $b->total ) ); ?></td>
                <td><?php echo esc_html( Nira_Admin::money( $b->amount_paid ) ); ?></td>
                <td><code><?php echo esc_html( $b->source ); ?></code></td>
                <td><span class="nira-pill <?php echo esc_attr( $lbl['class'] ); ?>"><?php echo esc_html( $lbl['label'] ); ?></span></td>
                <td>
                    <a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=nira-bookings&action=edit&id=' . (int) $b->id ) ); ?>"><?php esc_html_e( 'Modifier', 'nira-booking' ); ?></a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

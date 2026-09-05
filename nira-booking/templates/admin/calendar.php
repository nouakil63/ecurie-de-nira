<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1><?php esc_html_e( 'Calendrier', 'nira-booking' ); ?></h1>

    <form method="get" class="nira-filters">
        <input type="hidden" name="page" value="nira-calendar">
        <select name="property_id" onchange="this.form.submit()">
            <?php foreach ( $properties as $p ) : ?>
                <option value="<?php echo (int) $p->id; ?>" <?php selected( $pid, $p->id ); ?>><?php echo esc_html( $p->name ); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="month" name="month" value="<?php echo esc_attr( $month ); ?>">
        <button class="button" type="submit"><?php esc_html_e( 'Afficher', 'nira-booking' ); ?></button>
    </form>

    <?php if ( $property ) : ?>
    <div class="nira-two-col">
        <div class="nira-card">
            <h2><?php echo esc_html( $property->name ); ?></h2>
            <div class="nira-admin-cal">
                <?php
                $months_fr = [ 'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre' ];
                foreach ( $calendar as $m ) :
                    $first = $m['first_weekday']; // 0=dim
                    $offset = ( $first + 6 ) % 7; // align Lundi
                ?>
                <div class="nira-mini-cal">
                    <h4><?php echo esc_html( $months_fr[ $m['month'] - 1 ] . ' ' . $m['year'] ); ?></h4>
                    <div class="nira-mini-head">
                        <?php foreach ( [ 'L','M','M','J','V','S','D' ] as $d ) : ?>
                            <span><?php echo $d; ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="nira-mini-grid">
                        <?php for ( $i = 0; $i < $offset; $i++ ) : ?><span class="pad"></span><?php endfor; ?>
                        <?php foreach ( $m['days'] as $d ) :
                            $cls = $d['available'] ? 'ok' : ( $d['past'] ? 'past' : 'busy' );
                        ?>
                            <span class="day <?php echo esc_attr( $cls ); ?>" title="<?php echo esc_attr( $d['date'] . ' · ' . number_format( $d['price'], 0, ',', ' ' ) . ' €' ); ?>">
                                <?php echo (int) date( 'j', strtotime( $d['date'] ) ); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <p class="nira-legend"><span class="leg ok"></span><?php esc_html_e( 'Dispo', 'nira-booking' ); ?> <span class="leg busy"></span><?php esc_html_e( 'Occupé', 'nira-booking' ); ?> <span class="leg past"></span><?php esc_html_e( 'Passé', 'nira-booking' ); ?></p>
        </div>

        <div class="nira-card">
            <h2><?php esc_html_e( 'Bloquer une période', 'nira-booking' ); ?></h2>
            <form method="post">
                <?php wp_nonce_field( 'nira_admin_block_dates' ); ?>
                <input type="hidden" name="nira_action" value="block_dates">
                <input type="hidden" name="property_id" value="<?php echo (int) $pid; ?>">
                <p><label><?php esc_html_e( 'Du', 'nira-booking' ); ?><br><input type="date" name="check_in" required></label></p>
                <p><label><?php esc_html_e( 'Au', 'nira-booking' ); ?><br><input type="date" name="check_out" required></label></p>
                <p><label><?php esc_html_e( 'Libellé', 'nira-booking' ); ?><br><input type="text" name="label" value="Bloc manuel" class="regular-text"></label></p>
                <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Bloquer', 'nira-booking' ); ?></button></p>
            </form>

            <hr>
            <h3><?php esc_html_e( 'Réservations sur cette période', 'nira-booking' ); ?></h3>
            <?php if ( empty( $bookings ) ) : ?>
                <p class="description"><?php esc_html_e( 'Aucune.', 'nira-booking' ); ?></p>
            <?php else : ?>
                <ul class="nira-cal-list">
                <?php foreach ( $bookings as $b ) : $lbl = Nira_Admin::status_label( $b->status ); ?>
                    <li>
                        <span class="nira-pill <?php echo esc_attr( $lbl['class'] ); ?>"><?php echo esc_html( $lbl['label'] ); ?></span>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=nira-bookings&action=edit&id=' . (int) $b->id ) ); ?>"><?php echo esc_html( $b->guest_name ?: $b->reference ); ?></a>
                        — <?php echo esc_html( Nira_Admin::fr_date( $b->check_in ) ); ?> → <?php echo esc_html( Nira_Admin::fr_date( $b->check_out ) ); ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

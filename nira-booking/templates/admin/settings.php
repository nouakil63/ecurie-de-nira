<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1><?php esc_html_e( 'Réglages', 'nira-booking' ); ?></h1>

    <form method="post" class="nira-card">
        <?php wp_nonce_field( 'nira_admin_save_settings' ); ?>
        <input type="hidden" name="nira_action" value="save_settings">

        <h2><?php esc_html_e( 'Identité & contact', 'nira-booking' ); ?></h2>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Nom de la structure', 'nira-booking' ); ?></th><td><input type="text" name="business_name" value="<?php echo esc_attr( $settings['business_name'] ); ?>" class="regular-text"></td></tr>
            <tr><th><?php esc_html_e( 'Adresse', 'nira-booking' ); ?></th><td><input type="text" name="business_address" value="<?php echo esc_attr( $settings['business_address'] ); ?>" class="regular-text"></td></tr>
            <tr><th><?php esc_html_e( 'Téléphone', 'nira-booking' ); ?></th><td><input type="text" name="business_phone" value="<?php echo esc_attr( $settings['business_phone'] ); ?>" class="regular-text"></td></tr>
            <tr><th><?php esc_html_e( 'Logo (URL)', 'nira-booking' ); ?></th><td><input type="url" name="logo_url" value="<?php echo esc_attr( $settings['logo_url'] ); ?>" class="regular-text"></td></tr>
        </table>

        <h2><?php esc_html_e( 'Paiement', 'nira-booking' ); ?></h2>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Devise', 'nira-booking' ); ?></th>
                <td><select name="currency">
                    <?php foreach ( [ 'EUR'=>'Euro (€)', 'USD'=>'US Dollar ($)', 'GBP'=>'Livre (£)', 'CHF'=>'Franc suisse' ] as $c=>$lbl ) : ?>
                        <option value="<?php echo esc_attr( $c ); ?>" <?php selected( $settings['currency'], $c ); ?>><?php echo esc_html( $lbl ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
            <tr><th><?php esc_html_e( 'TVA (%)', 'nira-booking' ); ?></th><td><input type="number" step="0.01" min="0" name="tax_rate" value="<?php echo esc_attr( $settings['tax_rate'] ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Taxe de séjour / nuit / personne', 'nira-booking' ); ?></th><td><input type="number" step="0.01" min="0" name="tourist_tax" value="<?php echo esc_attr( $settings['tourist_tax'] ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Mode de facturation', 'nira-booking' ); ?></th>
                <td><select name="charge_mode">
                    <option value="deposit" <?php selected( $settings['charge_mode'], 'deposit' ); ?>><?php esc_html_e( 'Acompte uniquement (solde sur place)', 'nira-booking' ); ?></option>
                    <option value="full" <?php selected( $settings['charge_mode'], 'full' ); ?>><?php esc_html_e( 'Paiement intégral à la réservation', 'nira-booking' ); ?></option>
                </select></td></tr>
            <tr><th>Stripe Publishable Key (pk_…)</th><td><input type="text" name="stripe_pk" value="<?php echo esc_attr( $settings['stripe_pk'] ); ?>" class="large-text code"></td></tr>
            <tr><th>Stripe Secret Key (sk_…)</th><td><input type="password" name="stripe_sk" value="<?php echo $settings['stripe_sk'] ? '••••••••' : ''; ?>" class="large-text code" placeholder="sk_live_…"><p class="description"><?php esc_html_e( 'Laissez les points pour conserver la valeur actuelle.', 'nira-booking' ); ?></p></td></tr>
            <tr><th>Stripe Webhook Secret (whsec_…)</th><td><input type="password" name="stripe_webhook_secret" value="<?php echo $settings['stripe_webhook_secret'] ? '••••••••' : ''; ?>" class="large-text code"><p class="description">Endpoint : <code><?php echo esc_url( rest_url( 'nira/v1/stripe-webhook' ) ); ?></code></p></td></tr>
        </table>

        <h2><?php esc_html_e( 'Horaires par défaut', 'nira-booking' ); ?></h2>
        <table class="form-table">
            <tr><th><?php esc_html_e( "Heure d'arrivée", 'nira-booking' ); ?></th><td><input type="time" name="checkin_time" value="<?php echo esc_attr( $settings['checkin_time'] ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Heure de départ', 'nira-booking' ); ?></th><td><input type="time" name="checkout_time" value="<?php echo esc_attr( $settings['checkout_time'] ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Délai de paiement (minutes)', 'nira-booking' ); ?></th><td><input type="number" min="5" max="240" name="hold_minutes" value="<?php echo esc_attr( $settings['hold_minutes'] ); ?>"><p class="description"><?php esc_html_e( 'Au-delà de ce délai sans paiement, la réservation est libérée automatiquement.', 'nira-booking' ); ?></p></td></tr>
            <tr><th><?php esc_html_e( 'Demande de solde auto (jours avant arrivée)', 'nira-booking' ); ?></th><td><input type="number" min="1" max="90" name="balance_request_days" value="<?php echo esc_attr( $settings['balance_request_days'] ?? 14 ); ?>"><p class="description"><?php esc_html_e( "Le système envoie automatiquement l'email de paiement du solde X jours avant l'arrivée du client.", 'nira-booking' ); ?></p></td></tr>
            <tr><th><?php esc_html_e( "Politique d'annulation par défaut", 'nira-booking' ); ?></th>
                <td><select name="default_cancellation">
                    <?php foreach ( [ 'flexible'=>'Flexible', 'moderate'=>'Modérée', 'strict'=>'Stricte' ] as $v=>$l ) : ?>
                        <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $settings['default_cancellation'], $v ); ?>><?php echo esc_html( $l ); ?></option>
                    <?php endforeach; ?>
                </select></td></tr>
        </table>

        <h2><?php esc_html_e( 'Emails', 'nira-booking' ); ?></h2>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Nom expéditeur', 'nira-booking' ); ?></th><td><input type="text" name="from_name" value="<?php echo esc_attr( $settings['from_name'] ); ?>" class="regular-text"></td></tr>
            <tr><th><?php esc_html_e( 'Email expéditeur', 'nira-booking' ); ?></th><td><input type="email" name="from_email" value="<?php echo esc_attr( $settings['from_email'] ); ?>" class="regular-text"></td></tr>
            <tr><th><?php esc_html_e( 'Email notifications admin', 'nira-booking' ); ?></th><td><input type="email" name="notification_email" value="<?php echo esc_attr( $settings['notification_email'] ); ?>" class="regular-text"></td></tr>
        </table>

        <p><button class="button button-primary button-large" type="submit"><?php esc_html_e( 'Enregistrer les réglages', 'nira-booking' ); ?></button></p>
    </form>

    <div class="nira-card" style="margin-top:24px;border-left:4px solid <?php echo $test_mode ? '#d63638' : '#A41C2B'; ?>;">
        <h2><?php esc_html_e( 'Mode test (1 €)', 'nira-booking' ); ?></h2>
        <?php if ( $test_mode ) :
            $tm_property = Nira_Properties::instance()->get( (int) $test_mode['property_id'] );
        ?>
            <p><strong style="color:#d63638;">⚠ <?php esc_html_e( 'Mode test ACTIF', 'nira-booking' ); ?></strong> —
                <?php printf(
                    esc_html__( '« %s » est à 1 €/nuit depuis le %s. Les vrais prix sont sauvegardés et seront restaurés automatiquement.', 'nira-booking' ),
                    esc_html( $tm_property->name ?? '#' . (int) $test_mode['property_id'] ),
                    esc_html( $test_mode['activated_at'] ?? '' )
                ); ?></p>
            <form method="post">
                <?php wp_nonce_field( 'nira_admin_test_mode_off' ); ?>
                <input type="hidden" name="nira_action" value="test_mode_off">
                <button class="button button-primary" type="submit"><?php esc_html_e( 'Désactiver et restaurer les vrais prix', 'nira-booking' ); ?></button>
            </form>
        <?php else : ?>
            <p><?php esc_html_e( "Passe temporairement un hébergement à 1 €/nuit pour tester une réservation réelle de bout en bout (ménage 0 €, paiement intégral, 1 nuit minimum). Les prix actuels (prix de base, ménage, acompte, remises et toutes les règles tarifaires) sont sauvegardés en base et restaurés automatiquement à la désactivation.", 'nira-booking' ); ?></p>
            <form method="post">
                <?php wp_nonce_field( 'nira_admin_test_mode_on' ); ?>
                <input type="hidden" name="nira_action" value="test_mode_on">
                <select name="property_id">
                    <?php foreach ( $properties as $p ) : ?>
                        <option value="<?php echo (int) $p->id; ?>" <?php selected( 'appartement', $p->slug ); ?>><?php echo esc_html( $p->name ); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="button" type="submit" onclick="return confirm('<?php echo esc_js( __( 'Activer le mode test ? L\'hébergement sélectionné sera réservable à 1 €/nuit par de vrais visiteurs jusqu\'à la désactivation.', 'nira-booking' ) ); ?>');"><?php esc_html_e( 'Activer le mode test à 1 €', 'nira-booking' ); ?></button>
            </form>
        <?php endif; ?>
    </div>
</div>

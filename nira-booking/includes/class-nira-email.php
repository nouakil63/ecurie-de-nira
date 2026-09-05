<?php
/**
 * Emails transactionnels (confirmation, annulation, remboursement).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Email {

    public static function send( $to, $subject, $body, $extra_headers = [] ) {
        $from_name  = Nira_Settings::get( 'from_name',  'Écuries de Nira' );
        $from_email = Nira_Settings::get( 'from_email', get_option( 'admin_email' ) );
        $headers = array_merge( [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
        ], $extra_headers );
        return wp_mail( $to, $subject, self::wrap( $body ), $headers );
    }

    private static function wrap( $content ) {
        $logo    = esc_url( Nira_Settings::get( 'logo_url', '' ) );
        $name    = esc_html( Nira_Settings::get( 'business_name', 'Écuries de Nira' ) );
        $address = esc_html( Nira_Settings::get( 'business_address', '' ) );
        $phone   = esc_html( Nira_Settings::get( 'business_phone', '' ) );
        ob_start(); ?>
        <!doctype html>
        <html><body style="margin:0;padding:0;background:#FDFBF9;font-family:'Helvetica Neue',Arial,sans-serif;color:#2D2D2D;">
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#FDFBF9;padding:40px 0;">
            <tr><td align="center">
              <table width="600" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border-radius:12px;overflow:hidden;box-shadow:0 15px 50px rgba(0,0,0,.06);">
                <tr><td style="background:#A41C2B;padding:30px 40px;text-align:center;color:#FFFFFF;">
                  <?php if ( $logo ) : ?>
                    <img src="<?php echo $logo; ?>" alt="<?php echo $name; ?>" style="max-height:60px;">
                  <?php else : ?>
                    <h1 style="margin:0;font-family:Georgia,serif;font-size:28px;letter-spacing:1px;"><?php echo $name; ?></h1>
                  <?php endif; ?>
                </td></tr>
                <tr><td style="padding:40px;line-height:1.7;font-size:15px;">
                  <?php echo $content; ?>
                </td></tr>
                <tr><td style="background:#2D2D2D;color:#FDFBF9;padding:20px 40px;text-align:center;font-size:12px;">
                  <?php echo $name; ?><?php if ( $address ) echo ' · ' . $address; ?><?php if ( $phone ) echo ' · ' . $phone; ?>
                </td></tr>
              </table>
            </td></tr>
          </table>
        </body></html>
        <?php
        return ob_get_clean();
    }

    public static function send_confirmation( $booking_id ) {
        $b = Nira_Booking::get( $booking_id );
        if ( ! $b ) return;
        $property = Nira_Properties::instance()->get( $b->property_id );
        $checkin_t = $property->checkin_time ?? Nira_Settings::get( 'checkin_time', '16:00' );
        $checkout_t= $property->checkout_time ?? Nira_Settings::get( 'checkout_time', '11:00' );

        $balance_due = Nira_Booking::balance_due( $b );
        $cancel_url  = Nira_Booking::action_url( $b->id, 'cancel' );
        $balance_url = Nira_Booking::action_url( $b->id, 'pay_balance' );
        $policy = $property->cancellation_policy ?? Nira_Settings::get( 'default_cancellation', 'flexible' );
        $policy_label = [ 'flexible' => 'Flexible', 'moderate' => 'Modérée', 'strict' => 'Stricte' ][ $policy ] ?? $policy;

        $balance_block = '';
        if ( $balance_due > 0.01 ) {
            $balance_block = sprintf(
                '<div style="background:#FDFBF9;border:1px solid rgba(164,28,43,0.12);border-left:4px solid #A41C2B;padding:18px 22px;border-radius:6px;margin:24px 0;">
                   <p style="margin:0 0 10px;font-weight:600;color:#A41C2B;">Solde restant : %s €</p>
                   <p style="margin:0 0 14px;font-size:14px;color:#555;">Vous pouvez régler le solde dès maintenant via le lien ci-dessous, ou nous vous le rappellerons à l\'approche de votre arrivée.</p>
                   <a href="%s" style="display:inline-block;background:#A41C2B;color:#fff;padding:10px 22px;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;">Payer le solde</a>
                 </div>',
                number_format( $balance_due, 2, ',', ' ' ),
                esc_url( $balance_url )
            );
        }

        $cancel_block = sprintf(
            '<div style="margin-top:30px;padding-top:20px;border-top:1px solid #eee;font-size:13px;color:#888;">
               <p>Politique d\'annulation : <strong>%s</strong>.<br>Si votre projet change, vous pouvez annuler à tout moment :
                 <a href="%s" style="color:#A41C2B;">Annuler ma réservation</a>.
               </p>
             </div>',
            esc_html( $policy_label ),
            esc_url( $cancel_url )
        );

        $body = sprintf(
            '<h2 style="font-family:Georgia,serif;color:#A41C2B;margin-top:0;">Votre séjour est confirmé ✓</h2>
             <p>Bonjour <strong>%s</strong>,</p>
             <p>Nous avons le plaisir de confirmer votre réservation à <strong>%s</strong>.</p>
             <table cellpadding="8" cellspacing="0" style="border:1px solid #eee;border-radius:8px;width:100%%;margin:20px 0;">
               <tr><td><strong>Référence</strong></td><td>%s</td></tr>
               <tr><td><strong>Arrivée</strong></td><td>%s — à partir de %s</td></tr>
               <tr><td><strong>Départ</strong></td><td>%s — avant %s</td></tr>
               <tr><td><strong>Nuits</strong></td><td>%d</td></tr>
               <tr><td><strong>Voyageurs</strong></td><td>%d</td></tr>
               <tr><td><strong>Total</strong></td><td>%s €</td></tr>
               <tr><td><strong>Acompte payé</strong></td><td>%s €</td></tr>
             </table>
             %s
             <p>À très bientôt,<br>L\'équipe des Écuries de Nira</p>
             %s',
            esc_html( $b->guest_name ),
            esc_html( $property->name ?? '' ),
            esc_html( $b->reference ),
            esc_html( $b->check_in ), esc_html( $checkin_t ),
            esc_html( $b->check_out ), esc_html( $checkout_t ),
            (int) $b->nights,
            (int) $b->guest_count,
            number_format( $b->total, 2, ',', ' ' ),
            number_format( $b->amount_paid, 2, ',', ' ' ),
            $balance_block,
            $cancel_block
        );

        self::send( $b->guest_email, 'Confirmation de votre séjour — ' . $b->reference, $body );
        self::send_admin_notification( $booking_id );
    }

    /**
     * Notification interne envoyée à l'admin à chaque réservation payée.
     * Contenu dédié gestion (client, paiement, solde, lien admin) — pas le
     * même email que le client, qui contient ses liens d'annulation/solde.
     */
    public static function send_admin_notification( $booking_id ) {
        $b = Nira_Booking::get( $booking_id );
        if ( ! $b ) return;
        $property = Nira_Properties::instance()->get( $b->property_id );
        $balance  = Nira_Booking::balance_due( $b );
        $admin_url = admin_url( 'admin.php?page=nira-bookings&action=edit&id=' . (int) $b->id );

        $pay_line = $balance > 0.01
            ? sprintf(
                '<tr><td><strong>Payé (acompte)</strong></td><td>%s €</td></tr>
                 <tr style="background:rgba(164,28,43,0.04);"><td><strong style="color:#A41C2B;">Solde restant</strong></td><td><strong style="color:#A41C2B;">%s €</strong> (demande envoyée automatiquement avant l\'arrivée)</td></tr>',
                number_format( (float) $b->amount_paid, 2, ',', ' ' ),
                number_format( $balance, 2, ',', ' ' )
            )
            : sprintf(
                '<tr><td><strong>Payé</strong></td><td>%s € — <strong style="color:#186837;">intégralement réglé</strong></td></tr>',
                number_format( (float) $b->amount_paid, 2, ',', ' ' )
            );

        $body = sprintf(
            '<h2 style="font-family:Georgia,serif;color:#A41C2B;margin-top:0;">Nouvelle réservation 🎉</h2>
             <p>Une réservation vient d\'être payée sur le site.</p>
             <table cellpadding="8" cellspacing="0" style="border:1px solid #eee;border-radius:8px;width:100%%;margin:20px 0;">
               <tr><td style="width:140px;"><strong>Référence</strong></td><td>%s</td></tr>
               <tr><td><strong>Hébergement</strong></td><td>%s</td></tr>
               <tr><td><strong>Client</strong></td><td>%s — <a href="mailto:%s" style="color:#A41C2B;">%s</a>%s</td></tr>
               <tr><td><strong>Arrivée</strong></td><td>%s</td></tr>
               <tr><td><strong>Départ</strong></td><td>%s</td></tr>
               <tr><td><strong>Nuits / voyageurs</strong></td><td>%d nuits — %d voyageur(s)</td></tr>
               <tr><td><strong>Total séjour</strong></td><td>%s €</td></tr>
               %s
             </table>
             %s
             <p style="text-align:center;margin:28px 0;">
               <a href="%s" style="display:inline-block;background:#A41C2B;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;">Voir la réservation dans l\'admin</a>
             </p>',
            esc_html( $b->reference ),
            esc_html( $property->name ?? '—' ),
            esc_html( $b->guest_name ),
            esc_attr( $b->guest_email ), esc_html( $b->guest_email ),
            $b->guest_phone ? ' — ' . esc_html( $b->guest_phone ) : '',
            esc_html( wp_date( 'd/m/Y', strtotime( $b->check_in ) ) ),
            esc_html( wp_date( 'd/m/Y', strtotime( $b->check_out ) ) ),
            (int) $b->nights,
            (int) $b->guest_count,
            number_format( (float) $b->total, 2, ',', ' ' ),
            $pay_line,
            $b->notes ? '<div style="background:#FDFBF9;border-left:3px solid #A41C2B;border-radius:6px;padding:14px 18px;margin:16px 0;font-size:14px;color:#555;"><strong>Message du client :</strong><br>' . nl2br( esc_html( $b->notes ) ) . '</div>' : '',
            esc_url( $admin_url )
        );

        $admin = Nira_Settings::get( 'notification_email', get_option( 'admin_email' ) );
        self::send( $admin, sprintf( '[Nira] Nouvelle réservation %s — %s', $b->reference, $property->name ?? '' ), $body );
    }

    /**
     * Email de demande de paiement du solde envoyé au client par l'admin.
     */
    public static function send_balance_request( $booking_id ) {
        $b = Nira_Booking::get( $booking_id );
        if ( ! $b ) return false;
        $property = Nira_Properties::instance()->get( $b->property_id );
        $balance = Nira_Booking::balance_due( $b );
        if ( $balance <= 0.01 ) return false;
        $url = Nira_Booking::action_url( $b->id, 'pay_balance' );

        $body = sprintf(
            '<h2 style="font-family:Georgia,serif;color:#A41C2B;margin-top:0;">Le solde de votre séjour</h2>
             <p>Bonjour <strong>%s</strong>,</p>
             <p>Votre arrivée à <strong>%s</strong> approche. Voici un récapitulatif rapide pour finaliser le paiement de votre séjour.</p>
             <table cellpadding="8" cellspacing="0" style="border:1px solid #eee;border-radius:8px;width:100%%;margin:20px 0;">
               <tr><td><strong>Référence</strong></td><td>%s</td></tr>
               <tr><td><strong>Total séjour</strong></td><td>%s €</td></tr>
               <tr><td><strong>Acompte déjà versé</strong></td><td>− %s €</td></tr>
               <tr style="background:rgba(164,28,43,0.04);"><td><strong style="color:#A41C2B;">Solde à régler</strong></td><td><strong style="color:#A41C2B;font-size:1.1em;">%s €</strong></td></tr>
             </table>
             <p style="text-align:center;margin:30px 0;">
               <a href="%s" style="display:inline-block;background:#A41C2B;color:#fff;padding:14px 38px;border-radius:6px;text-decoration:none;font-weight:600;letter-spacing:1px;">PAYER %s €</a>
             </p>
             <p style="font-size:13px;color:#888;">Le paiement est sécurisé par Stripe.</p>',
            esc_html( $b->guest_name ),
            esc_html( $property->name ?? '' ),
            esc_html( $b->reference ),
            number_format( $b->total, 2, ',', ' ' ),
            number_format( $b->amount_paid, 2, ',', ' ' ),
            number_format( $balance, 2, ',', ' ' ),
            esc_url( $url ),
            number_format( $balance, 2, ',', ' ' )
        );

        return self::send( $b->guest_email, 'Solde à régler — ' . $b->reference, $body );
    }

    /**
     * Notification interne quand un visiteur envoie le formulaire de contact.
     * Le Reply-To est l'email du visiteur → on peut répondre directement.
     */
    public static function send_contact_notification( $to, $data ) {
        $name    = esc_html( $data['name']    ?? '' );
        $email   = esc_html( $data['email']   ?? '' );
        $phone   = esc_html( $data['phone']   ?? '' );
        $subject = esc_html( $data['subject'] ?? '' );
        $message = nl2br( esc_html( $data['message'] ?? '' ) );
        $admin_url = esc_url( $data['admin_url'] ?? '' );

        $body = sprintf(
            '<h2 style="font-family:Georgia,serif;color:#A41C2B;margin:0 0 16px;">Nouveau message reçu ✉️</h2>
             <p>Un visiteur a envoyé une demande via le formulaire de contact du site.</p>
             <table cellpadding="10" cellspacing="0" style="border:1px solid #f0eae6;border-radius:8px;width:100%%;margin:20px 0;background:#FDFBF9;">
               <tr><td style="width:130px;color:#888;"><strong>Nom</strong></td><td style="color:#2D2D2D;">%s</td></tr>
               <tr><td style="color:#888;"><strong>Email</strong></td><td><a href="mailto:%s" style="color:#A41C2B;">%s</a></td></tr>
               %s
               <tr><td style="color:#888;"><strong>Sujet</strong></td><td><span style="display:inline-block;padding:4px 10px;background:rgba(164,28,43,0.08);color:#A41C2B;border-radius:100px;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:1px;">%s</span></td></tr>
             </table>
             <div style="background:#FFFFFF;border:1px solid #f0eae6;border-left:3px solid #A41C2B;border-radius:6px;padding:22px 26px;margin:20px 0;font-family:Georgia,serif;line-height:1.7;">
               %s
             </div>
             %s',
            $name,
            $email, $email,
            $phone ? sprintf( '<tr><td style="color:#888;"><strong>Téléphone</strong></td><td><a href="tel:%s" style="color:#A41C2B;">%s</a></td></tr>', preg_replace( '/[^\d+]/', '', $phone ), $phone ) : '',
            $subject ?: '—',
            $message ?: '<em style="color:#999;">Pas de message</em>',
            $admin_url ? sprintf( '<p style="margin-top:24px;"><a href="%s" style="display:inline-block;background:#A41C2B;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;">Voir dans l\'admin</a></p>', $admin_url ) : ''
        );

        $reply_to = ( $data['email'] ?? '' ) ? [ 'Reply-To: ' . $name . ' <' . $data['email'] . '>' ] : [];
        return self::send( $to, sprintf( '[%s] Nouveau message — %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $data['name'] ?? '' ), $body, $reply_to );
    }

    /**
     * Accusé de réception envoyé au visiteur après soumission du formulaire.
     */
    public static function send_contact_autoreply( $to, $data ) {
        $name    = esc_html( $data['name']    ?? '' );
        $subject = esc_html( $data['subject'] ?? '' );
        $message = nl2br( esc_html( $data['message'] ?? '' ) );
        $business_name = esc_html( Nira_Settings::get( 'business_name', 'Écuries de Nira' ) );
        $business_phone = esc_html( Nira_Settings::get( 'business_phone', '06 74 57 28 19' ) );

        $body = sprintf(
            '<h2 style="font-family:Georgia,serif;color:#A41C2B;margin:0 0 16px;">Merci pour votre message %s ✓</h2>
             <p>Nous avons bien reçu votre demande et reviendrons vers vous dans les plus brefs délais.</p>
             <p style="color:#666;">En attendant, vous pouvez aussi nous joindre directement par téléphone au <a href="tel:%s" style="color:#A41C2B;font-weight:600;">%s</a>.</p>
             <h3 style="font-family:Georgia,serif;color:#2D2D2D;margin-top:30px;font-size:18px;">Récapitulatif de votre message</h3>
             <table cellpadding="8" cellspacing="0" style="border:1px solid #f0eae6;border-radius:8px;width:100%%;margin:14px 0;background:#FDFBF9;font-size:14px;">
               <tr><td style="width:110px;color:#888;"><strong>Sujet</strong></td><td>%s</td></tr>
             </table>
             <div style="background:#FFFFFF;border:1px solid #f0eae6;border-left:3px solid #A41C2B;border-radius:6px;padding:20px 24px;margin:14px 0;font-family:Georgia,serif;font-size:14px;line-height:1.7;color:#555;font-style:italic;">
               %s
             </div>
             <p style="margin-top:30px;">À très bientôt,<br><strong>%s</strong></p>',
            $name,
            preg_replace( '/[^\d+]/', '', $business_phone ),
            $business_phone,
            $subject ?: '—',
            $message,
            $business_name
        );

        return self::send( $to, 'Nous avons bien reçu votre message — ' . $business_name, $body );
    }

    public static function send_cancellation( $booking_id, $refund_amount ) {
        $b = Nira_Booking::get( $booking_id );
        if ( ! $b ) return;
        $body = sprintf(
            '<h2 style="font-family:Georgia,serif;color:#A41C2B;margin-top:0;">Votre réservation a été annulée</h2>
             <p>Bonjour <strong>%s</strong>,</p>
             <p>Votre réservation <strong>%s</strong> a été annulée.</p>
             <p>Montant remboursé : <strong>%s €</strong></p>',
            esc_html( $b->guest_name ),
            esc_html( $b->reference ),
            number_format( (float) $refund_amount, 2, ',', ' ' )
        );
        self::send( $b->guest_email, 'Annulation — ' . $b->reference, $body );
    }
}

add_action( 'nira_booking_cancelled', [ 'Nira_Email', 'send_cancellation' ], 10, 2 );

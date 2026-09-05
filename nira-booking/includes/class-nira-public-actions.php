<?php
/**
 * Pages publiques d'action sur réservation :
 *  - /?nira_action=cancel&b={id}&t={token}     → page d'annulation
 *  - /?nira_action=pay_balance&b={id}&t={token} → page de paiement du solde
 *
 * Tokens HMAC liés à la booking (id + reference + email + secret WP) :
 * impossibles à deviner sans accès au site.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class Nira_Public_Actions {

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'route' ], 5 );
    }

    public function route() {
        if ( empty( $_GET['nira_action'] ) || empty( $_GET['b'] ) || empty( $_GET['t'] ) ) {
            return;
        }
        $action     = sanitize_key( wp_unslash( $_GET['nira_action'] ) );
        $booking_id = (int) $_GET['b'];
        $token      = sanitize_text_field( wp_unslash( $_GET['t'] ) );

        if ( ! in_array( $action, [ 'cancel', 'pay_balance' ], true ) ) return;

        $booking = Nira_Booking::get( $booking_id );
        if ( ! $booking ) {
            wp_die( esc_html__( 'Réservation introuvable.', 'nira-booking' ), '', [ 'response' => 404 ] );
        }
        if ( ! Nira_Booking::verify_action_token( $booking_id, $action, $token ) ) {
            wp_die( esc_html__( 'Lien invalide ou expiré.', 'nira-booking' ), '', [ 'response' => 403 ] );
        }

        if ( 'cancel' === $action ) {
            $this->handle_cancel( $booking );
        } elseif ( 'pay_balance' === $action ) {
            $this->handle_pay_balance( $booking );
        }
        exit;
    }

    /* ============================================================
       ANNULATION CLIENT
       ============================================================ */

    private function handle_cancel( $booking ) {
        $confirmed = ! empty( $_POST['confirm'] ) && check_admin_referer( 'nira_cancel_' . $booking->id );
        $error = '';
        $success = false;

        if ( in_array( $booking->status, [ 'cancelled', 'refunded' ], true ) ) {
            $error = __( 'Cette réservation est déjà annulée.', 'nira-booking' );
        } elseif ( $confirmed ) {
            $res = Nira_Booking::cancel( $booking->id, __( 'Annulation par le client via lien email', 'nira-booking' ) );
            if ( is_wp_error( $res ) ) {
                $error = $res->get_error_message();
            } else {
                $success = true;
                Nira_Email::send_cancellation( $booking->id, (float) ( $res['refunded'] ?? 0 ) );
                // Recharge la réservation après annulation
                $booking = Nira_Booking::get( $booking->id );
            }
        }

        $property = Nira_Properties::instance()->get( (int) $booking->property_id );
        $refund_estimate = $confirmed ? null : Nira_Booking::compute_refund( $booking, $property );

        $this->render_page( 'cancel', [
            'booking'         => $booking,
            'property'        => $property,
            'refund_estimate' => $refund_estimate,
            'success'         => $success,
            'error'           => $error,
        ] );
    }

    /* ============================================================
       PAIEMENT DU SOLDE
       ============================================================ */

    private function handle_pay_balance( $booking ) {
        $remaining = max( 0, (float) $booking->total - (float) $booking->amount_paid );
        $error = '';
        $client_secret = '';
        $intent_id = '';

        if ( in_array( $booking->status, [ 'cancelled', 'refunded' ], true ) ) {
            $error = __( 'Cette réservation est annulée.', 'nira-booking' );
        } elseif ( $remaining <= 0.01 ) {
            $error = __( 'Cette réservation est déjà entièrement payée.', 'nira-booking' );
        } else {
            $intent = Nira_Stripe::create_intent_for_amount( $booking, $remaining, 'balance' );
            if ( is_wp_error( $intent ) ) {
                $error = $intent->get_error_message();
            } else {
                $client_secret = $intent['client_secret'];
                $intent_id     = $intent['payment_intent_id'];
            }
        }

        $property = Nira_Properties::instance()->get( (int) $booking->property_id );

        $this->render_page( 'pay_balance', [
            'booking'       => $booking,
            'property'      => $property,
            'remaining'     => $remaining,
            'client_secret' => $client_secret,
            'intent_id'     => $intent_id,
            'error'         => $error,
        ] );
    }

    /* ============================================================
       RENDU HTML (page autonome — pas de header WP)
       ============================================================ */

    private function render_page( $type, $vars ) {
        $b = $vars['booking'];
        $property = $vars['property'];
        $stripe_pk = Nira_Stripe::public_key();
        $business = Nira_Settings::get( 'business_name', 'Écuries de Nira' );

        ?><!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( $type === 'cancel' ? 'Annulation' : 'Paiement du solde' ); ?> · <?php echo esc_html( $business ); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if ( $type === 'pay_balance' && $stripe_pk ) : ?>
        <script src="https://js.stripe.com/v3/"></script>
    <?php endif; ?>
    <style>
        :root { --bordeaux:#A41C2B; --anthracite:#2D2D2D; --sand:#FDFBF9; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--sand); color: var(--anthracite); padding: 60px 5%; min-height: 100vh; line-height: 1.65; }
        .nb-card { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 14px; padding: 50px 44px; box-shadow: 0 30px 80px rgba(0,0,0,0.06); }
        .nb-logo { text-align: center; margin-bottom: 24px; font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--bordeaux); font-style: italic; }
        h1 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 12px; color: var(--anthracite); }
        h1 em { font-style: italic; color: var(--bordeaux); font-weight: 400; }
        p { margin-bottom: 14px; color: #555; }
        .nb-meta { background: var(--sand); border: 1px solid rgba(164,28,43,0.08); border-radius: 8px; padding: 18px 22px; margin: 24px 0; font-size: 0.95rem; }
        .nb-meta-row { display: flex; justify-content: space-between; padding: 6px 0; }
        .nb-meta-row strong { color: var(--anthracite); }
        .nb-btn { display: inline-block; padding: 14px 32px; background: var(--bordeaux); color: #fff; border: 0; border-radius: 100px; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; font-size: 0.85rem; cursor: pointer; transition: all 0.3s; font-family: inherit; }
        .nb-btn:hover { background: #2D2D2D; transform: translateY(-2px); }
        .nb-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .nb-btn-light { background: transparent; color: var(--anthracite); border: 1px solid #ddd; }
        .nb-btn-light:hover { background: #f5f5f5; transform: none; }
        .nb-actions { display: flex; gap: 12px; margin-top: 28px; flex-wrap: wrap; }
        .nb-success { background: #E7F8EC; color: #186837; border-left: 4px solid #2EA043; padding: 18px 22px; border-radius: 6px; margin-bottom: 24px; }
        .nb-error   { background: #FBE9EC; color: var(--bordeaux); border-left: 4px solid var(--bordeaux); padding: 18px 22px; border-radius: 6px; margin-bottom: 24px; }
        .nb-refund-pill { display: inline-block; padding: 8px 16px; background: rgba(164,28,43,0.07); color: var(--bordeaux); border-radius: 100px; font-weight: 600; font-size: 0.95rem; }
        .nb-stripe-mount { padding: 18px; border: 1px solid #e0e0e0; border-radius: 8px; margin: 22px 0 14px; background: #fff; }
        .nb-stripe-error { color: var(--bordeaux); margin-top: 8px; font-size: 0.9rem; }
        a.nb-back { display: inline-block; margin-top: 28px; color: #888; font-size: 0.85rem; text-decoration: none; }
        a.nb-back:hover { color: var(--bordeaux); }
    </style>
</head>
<body>
<div class="nb-card">
    <div class="nb-logo">Écurie de Nira</div>

    <?php if ( $type === 'cancel' ) : ?>
        <?php $this->render_cancel_body( $vars ); ?>
    <?php else : ?>
        <?php $this->render_pay_balance_body( $vars ); ?>
    <?php endif; ?>

    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nb-back">← Retour à l'accueil</a>
</div>
</body>
</html><?php
    }

    private function render_cancel_body( $vars ) {
        $b = $vars['booking'];
        $property = $vars['property'];
        if ( ! empty( $vars['success'] ) ) : ?>
            <div class="nb-success"><strong>✓ Réservation annulée.</strong> Si un remboursement était dû, il sera effectué sur votre carte sous 5 à 10 jours ouvrés.</div>
            <p>Nous sommes désolés que votre projet ne se concrétise pas cette fois. N'hésitez pas à nous recontacter pour vos prochains séjours.</p>
            <?php return;
        endif;
        if ( ! empty( $vars['error'] ) ) : ?>
            <div class="nb-error"><?php echo esc_html( $vars['error'] ); ?></div>
            <?php return;
        endif;
        $refund = (float) $vars['refund_estimate'];
        ?>
        <h1>Annuler votre <em>réservation</em>.</h1>
        <p>Vous êtes sur le point d'annuler la réservation <strong><?php echo esc_html( $b->reference ); ?></strong>.</p>

        <div class="nb-meta">
            <div class="nb-meta-row"><span>Hébergement</span><strong><?php echo esc_html( $property->name ?? '—' ); ?></strong></div>
            <div class="nb-meta-row"><span>Arrivée</span><strong><?php echo esc_html( wp_date( 'd/m/Y', strtotime( $b->check_in ) ) ); ?></strong></div>
            <div class="nb-meta-row"><span>Départ</span><strong><?php echo esc_html( wp_date( 'd/m/Y', strtotime( $b->check_out ) ) ); ?></strong></div>
            <div class="nb-meta-row"><span>Total payé</span><strong><?php echo number_format( (float) $b->amount_paid, 2, ',', ' ' ); ?> €</strong></div>
        </div>

        <p>Selon notre politique d'annulation <strong><?php echo esc_html( $property->cancellation_policy ?? 'flexible' ); ?></strong>, vous serez remboursé&nbsp;:</p>
        <p style="text-align:center;margin:18px 0;"><span class="nb-refund-pill"><?php echo number_format( $refund, 2, ',', ' ' ); ?> € sur <?php echo number_format( (float) $b->amount_paid, 2, ',', ' ' ); ?> € payés</span></p>

        <form method="post">
            <?php wp_nonce_field( 'nira_cancel_' . $b->id ); ?>
            <input type="hidden" name="confirm" value="1">
            <div class="nb-actions">
                <button type="submit" class="nb-btn">Confirmer l'annulation</button>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nb-btn nb-btn-light" style="text-decoration:none;display:inline-flex;align-items:center;">Garder ma réservation</a>
            </div>
        </form>
        <?php
    }

    private function render_pay_balance_body( $vars ) {
        $b = $vars['booking'];
        $property = $vars['property'];
        $remaining = (float) $vars['remaining'];

        if ( ! empty( $vars['error'] ) ) : ?>
            <div class="nb-error"><?php echo esc_html( $vars['error'] ); ?></div>
            <?php return;
        endif;
        ?>
        <h1>Paiement du <em>solde</em>.</h1>
        <p>Réservation <strong><?php echo esc_html( $b->reference ); ?></strong> — <?php echo esc_html( $property->name ?? '' ); ?></p>

        <div class="nb-meta">
            <div class="nb-meta-row"><span>Total séjour</span><strong><?php echo number_format( (float) $b->total, 2, ',', ' ' ); ?> €</strong></div>
            <div class="nb-meta-row"><span>Acompte déjà versé</span><strong>− <?php echo number_format( (float) $b->amount_paid, 2, ',', ' ' ); ?> €</strong></div>
            <div class="nb-meta-row" style="border-top:1px solid rgba(0,0,0,0.07);padding-top:10px;margin-top:6px;color:var(--bordeaux)"><span><strong>Solde à régler</strong></span><strong><?php echo number_format( $remaining, 2, ',', ' ' ); ?> €</strong></div>
        </div>

        <div class="nb-stripe-mount" id="nira-stripe-balance"></div>
        <div class="nb-stripe-error" id="nira-balance-error" hidden></div>

        <button type="button" id="nira-balance-pay" class="nb-btn">Payer <?php echo number_format( $remaining, 2, ',', ' ' ); ?> €</button>

        <script>
        (function () {
            var pk = <?php echo wp_json_encode( Nira_Stripe::public_key() ); ?>;
            var clientSecret = <?php echo wp_json_encode( $vars['client_secret'] ); ?>;
            var btn = document.getElementById('nira-balance-pay');
            var errEl = document.getElementById('nira-balance-error');
            if (!pk || !clientSecret || !window.Stripe) {
                errEl.hidden = false;
                errEl.textContent = 'Stripe n\'est pas disponible.';
                btn.disabled = true;
                return;
            }
            var stripe = Stripe(pk, { locale: 'fr' });
            var elements = stripe.elements({ clientSecret: clientSecret, appearance: { theme: 'stripe', variables: { colorPrimary: '#A41C2B', borderRadius: '8px' } } });
            var pay = elements.create('payment', { layout: { type: 'tabs' } });
            pay.mount('#nira-stripe-balance');

            btn.addEventListener('click', function () {
                btn.disabled = true;
                errEl.hidden = true;
                stripe.confirmPayment({
                    elements: elements,
                    confirmParams: { return_url: window.location.href + '&paid=1' },
                    redirect: 'if_required'
                }).then(function (res) {
                    if (res.error) {
                        errEl.hidden = false;
                        errEl.textContent = res.error.message || 'Erreur de paiement.';
                        btn.disabled = false;
                        return;
                    }
                    if (res.paymentIntent && (res.paymentIntent.status === 'succeeded' || res.paymentIntent.status === 'processing')) {
                        document.querySelector('.nb-card').innerHTML =
                            '<div class="nb-logo">Écurie de Nira</div>' +
                            '<div class="nb-success"><strong>✓ Paiement reçu.</strong> Merci, votre solde est réglé. Un email de confirmation va vous être envoyé.</div>';
                    }
                });
            });
        })();
        </script>
        <?php
    }
}

<?php
/**
 * Shortcode frontend `[nira_booking slug="appartement"]`.
 * Rend la carte de réservation type Airbnb.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Nira_Shortcode {

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'nira_booking', [ $this, 'render' ] );
    }

    public function render( $atts ) {
        $atts = shortcode_atts( [
            'slug'   => 'appartement',
            'months' => 2,
            'layout' => 'card',
        ], $atts, 'nira_booking' );

        $property = Nira_Properties::instance()->get_by_slug( $atts['slug'] );
        if ( ! $property ) {
            return '<p class="nira-error">' . esc_html__( 'Hébergement introuvable.', 'nira-booking' ) . '</p>';
        }
        if ( 'active' !== $property->status ) {
            return '<p class="nira-error">' . esc_html__( 'Hébergement momentanément indisponible.', 'nira-booking' ) . '</p>';
        }

        wp_enqueue_style( 'nira-booking' );
        wp_enqueue_script( 'nira-booking' );
        if ( Nira_Stripe::public_key() ) {
            wp_enqueue_script( 'nira-stripe', 'https://js.stripe.com/v3/', [], null, true );
        }

        $currency = Nira_Settings::get( 'currency', 'EUR' );
        $symbol   = $this->currency_symbol( $currency );
        $base_price = (float) $property->base_price;

        ob_start(); ?>
        <div class="nira-booking-wrap"
             data-slug="<?php echo esc_attr( $property->slug ); ?>"
             data-property-id="<?php echo (int) $property->id; ?>"
             data-months="<?php echo (int) $atts['months']; ?>"
             data-capacity="<?php echo (int) $property->capacity; ?>"
             data-base-price="<?php echo esc_attr( $property->base_price ); ?>"
             data-deposit-pct="<?php echo (int) $property->deposit_pct; ?>"
             data-min-nights="<?php echo (int) $property->min_nights; ?>"
             data-currency="<?php echo esc_attr( $currency ); ?>"
             data-currency-symbol="<?php echo esc_attr( $symbol ); ?>"
             data-checkin-time="<?php echo esc_attr( $property->checkin_time ); ?>"
             data-checkout-time="<?php echo esc_attr( $property->checkout_time ); ?>">

            <div class="nira-booking-card">
                <div class="nira-bc-header">
                    <div class="nira-bc-price">
                        <span class="nira-bc-amount"><?php echo number_format( $base_price, 0, ',', ' ' ); ?>&nbsp;<?php echo esc_html( $symbol ); ?></span>
                        <span class="nira-bc-period">/ <?php esc_html_e( 'nuit', 'nira-booking' ); ?></span>
                    </div>
                    <div class="nira-bc-meta">
                        <span><i class="fa-solid fa-user-group"></i> <?php echo (int) $property->capacity; ?></span>
                        <span><i class="fa-solid fa-bed"></i> <?php echo (int) $property->bedrooms; ?></span>
                    </div>
                </div>

                <div class="nira-date-picker" role="button" tabindex="0">
                    <div class="nira-date-field" data-field="checkin">
                        <label><?php esc_html_e( 'ARRIVÉE', 'nira-booking' ); ?></label>
                        <span class="nira-date-value"><?php esc_html_e( 'Ajouter une date', 'nira-booking' ); ?></span>
                    </div>
                    <div class="nira-date-field" data-field="checkout">
                        <label><?php esc_html_e( 'DÉPART', 'nira-booking' ); ?></label>
                        <span class="nira-date-value"><?php esc_html_e( 'Ajouter une date', 'nira-booking' ); ?></span>
                    </div>
                </div>

                <div class="nira-guest-select">
                    <label><?php esc_html_e( 'VOYAGEURS', 'nira-booking' ); ?></label>
                    <div class="nira-guest-stepper" data-min="1" data-max="<?php echo (int) $property->capacity; ?>">
                        <button type="button" class="nira-step" data-step="-1" aria-label="-">−</button>
                        <span class="nira-guest-value">1</span>
                        <button type="button" class="nira-step" data-step="1" aria-label="+">+</button>
                    </div>
                </div>

                <button type="button" class="nira-btn-primary" disabled><?php esc_html_e( 'Sélectionnez vos dates', 'nira-booking' ); ?></button>

                <div class="nira-price-breakdown" hidden>
                    <div class="nira-row nira-sub"><span class="nira-row-label"></span><span class="nira-row-value"></span></div>
                    <div class="nira-row nira-cleaning-row"><span><?php esc_html_e( 'Frais de ménage', 'nira-booking' ); ?></span><span class="nira-cleaning"></span></div>
                    <div class="nira-row nira-taxes-row" hidden><span><?php esc_html_e( 'Taxes', 'nira-booking' ); ?></span><span class="nira-tax-value"></span></div>
                    <div class="nira-row nira-total-row"><span><?php esc_html_e( 'Total', 'nira-booking' ); ?></span><span class="nira-total-value"></span></div>
                    <div class="nira-row nira-deposit-row"><span><?php esc_html_e( 'Acompte à verser', 'nira-booking' ); ?></span><span class="nira-deposit-value"></span></div>
                </div>

                <div class="nira-bc-footer">
                    <i class="fa-solid fa-shield-heart"></i> <?php esc_html_e( 'Paiement sécurisé par Stripe', 'nira-booking' ); ?>
                </div>
            </div>

            <!-- Calendar backdrop + popover -->
            <div class="nira-cal-backdrop" hidden></div>
            <div class="nira-calendar-popover" hidden role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Sélectionnez vos dates', 'nira-booking' ); ?>">
                <button type="button" class="nira-cal-close" aria-label="<?php esc_attr_e( 'Fermer', 'nira-booking' ); ?>"><i class="fa-solid fa-xmark"></i></button>
                <div class="nira-cal-header">
                    <button type="button" class="nira-cal-nav" data-dir="-1" aria-label="<?php esc_attr_e( 'Mois précédent', 'nira-booking' ); ?>"><i class="fa-solid fa-chevron-left"></i></button>
                    <div class="nira-cal-title"></div>
                    <button type="button" class="nira-cal-nav" data-dir="1" aria-label="<?php esc_attr_e( 'Mois suivant', 'nira-booking' ); ?>"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
                <div class="nira-cal-months"></div>
                <div class="nira-cal-footer">
                    <div class="nira-cal-legend">
                        <span class="nira-lg-available"><i></i> <?php esc_html_e( 'Disponible', 'nira-booking' ); ?></span>
                        <span class="nira-lg-unavail"><i></i> <?php esc_html_e( 'Indisponible', 'nira-booking' ); ?></span>
                    </div>
                    <button type="button" class="nira-cal-clear"><?php esc_html_e( 'Effacer', 'nira-booking' ); ?></button>
                </div>
            </div>

            <!-- Checkout modal -->
            <div class="nira-modal" hidden>
                <div class="nira-modal-backdrop"></div>
                <div class="nira-modal-dialog" role="dialog" aria-modal="true">
                    <button type="button" class="nira-modal-close" aria-label="<?php esc_attr_e( 'Fermer', 'nira-booking' ); ?>"><i class="fa-solid fa-xmark"></i></button>
                    <h2><?php esc_html_e( 'Finaliser votre réservation', 'nira-booking' ); ?></h2>
                    <div class="nira-modal-summary"></div>

                    <form class="nira-checkout-form" autocomplete="on">
                        <div class="nira-form-grid">
                            <label><span><?php esc_html_e( 'Nom complet', 'nira-booking' ); ?></span>
                                <input type="text" name="guest_name" required autocomplete="name">
                            </label>
                            <label><span><?php esc_html_e( 'E-mail', 'nira-booking' ); ?></span>
                                <input type="email" name="guest_email" required autocomplete="email">
                            </label>
                            <label><span><?php esc_html_e( 'Téléphone', 'nira-booking' ); ?></span>
                                <input type="tel" name="guest_phone" autocomplete="tel">
                            </label>
                        </div>
                        <label class="nira-full"><span><?php esc_html_e( 'Message (optionnel)', 'nira-booking' ); ?></span>
                            <textarea name="notes" rows="2" placeholder="<?php esc_attr_e( 'Informations particulières, chevaux à accueillir, etc.', 'nira-booking' ); ?>"></textarea>
                        </label>

                        <div class="nira-stripe-mount"></div>
                        <div class="nira-stripe-error" hidden></div>

                        <button type="submit" class="nira-btn-primary nira-pay-btn">
                            <span class="nira-pay-label"><?php esc_html_e( 'Payer et réserver', 'nira-booking' ); ?></span>
                            <span class="nira-pay-spinner" hidden aria-hidden="true"></span>
                        </button>
                    </form>

                    <div class="nira-success" hidden>
                        <i class="fa-solid fa-circle-check"></i>
                        <h3><?php esc_html_e( 'Réservation confirmée', 'nira-booking' ); ?></h3>
                        <p class="nira-success-ref"></p>
                        <p><?php esc_html_e( 'Un e-mail récapitulatif vient de vous être envoyé.', 'nira-booking' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function currency_symbol( $code ) {
        $map = [ 'EUR' => '€', 'USD' => '$', 'GBP' => '£', 'CHF' => 'CHF', 'CAD' => '$' ];
        return $map[ strtoupper( $code ) ] ?? $code;
    }
}

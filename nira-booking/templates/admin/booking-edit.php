<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1><?php echo $booking ? esc_html__( 'Réservation ', 'nira-booking' ) . esc_html( $booking->reference ) : esc_html__( 'Nouvelle réservation', 'nira-booking' ); ?></h1>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=nira-bookings' ) ); ?>">← <?php esc_html_e( 'Retour à la liste', 'nira-booking' ); ?></a></p>

    <div class="nira-two-col">
        <div class="nira-card">
            <h2><?php esc_html_e( 'Détails', 'nira-booking' ); ?></h2>
            <form method="post">
                <?php wp_nonce_field( 'nira_admin_save_booking' ); ?>
                <input type="hidden" name="nira_action" value="save_booking">
                <?php if ( $booking ) : ?><input type="hidden" name="id" value="<?php echo (int) $booking->id; ?>"><?php endif; ?>

                <table class="form-table">
                    <tr><th><?php esc_html_e( 'Hébergement', 'nira-booking' ); ?></th>
                        <td>
                            <select name="property_id" required <?php echo $booking ? 'disabled' : ''; ?>>
                                <?php foreach ( $properties as $p ) : ?>
                                    <option value="<?php echo (int) $p->id; ?>" <?php selected( $booking->property_id ?? 0, $p->id ); ?>><?php echo esc_html( $p->name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ( $booking ) : ?><input type="hidden" name="property_id" value="<?php echo (int) $booking->property_id; ?>"><?php endif; ?>
                        </td>
                    </tr>
                    <tr><th><?php esc_html_e( 'Nom', 'nira-booking' ); ?></th><td><input type="text" name="guest_name" value="<?php echo esc_attr( $booking->guest_name ?? '' ); ?>" required class="regular-text"></td></tr>
                    <tr><th><?php esc_html_e( 'E-mail', 'nira-booking' ); ?></th><td><input type="email" name="guest_email" value="<?php echo esc_attr( $booking->guest_email ?? '' ); ?>" required class="regular-text"></td></tr>
                    <tr><th><?php esc_html_e( 'Téléphone', 'nira-booking' ); ?></th><td><input type="tel" name="guest_phone" value="<?php echo esc_attr( $booking->guest_phone ?? '' ); ?>" class="regular-text"></td></tr>
                    <tr><th><?php esc_html_e( 'Voyageurs', 'nira-booking' ); ?></th><td><input type="number" name="guest_count" min="1" value="<?php echo esc_attr( $booking->guest_count ?? 1 ); ?>"></td></tr>
                    <tr><th><?php esc_html_e( 'Arrivée', 'nira-booking' ); ?></th><td><input type="date" name="check_in" value="<?php echo esc_attr( $booking->check_in ?? '' ); ?>" required></td></tr>
                    <tr><th><?php esc_html_e( 'Départ', 'nira-booking' ); ?></th><td><input type="date" name="check_out" value="<?php echo esc_attr( $booking->check_out ?? '' ); ?>" required></td></tr>
                    <tr><th><?php esc_html_e( 'Statut', 'nira-booking' ); ?></th>
                        <td>
                            <select name="status">
                                <?php foreach ( [ 'pending','confirmed','cancelled','refunded','blocked','airbnb' ] as $s ) : ?>
                                    <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $booking->status ?? 'pending', $s ); ?>><?php echo esc_html( Nira_Admin::status_label( $s )['label'] ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr><th><?php esc_html_e( 'Notes', 'nira-booking' ); ?></th><td><textarea name="notes" rows="4" class="large-text"><?php echo esc_textarea( $booking->notes ?? '' ); ?></textarea></td></tr>
                    <?php if ( ! $booking ) : ?>
                        <tr><th></th><td><label><input type="checkbox" name="mark_confirmed" value="1"> <?php esc_html_e( 'Marquer comme confirmée immédiatement', 'nira-booking' ); ?></label></td></tr>
                    <?php endif; ?>
                </table>
                <p><button class="button button-primary" type="submit"><?php esc_html_e( 'Enregistrer', 'nira-booking' ); ?></button></p>
            </form>
        </div>

        <?php if ( $booking ) :
            $balance_due = Nira_Booking::balance_due( $booking );
            $is_cancelled = in_array( $booking->status, [ 'cancelled', 'refunded' ], true );
            $deposit_paid = (float) $booking->amount_paid >= 0.01;
            $fully_paid = $balance_due <= 0.01 && (float) $booking->amount_paid >= 0.01;
            $property_obj = isset($property) ? $property : Nira_Properties::instance()->get( (int) $booking->property_id );
            $auto_refund = Nira_Booking::compute_refund( $booking, $property_obj );
        ?>
        <div class="nira-card">
            <h2><?php esc_html_e( 'Paiement & Statut', 'nira-booking' ); ?></h2>

            <!-- Timeline paiement -->
            <div style="background:#FDFBF9;border:1px solid rgba(164,28,43,0.08);border-radius:8px;padding:18px 20px;margin-bottom:20px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:#888;margin-bottom:4px;">Total séjour</div>
                        <div style="font-size:1.5rem;font-weight:700;color:#2D2D2D;"><?php echo esc_html( Nira_Admin::money( $booking->total ) ); ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:#888;margin-bottom:4px;">Acompte attendu</div>
                        <div style="font-size:1.5rem;font-weight:700;color:#2D2D2D;"><?php echo esc_html( Nira_Admin::money( $booking->deposit ) ); ?></div>
                    </div>
                </div>
                <hr style="border:0;border-top:1px dashed rgba(0,0,0,0.08);margin:14px 0;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div>
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:#186837;margin-bottom:4px;">Déjà payé</div>
                        <div style="font-size:1.3rem;font-weight:700;color:#186837;"><?php echo esc_html( Nira_Admin::money( $booking->amount_paid ) ); ?></div>
                    </div>
                    <div>
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:<?php echo $balance_due > 0 ? '#A41C2B' : '#888'; ?>;margin-bottom:4px;">Solde à régler</div>
                        <div style="font-size:1.3rem;font-weight:700;color:<?php echo $balance_due > 0 ? '#A41C2B' : '#888'; ?>;"><?php echo esc_html( Nira_Admin::money( $balance_due ) ); ?></div>
                    </div>
                </div>
                <?php if ( (float) $booking->amount_refunded > 0 ) : ?>
                    <hr style="border:0;border-top:1px dashed rgba(0,0,0,0.08);margin:14px 0;">
                    <div>
                        <div style="font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:#888;margin-bottom:4px;">Remboursé</div>
                        <div style="font-size:1.1rem;font-weight:700;color:#888;"><?php echo esc_html( Nira_Admin::money( $booking->amount_refunded ) ); ?></div>
                    </div>
                <?php endif; ?>
                <?php if ( $booking->stripe_payment_intent ) : ?>
                    <hr style="border:0;border-top:1px dashed rgba(0,0,0,0.08);margin:14px 0;">
                    <div style="font-size:11px;color:#aaa;">Stripe : <code style="font-size:11px;"><?php echo esc_html( $booking->stripe_payment_intent ); ?></code></div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <?php if ( ! $is_cancelled ) : ?>
                <h3 style="margin-top:24px;font-size:14px;text-transform:uppercase;letter-spacing:1.5px;color:#888;">Actions</h3>

                <?php if ( $balance_due > 0.01 && $deposit_paid ) : ?>
                <form method="post" style="margin-bottom:12px;">
                    <?php wp_nonce_field( 'nira_admin_send_balance_request' ); ?>
                    <input type="hidden" name="nira_action" value="send_balance_request">
                    <input type="hidden" name="id" value="<?php echo (int) $booking->id; ?>">
                    <button type="submit" class="button button-primary" style="width:100%;background:#A41C2B;border-color:#A41C2B;">
                        💳 Envoyer la demande de paiement du solde (<?php echo esc_html( Nira_Admin::money( $balance_due ) ); ?>)
                    </button>
                </form>
                <?php endif; ?>

                <form method="post" style="margin-bottom:12px;">
                    <?php wp_nonce_field( 'nira_admin_send_confirmation_email' ); ?>
                    <input type="hidden" name="nira_action" value="send_confirmation_email">
                    <input type="hidden" name="id" value="<?php echo (int) $booking->id; ?>">
                    <button type="submit" class="button button-secondary" style="width:100%;">
                        📧 Renvoyer l'email de confirmation
                    </button>
                </form>

                <details style="margin-top:18px;border:1px solid #eee;border-radius:6px;padding:0;">
                    <summary style="padding:12px 16px;cursor:pointer;background:#fafafa;border-radius:6px;font-weight:600;color:#A41C2B;">⚠️ Annuler la réservation</summary>
                    <div style="padding:18px;">
                        <p style="font-size:13px;color:#666;margin-bottom:12px;">
                            <strong>Politique :</strong> <?php echo esc_html( $property_obj->cancellation_policy ?? 'flexible' ); ?><br>
                            <strong>Remboursement automatique calculé :</strong> <?php echo esc_html( Nira_Admin::money( $auto_refund ) ); ?> sur <?php echo esc_html( Nira_Admin::money( $booking->amount_paid ) ); ?> payés.
                        </p>
                        <form method="post" onsubmit="return confirm('<?php esc_attr_e( 'Confirmer l\'annulation et le remboursement ?', 'nira-booking' ); ?>')">
                            <?php wp_nonce_field( 'nira_admin_cancel_booking' ); ?>
                            <input type="hidden" name="nira_action" value="cancel_booking">
                            <input type="hidden" name="id" value="<?php echo (int) $booking->id; ?>">
                            <p><label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Motif (optionnel)</label>
                                <input type="text" name="reason" class="regular-text" style="width:100%;"></p>
                            <p><label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Montant remboursé</label>
                                <input type="number" step="0.01" min="0" name="refund" placeholder="Vide = automatique selon politique" class="regular-text" style="width:100%;">
                                <span style="font-size:12px;color:#888;">Mettez <strong>0</strong> pour annuler sans remboursement.</span>
                            </p>
                            <p><button type="submit" class="button button-secondary" style="background:#A41C2B;color:#fff;border-color:#A41C2B;width:100%;">
                                Annuler la réservation
                            </button></p>
                        </form>
                    </div>
                </details>
            <?php else : ?>
                <p style="background:#f5f5f5;padding:14px 18px;border-radius:6px;color:#888;font-size:14px;">
                    Cette réservation est <strong><?php echo esc_html( $booking->status ); ?></strong>.
                    <?php if ( (float) $booking->amount_refunded > 0 ) : ?>
                        Remboursement effectué : <strong><?php echo esc_html( Nira_Admin::money( $booking->amount_refunded ) ); ?></strong>.
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <hr style="margin:24px 0 14px;">
            <form method="post" onsubmit="return confirm('<?php esc_attr_e( 'Supprimer définitivement cette réservation ? Action irréversible.', 'nira-booking' ); ?>')">
                <?php wp_nonce_field( 'nira_admin_delete_booking' ); ?>
                <input type="hidden" name="nira_action" value="delete_booking">
                <input type="hidden" name="id" value="<?php echo (int) $booking->id; ?>">
                <button type="submit" class="button-link-delete" style="color:#a00;font-size:12px;"><?php esc_html_e( 'Supprimer définitivement (sans remboursement)', 'nira-booking' ); ?></button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

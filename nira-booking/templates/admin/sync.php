<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1><?php esc_html_e( 'Synchronisation Airbnb (iCal)', 'nira-booking' ); ?></h1>

    <p><?php esc_html_e( "La synchronisation est bidirectionnelle : importez les réservations Airbnb pour bloquer vos dates, et exportez vos réservations directes vers Airbnb via l'URL ci-dessous.", 'nira-booking' ); ?></p>

    <div class="nira-two-col">
        <div class="nira-card">
            <h2><?php esc_html_e( 'Flux iCal entrants (import)', 'nira-booking' ); ?></h2>
            <?php if ( empty( $feeds ) ) : ?>
                <p><?php esc_html_e( 'Aucun flux configuré.', 'nira-booking' ); ?></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead><tr><th>Libellé</th><th>Hébergement</th><th>URL</th><th>Dernière synchro</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ( $feeds as $f ) :
                        $prop = Nira_Properties::instance()->get( $f->property_id );
                    ?>
                        <tr>
                            <td><strong><?php echo esc_html( $f->label ); ?></strong></td>
                            <td><?php echo esc_html( $prop->name ?? '—' ); ?></td>
                            <td><code style="max-width:300px;overflow:hidden;display:inline-block;text-overflow:ellipsis;white-space:nowrap;vertical-align:middle"><?php echo esc_html( $f->url ); ?></code></td>
                            <td><?php echo $f->last_synced ? esc_html( wp_date( 'd/m/Y H:i', strtotime( $f->last_synced ) ) ) : '—'; ?><br><small><?php echo (int) $f->last_events; ?> événements</small></td>
                            <td><span class="nira-pill <?php echo 'ok' === $f->last_status ? 'confirmed' : ( $f->last_status ? 'cancelled' : 'pending' ); ?>"><?php echo esc_html( $f->last_status ?: '—' ); ?></span></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <?php wp_nonce_field( 'nira_admin_sync_feed' ); ?>
                                    <input type="hidden" name="nira_action" value="sync_feed">
                                    <input type="hidden" name="id" value="<?php echo (int) $f->id; ?>">
                                    <button class="button button-small" type="submit"><?php esc_html_e( 'Synchro', 'nira-booking' ); ?></button>
                                </form>
                                <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ?')">
                                    <?php wp_nonce_field( 'nira_admin_delete_feed' ); ?>
                                    <input type="hidden" name="nira_action" value="delete_feed">
                                    <input type="hidden" name="id" value="<?php echo (int) $f->id; ?>">
                                    <button class="button button-small button-link-delete" style="color:#a00" type="submit">×</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="post" style="margin-top:15px">
                    <?php wp_nonce_field( 'nira_admin_sync_all' ); ?>
                    <input type="hidden" name="nira_action" value="sync_all">
                    <button class="button button-primary" type="submit"><?php esc_html_e( 'Tout synchroniser maintenant', 'nira-booking' ); ?></button>
                </form>
            <?php endif; ?>
        </div>

        <div class="nira-card">
            <h2><?php esc_html_e( 'Ajouter un flux', 'nira-booking' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Copiez l\'URL iCal depuis le tableau de bord Airbnb (Calendrier → Paramètres → Exporter le calendrier).', 'nira-booking' ); ?></p>
            <form method="post">
                <?php wp_nonce_field( 'nira_admin_save_feed' ); ?>
                <input type="hidden" name="nira_action" value="save_feed">
                <p><label><?php esc_html_e( 'Hébergement', 'nira-booking' ); ?><br>
                    <select name="property_id" required>
                        <?php foreach ( $properties as $p ) : ?>
                            <option value="<?php echo (int) $p->id; ?>"><?php echo esc_html( $p->name ); ?></option>
                        <?php endforeach; ?>
                    </select></label></p>
                <p><label><?php esc_html_e( 'Libellé', 'nira-booking' ); ?><br><input type="text" name="label" value="Airbnb" class="regular-text"></label></p>
                <p><label><?php esc_html_e( 'URL iCal', 'nira-booking' ); ?><br><input type="url" name="url" class="regular-text" required placeholder="https://www.airbnb.fr/calendar/ical/..."></label></p>
                <p><button class="button button-primary" type="submit"><?php esc_html_e( 'Ajouter', 'nira-booking' ); ?></button></p>
            </form>
        </div>
    </div>

    <div class="nira-card">
        <h2><?php esc_html_e( 'URLs à donner à Airbnb (export)', 'nira-booking' ); ?></h2>
        <p><?php esc_html_e( 'Copiez-collez ces URLs dans Airbnb (Calendrier → Paramètres → Importer le calendrier) pour qu\'Airbnb bloque automatiquement les dates de vos réservations directes.', 'nira-booking' ); ?></p>
        <table class="widefat">
            <thead><tr><th>Hébergement</th><th>URL à importer dans Airbnb</th></tr></thead>
            <tbody>
                <?php foreach ( $properties as $p ) : $url = rest_url( 'nira/v1/ical/' . $p->slug . '.ics' ); ?>
                    <tr>
                        <td><?php echo esc_html( $p->name ); ?></td>
                        <td><code><?php echo esc_html( $url ); ?></code>
                            <button type="button" class="button button-small" onclick="navigator.clipboard.writeText('<?php echo esc_js( $url ); ?>').then(()=>this.textContent='Copié')"><?php esc_html_e( 'Copier', 'nira-booking' ); ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

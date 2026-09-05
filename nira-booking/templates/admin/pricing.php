<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1><?php esc_html_e( 'Tarifs par période', 'nira-booking' ); ?></h1>

    <form method="get" class="nira-filters">
        <input type="hidden" name="page" value="nira-pricing">
        <select name="property_id" onchange="this.form.submit()">
            <?php foreach ( $properties as $p ) : ?>
                <option value="<?php echo (int) $p->id; ?>" <?php selected( $pid, $p->id ); ?>><?php echo esc_html( $p->name ); ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ( $property ) : ?>
    <div class="nira-two-col">
        <div class="nira-card">
            <h2><?php esc_html_e( 'Périodes tarifaires', 'nira-booking' ); ?></h2>
            <p class="description">
                <?php printf(
                    wp_kses_post( __( 'Prix par défaut : <strong>%s</strong> par nuit (modifiable dans la fiche de l\'hébergement). Toutes les dates en dehors des périodes ci-dessous utilisent ce prix.', 'nira-booking' ) ),
                    esc_html( Nira_Admin::money( $property->base_price ) )
                ); ?>
            </p>

            <?php
            // On n'affiche que les règles de type "saison" (plage de dates).
            $seasons = array_values( array_filter( $rules, function ( $r ) { return 'season' === $r->rule_type; } ) );
            usort( $seasons, function ( $a, $b ) { return strcmp( (string) $a->date_from, (string) $b->date_from ); } );
            ?>

            <?php if ( empty( $seasons ) ) : ?>
                <p><em><?php esc_html_e( 'Aucune période définie. Utilisez le formulaire ci-contre pour ajouter une haute saison, Noël, vacances, etc.', 'nira-booking' ); ?></em></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead><tr>
                        <th><?php esc_html_e( 'Libellé', 'nira-booking' ); ?></th>
                        <th><?php esc_html_e( 'Du', 'nira-booking' ); ?></th>
                        <th><?php esc_html_e( 'Au', 'nira-booking' ); ?></th>
                        <th><?php esc_html_e( 'Prix / nuit', 'nira-booking' ); ?></th>
                        <th><?php esc_html_e( 'Séjour min.', 'nira-booking' ); ?></th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ( $seasons as $r ) : ?>
                        <tr>
                            <td><strong><?php echo esc_html( $r->label ); ?></strong></td>
                            <td><?php echo esc_html( Nira_Admin::fr_date( $r->date_from ) ); ?></td>
                            <td><?php echo esc_html( Nira_Admin::fr_date( $r->date_to ) ); ?></td>
                            <td><strong><?php echo esc_html( Nira_Admin::money( $r->price ) ); ?></strong></td>
                            <td><?php echo ( null === $r->min_nights || '' === $r->min_nights ) ? '—' : (int) $r->min_nights . ' nuits'; ?></td>
                            <td>
                                <a class="button button-small" href="<?php echo esc_url( add_query_arg( [ 'edit' => (int) $r->id, 'property_id' => $pid ] ) ); ?>"><?php esc_html_e( 'Modifier', 'nira-booking' ); ?></a>
                                <form method="post" style="display:inline" onsubmit="return confirm('<?php esc_attr_e( 'Supprimer cette période ?', 'nira-booking' ); ?>')">
                                    <?php wp_nonce_field( 'nira_admin_delete_pricing' ); ?>
                                    <input type="hidden" name="nira_action" value="delete_pricing">
                                    <input type="hidden" name="id" value="<?php echo (int) $r->id; ?>">
                                    <input type="hidden" name="property_id" value="<?php echo (int) $pid; ?>">
                                    <button class="button button-small button-link-delete" type="submit" style="color:#a00"><?php esc_html_e( 'Suppr.', 'nira-booking' ); ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="description" style="margin-top:14px">
                    <?php esc_html_e( 'En cas de chevauchement entre deux périodes, celle créée en dernier prévaut.', 'nira-booking' ); ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="nira-card">
            <h2><?php echo $edit_rule ? esc_html__( 'Modifier la période', 'nira-booking' ) : esc_html__( 'Ajouter une période', 'nira-booking' ); ?></h2>
            <form method="post">
                <?php wp_nonce_field( 'nira_admin_save_pricing' ); ?>
                <input type="hidden" name="nira_action" value="save_pricing">
                <input type="hidden" name="property_id" value="<?php echo (int) $pid; ?>">
                <input type="hidden" name="rule_type" value="season">
                <input type="hidden" name="priority" value="10">
                <?php if ( $edit_rule ) : ?><input type="hidden" name="id" value="<?php echo (int) $edit_rule->id; ?>"><?php endif; ?>

                <p><label>
                    <strong><?php esc_html_e( 'Libellé', 'nira-booking' ); ?></strong><br>
                    <input type="text" name="label" value="<?php echo esc_attr( $edit_rule->label ?? '' ); ?>" class="regular-text" placeholder="Ex : Haute saison, Noël, Vacances de Pâques…" required>
                </label></p>

                <p>
                    <label style="display:inline-block;margin-right:16px">
                        <strong><?php esc_html_e( 'Du', 'nira-booking' ); ?></strong><br>
                        <input type="date" name="date_from" value="<?php echo esc_attr( $edit_rule->date_from ?? '' ); ?>" required>
                    </label>
                    <label style="display:inline-block">
                        <strong><?php esc_html_e( 'Au', 'nira-booking' ); ?></strong><br>
                        <input type="date" name="date_to" value="<?php echo esc_attr( $edit_rule->date_to ?? '' ); ?>" required>
                    </label>
                </p>

                <p><label>
                    <strong><?php esc_html_e( 'Prix par nuit', 'nira-booking' ); ?></strong><br>
                    <input type="number" step="0.01" min="0" name="price" value="<?php echo esc_attr( $edit_rule->price ?? '' ); ?>" required style="width:140px">
                    &nbsp;<span class="description">€ / nuit</span>
                </label></p>

                <p><label>
                    <strong><?php esc_html_e( 'Séjour minimum', 'nira-booking' ); ?></strong>
                    <span class="description">(optionnel)</span><br>
                    <input type="number" min="1" name="min_nights" value="<?php echo esc_attr( $edit_rule->min_nights ?? '' ); ?>" style="width:100px" placeholder="1">
                    &nbsp;<span class="description">nuits minimum pendant cette période</span>
                </label></p>

                <p style="margin-top:20px">
                    <button class="button button-primary button-large" type="submit">
                        <?php echo $edit_rule ? esc_html__( 'Mettre à jour', 'nira-booking' ) : esc_html__( 'Ajouter la période', 'nira-booking' ); ?>
                    </button>
                    <?php if ( $edit_rule ) : ?>
                        &nbsp;<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=nira-pricing&property_id=' . (int) $pid ) ); ?>"><?php esc_html_e( 'Annuler', 'nira-booking' ); ?></a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

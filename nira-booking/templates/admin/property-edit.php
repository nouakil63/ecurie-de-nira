<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap nira-wrap">
    <h1><?php echo $property ? esc_html__( 'Modifier — ', 'nira-booking' ) . esc_html( $property->name ) : esc_html__( 'Nouvel hébergement', 'nira-booking' ); ?></h1>
    <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=nira-properties' ) ); ?>">← <?php esc_html_e( 'Retour', 'nira-booking' ); ?></a></p>

    <form method="post" class="nira-card">
        <?php wp_nonce_field( 'nira_admin_save_property' ); ?>
        <input type="hidden" name="nira_action" value="save_property">
        <?php if ( $property ) : ?><input type="hidden" name="id" value="<?php echo (int) $property->id; ?>"><?php endif; ?>

        <h2><?php esc_html_e( 'Informations', 'nira-booking' ); ?></h2>
        <table class="form-table">
            <tr><th><label><?php esc_html_e( 'Nom', 'nira-booking' ); ?></label></th>
                <td><input type="text" name="name" value="<?php echo esc_attr( $property->name ?? '' ); ?>" class="regular-text" required></td></tr>
            <tr><th><label>Slug</label></th>
                <td><input type="text" name="slug" value="<?php echo esc_attr( $property->slug ?? '' ); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e( 'Identifiant URL utilisé par le shortcode. Laisser vide pour générer automatiquement.', 'nira-booking' ); ?></p></td></tr>
            <tr><th><label><?php esc_html_e( 'Description', 'nira-booking' ); ?></label></th>
                <td><textarea name="description" rows="3" class="large-text"><?php echo esc_textarea( $property->description ?? '' ); ?></textarea></td></tr>
            <tr><th><label><?php esc_html_e( 'Équipements (un par ligne)', 'nira-booking' ); ?></label></th>
                <td><textarea name="amenities" rows="6" class="large-text"><?php echo esc_textarea( $property->amenities ?? '' ); ?></textarea></td></tr>
            <tr><th><label><?php esc_html_e( 'URL Airbnb', 'nira-booking' ); ?></label></th>
                <td><input type="url" name="airbnb_url" value="<?php echo esc_attr( $property->airbnb_url ?? '' ); ?>" class="regular-text"></td></tr>
            <tr><th><label><?php esc_html_e( 'Image de couverture (URL ou chemin)', 'nira-booking' ); ?></label></th>
                <td><input type="text" name="cover_image" value="<?php echo esc_attr( $property->cover_image ?? '' ); ?>" class="regular-text"></td></tr>
            <tr><th><label><?php esc_html_e( 'Statut', 'nira-booking' ); ?></label></th>
                <td>
                    <select name="status">
                        <option value="active"   <?php selected( $property->status ?? 'active', 'active' ); ?>>Actif</option>
                        <option value="inactive" <?php selected( $property->status ?? '', 'inactive' ); ?>>Inactif</option>
                    </select>
                </td></tr>
            <tr><th><?php esc_html_e( 'Ordre', 'nira-booking' ); ?></th>
                <td><input type="number" name="sort_order" value="<?php echo (int) ( $property->sort_order ?? 0 ); ?>" min="0"></td></tr>
        </table>

        <h2><?php esc_html_e( 'Capacité & séjour', 'nira-booking' ); ?></h2>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Capacité (personnes)', 'nira-booking' ); ?></th>
                <td><input type="number" name="capacity" min="1" value="<?php echo (int) ( $property->capacity ?? 2 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Chambres', 'nira-booking' ); ?></th>
                <td><input type="number" name="bedrooms" min="0" value="<?php echo (int) ( $property->bedrooms ?? 1 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Salles de bain', 'nira-booking' ); ?></th>
                <td><input type="number" name="bathrooms" min="0" value="<?php echo (int) ( $property->bathrooms ?? 1 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Séjour minimum (nuits)', 'nira-booking' ); ?></th>
                <td><input type="number" name="min_nights" min="1" value="<?php echo (int) ( $property->min_nights ?? 2 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Séjour maximum (nuits)', 'nira-booking' ); ?></th>
                <td><input type="number" name="max_nights" min="1" value="<?php echo (int) ( $property->max_nights ?? 30 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( "Heure d'arrivée", 'nira-booking' ); ?></th>
                <td><input type="time" name="checkin_time" value="<?php echo esc_attr( $property->checkin_time ?? '16:00' ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Heure de départ', 'nira-booking' ); ?></th>
                <td><input type="time" name="checkout_time" value="<?php echo esc_attr( $property->checkout_time ?? '11:00' ); ?>"></td></tr>
        </table>

        <h2><?php esc_html_e( 'Tarification', 'nira-booking' ); ?></h2>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Prix de base / nuit', 'nira-booking' ); ?></th>
                <td><input type="number" step="0.01" min="0" name="base_price" value="<?php echo esc_attr( $property->base_price ?? 100 ); ?>">
                    <p class="description"><?php esc_html_e( 'Utilisé lorsque aucune règle saisonnière ne s\'applique.', 'nira-booking' ); ?></p></td></tr>
            <tr><th><?php esc_html_e( 'Frais de ménage', 'nira-booking' ); ?></th>
                <td><input type="number" step="0.01" min="0" name="cleaning_fee" value="<?php echo esc_attr( $property->cleaning_fee ?? 50 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( "Acompte (% du total)", 'nira-booking' ); ?></th>
                <td><input type="number" name="deposit_pct" min="0" max="100" value="<?php echo (int) ( $property->deposit_pct ?? 30 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( "Politique d'annulation", 'nira-booking' ); ?></th>
                <td>
                    <select name="cancellation_policy">
                        <?php foreach ( [ 'flexible' => 'Flexible — 100% jusqu\'à J-1', 'moderate' => 'Modérée — 100% jusqu\'à J-5, puis 50%', 'strict' => 'Stricte — 50% jusqu\'à J-7, puis 0%' ] as $val => $lbl ) : ?>
                            <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $property->cancellation_policy ?? 'flexible', $val ); ?>><?php echo esc_html( $lbl ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td></tr>
        </table>

        <h2><?php esc_html_e( 'Remises long séjour (style Airbnb)', 'nira-booking' ); ?></h2>
        <p class="description" style="margin-bottom:14px;">
            <?php esc_html_e( "Reproduisent les réductions « À la semaine » et « Au mois » d'Airbnb. Mettez 0 pour désactiver.", 'nira-booking' ); ?>
        </p>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Remise à la semaine', 'nira-booking' ); ?></th>
                <td>
                    <input type="number" min="0" max="100" name="weekly_discount_pct" value="<?php echo (int) ( $property->weekly_discount_pct ?? 0 ); ?>" style="width:80px;"> %
                    <?php esc_html_e( 'à partir de', 'nira-booking' ); ?>
                    <input type="number" min="2" max="60" name="weekly_threshold_nights" value="<?php echo (int) ( $property->weekly_threshold_nights ?? 7 ); ?>" style="width:60px;"> <?php esc_html_e( 'nuits', 'nira-booking' ); ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Remise au mois', 'nira-booking' ); ?></th>
                <td>
                    <input type="number" min="0" max="100" name="monthly_discount_pct" value="<?php echo (int) ( $property->monthly_discount_pct ?? 0 ); ?>" style="width:80px;"> %
                    <?php esc_html_e( 'à partir de', 'nira-booking' ); ?>
                    <input type="number" min="7" max="120" name="monthly_threshold_nights" value="<?php echo (int) ( $property->monthly_threshold_nights ?? 28 ); ?>" style="width:60px;"> <?php esc_html_e( 'nuits', 'nira-booking' ); ?>
                </td>
            </tr>
        </table>

        <p>
            <button class="button button-primary" type="submit"><?php esc_html_e( 'Enregistrer', 'nira-booking' ); ?></button>
            <?php if ( $property ) : ?>
                &nbsp;<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=nira-pricing&property_id=' . (int) $property->id ) ); ?>"><?php esc_html_e( 'Règles tarifaires', 'nira-booking' ); ?></a>
            <?php endif; ?>
        </p>
    </form>

    <?php if ( $property ) : ?>
    <form method="post" class="nira-card" onsubmit="return confirm('<?php esc_attr_e( 'Supprimer cet hébergement ?', 'nira-booking' ); ?>')">
        <?php wp_nonce_field( 'nira_admin_delete_property' ); ?>
        <input type="hidden" name="nira_action" value="delete_property">
        <input type="hidden" name="id" value="<?php echo (int) $property->id; ?>">
        <p><button class="button-link-delete" style="color:#a00"><?php esc_html_e( 'Supprimer cet hébergement', 'nira-booking' ); ?></button></p>
    </form>
    <?php endif; ?>
</div>

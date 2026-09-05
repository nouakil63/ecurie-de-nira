<?php
/**
 * Template Name: Gîtes & Séjours (Nira)
 *
 * Template de page livré par le plugin Nira Booking. Enregistré
 * dynamiquement via nira-booking.php (filtre theme_page_templates).
 *
 * Les images sont chargées depuis nira-booking/assets/img/.
 * Tu peux surcharger une image en définissant une option WP du même
 * nom (ex : nira_gites_hero_bg) avec l'URL de la médiathèque, depuis
 * Nira Booking > Images des pages > onglet "Gîtes & Séjours".
 *
 * Galeries : Appartement = appt_cover + appt_1..4 ; Duplex = duplex_cover
 * + duplex_1..4. Les photos du Duplex sont donc indépendantes de celles
 * de l'Appartement (tant qu'elles ne sont pas configurées, elles reprennent
 * par défaut les mêmes fichiers, d'où un rendu identique à l'existant).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Base d'images : dossier du plugin (fallback) + overrides via options WP
$IMG_BASE = NIRA_BOOKING_URL . 'assets/img';
function nira_gites_img( $key, $default_filename, $base ) {
    $override = get_option( 'nira_gites_' . $key );
    if ( ! empty( $override ) ) return $override;
    return $base . '/' . $default_filename;
}
$IMG = [
    'hero_bg'     => nira_gites_img( 'hero_bg',     'IMG_2918-scaled.jpeg',   $IMG_BASE ),
    'logo_white'  => nira_gites_img( 'logo_white',  'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'   => nira_gites_img( 'logo_dark',   'logo-OK-VECTO.png',      $IMG_BASE ),
    'appt_cover'  => nira_gites_img( 'appt_cover',  'IMG_3093-scaled.jpeg',   $IMG_BASE ),
    'appt_1'      => nira_gites_img( 'appt_1',      'IMG_3092-1-scaled.jpeg', $IMG_BASE ),
    'appt_2'      => nira_gites_img( 'appt_2',      'IMG_3091-scaled.jpeg',   $IMG_BASE ),
    'appt_3'      => nira_gites_img( 'appt_3',      'IMG_3090-scaled.jpeg',   $IMG_BASE ),
    'appt_4'      => nira_gites_img( 'appt_4',      'IMG_3088-scaled.jpeg',   $IMG_BASE ),
    'duplex_cover'=> nira_gites_img( 'duplex_cover','IMG_3089-2048x1536.jpeg',$IMG_BASE ),
    'duplex_1'    => nira_gites_img( 'duplex_1',    'IMG_3092-1-scaled.jpeg', $IMG_BASE ),
    'duplex_2'    => nira_gites_img( 'duplex_2',    'IMG_3091-scaled.jpeg',   $IMG_BASE ),
    'duplex_3'    => nira_gites_img( 'duplex_3',    'IMG_3090-scaled.jpeg',   $IMG_BASE ),
    'duplex_4'    => nira_gites_img( 'duplex_4',    'IMG_3088-scaled.jpeg',   $IMG_BASE ),
    'partner_1'   => nira_gites_img( 'partner_1',   'macaron-engagement-1-150x150-1.png', $IMG_BASE ),
    'partner_2'   => nira_gites_img( 'partner_2',   'region-normandie.png',     $IMG_BASE ),
    'partner_3'   => nira_gites_img( 'partner_3',   'union-europeenne.png',     $IMG_BASE ),
];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( get_the_title() ); ?> | <?php bloginfo( 'name' ); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bordeaux: #A41C2B;
            --anthracite: #2D2D2D;
            --sand: #FDFBF9;
            --white: #FFFFFF;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background-color: var(--sand); color: var(--anthracite); overflow-x: hidden; line-height: 1.6; }
        h1, h2, h3 { font-family: 'Playfair Display', serif; font-weight: 700; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }

        /* HEADER */
        header.site-header { position: fixed; top: 0; width: 100%; z-index: 1000; padding: 30px 4%; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0)); transition: var(--transition); }
        header.site-header.scrolled { padding: 15px 4%; background: rgba(253, 251, 249, 0.98); backdrop-filter: blur(10px); box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .logo img { height: 60px; transition: var(--transition); }
        header.site-header.scrolled .logo img { height: 50px; }
        .header-nav-container { display: flex; align-items: center; gap: 40px; }
        nav ul { display: flex; list-style: none; gap: 25px; align-items: center; }
        nav a { color: var(--white); font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; transition: var(--transition); }
        header.site-header.scrolled nav a { color: var(--anthracite); }
        nav a:hover { color: #ccc; }
        header.site-header.scrolled nav a:hover { color: var(--bordeaux); }
        .header-social { display: flex; gap: 12px; align-items: center; }
        .social-circle { background-color: var(--white); color: #000; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: var(--transition); }
        header.site-header.scrolled .social-circle { background-color: var(--anthracite); color: var(--white); }
        .social-circle:hover { transform: scale(1.1); }
        header.site-header.scrolled .social-circle:hover { background-color: var(--bordeaux); }

        /* HERO POSTCARD STYLE */
        .gites-hero {
            background-color: var(--white);
            padding: 160px 5% 80px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .gites-hero-coords {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            font-family: 'Inter', sans-serif;
            font-size: 0.72rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 5px;
            color: var(--bordeaux);
            margin-bottom: 22px;
        }
        .gites-hero-coords::before,
        .gites-hero-coords::after {
            content: "";
            width: 50px;
            height: 1px;
            background: rgba(164, 28, 43, 0.4);
        }
        .gites-hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 7vw, 6rem);
            line-height: 1;
            color: var(--anthracite);
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.01em;
        }
        .gites-hero-title em {
            font-style: italic;
            font-weight: 400;
            color: var(--bordeaux);
        }
        .gites-hero-tagline {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.2rem, 2vw, 1.5rem);
            font-style: italic;
            color: #777;
            margin-bottom: 24px;
        }
        .gites-hero-desc {
            font-size: 1.05rem;
            line-height: 1.85;
            color: #666;
            font-weight: 300;
            max-width: 720px;
            margin: 0 auto 50px;
        }
        .gites-hero-image-stage {
            position: relative;
            max-width: 1300px;
            margin: 0 auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 40px 90px rgba(0,0,0,0.15);
            aspect-ratio: 16 / 7;
        }
        .gites-hero-image-stage img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 1.5s ease;
        }
        .gites-hero-image-stage:hover img { transform: scale(1.04); }
        .gites-hero-image-stage::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.45));
            pointer-events: none;
        }
        .gites-hero-pin {
            position: absolute;
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 14px 22px;
            border-radius: 100px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 12px 35px rgba(0,0,0,0.18);
            z-index: 2;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }
        .gites-hero-pin i { font-size: 1.05rem; color: var(--bordeaux); }
        .gites-hero-pin strong { font-weight: 700; color: var(--anthracite); font-size: 0.9rem; }
        .gites-hero-pin span { color: #888; font-size: 0.78rem; }
        .gites-hero-pin--tl { top: 30px; left: 30px; }
        .gites-hero-pin--tr { top: 30px; right: 30px; }
        .gites-hero-pin--bc { bottom: 30px; left: 50%; transform: translateX(-50%); }
        @media (max-width: 992px) {
            .gites-hero { padding: 130px 5% 60px; }
            .gites-hero-image-stage { aspect-ratio: 4 / 3; }
            .gites-hero-pin { padding: 10px 16px; gap: 8px; }
            .gites-hero-pin strong { font-size: 0.78rem; }
            .gites-hero-pin span { display: none; }
        }
        @media (max-width: 600px) {
            .gites-hero-pin--tl { top: 14px; left: 14px; }
            .gites-hero-pin--tr { top: 14px; right: 14px; }
            .gites-hero-pin--bc { bottom: 14px; }
            .gites-hero-coords::before, .gites-hero-coords::after { width: 25px; }
            .gites-hero-coords { letter-spacing: 3px; gap: 8px; }
        }

        /* SELECTION */
        .gites-selection { padding: 80px 5% 40px; max-width: 1200px; margin: 0 auto; }
        .section-intro { text-align: center; margin-bottom: 50px; }
        .section-intro h2 { font-size: 2.5rem; color: var(--anthracite); margin-bottom: 15px; }
        .section-intro p { color: #666; max-width: 600px; margin: 0 auto; }

        .cards-container { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .room-card { background: var(--white); border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); cursor: pointer; transition: var(--transition); border: 2px solid transparent; }
        .room-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .room-card.active { border-color: var(--bordeaux); }

        .room-img-wrapper { height: 280px; overflow: hidden; }
        .room-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease; }
        .room-card:hover .room-img-wrapper img { transform: scale(1.05); }

        .room-info { padding: 25px; text-align: center; }
        .room-info h3 { font-size: 1.8rem; color: var(--anthracite); margin-bottom: 5px; }
        .room-info p { font-size: 0.85rem; color: #888; text-transform: uppercase; letter-spacing: 2px; }

        /* DETAILS */
        .room-details-container { max-width: 1200px; margin: 0 auto 100px; padding: 0 5%; }
        .room-detail { display: none; opacity: 0; background: var(--white); border-radius: 12px; padding: 40px; box-shadow: 0 15px 50px rgba(0,0,0,0.05); transition: opacity 0.5s ease; }
        .room-detail.active { display: block; opacity: 1; animation: slideUp 0.5s ease; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .airbnb-gallery { display: grid; grid-template-columns: 2fr 1fr 1fr; grid-template-rows: 200px 200px; gap: 10px; border-radius: 12px; overflow: hidden; margin-bottom: 40px; }
        .airbnb-gallery img { width: 100%; height: 100%; object-fit: cover; cursor: pointer; transition: filter 0.3s ease; }
        .airbnb-gallery img:hover { filter: brightness(0.85); }
        .gallery-main { grid-column: 1 / 2; grid-row: 1 / 3; }

        .detail-content { display: grid; grid-template-columns: 2fr 1fr; gap: 50px; }
        .detail-text h3 { font-size: 2rem; color: var(--anthracite); margin-bottom: 10px; }
        .detail-meta { font-size: 0.95rem; color: var(--bordeaux); font-weight: 600; margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid #EEE; }
        .detail-desc { color: #555; line-height: 1.8; margin-bottom: 30px; font-weight: 300; }

        .amenities-title { font-size: 1.2rem; margin-bottom: 20px; font-weight: 600; }
        .amenities-list { list-style: none; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
        .amenities-list li { display: flex; align-items: center; gap: 12px; font-size: 0.95rem; color: var(--anthracite); }
        .amenities-list i { color: var(--bordeaux); width: 20px; text-align: center; font-size: 1.1rem; }

        .warning-text { background: rgba(164, 28, 43, 0.05); padding: 15px; border-left: 3px solid var(--bordeaux); font-size: 0.85rem; color: var(--anthracite); }

        /* Carte de réservation — conteneur du widget Nira */
        .booking-card { background: transparent; border: none; padding: 0; box-shadow: none; position: sticky; top: 120px; }
        .booking-card .nira-booking-card { position: relative; }
        .btn-airbnb { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 15px; background-color: #FF5A5F; color: var(--white); border-radius: 8px; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; border: 2px solid #FF5A5F; cursor: pointer; transition: var(--transition); margin-top: 15px; }
        .btn-airbnb:hover { background-color: transparent; color: #FF5A5F; }

        /* LIGHTBOX */
        #lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 2000; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
        #lightbox.active { opacity: 1; pointer-events: auto; }
        #lightbox img { max-width: 90%; max-height: 90vh; border-radius: 4px; box-shadow: 0 0 50px rgba(0,0,0,0.5); }
        #lightbox-close { position: absolute; top: 30px; right: 40px; color: white; font-size: 2rem; cursor: pointer; }

        /* FOOTER */
        footer.site-footer { background-color: var(--white); padding-top: 40px; border-top: 1px solid rgba(0,0,0,0.05); }
        .footer-link { transition: color 0.3s ease; }
        .footer-link:hover { color: var(--bordeaux); }
        .social-icon { width: 40px; height: 40px; border-radius: 50%; background-color: var(--anthracite); color: var(--white); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: var(--transition); }
        .social-icon:hover { background-color: var(--bordeaux); transform: translateY(-3px); }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .detail-content { grid-template-columns: 1fr; }
            .booking-card { position: relative; top: 0; }
        }
        @media (max-width: 768px) {
            .cards-container { grid-template-columns: 1fr; }
            .airbnb-gallery { grid-template-columns: 1fr; grid-template-rows: auto; height: auto; }
            .gallery-main { grid-column: span 1; grid-row: span 1; height: 250px; }
            .airbnb-gallery img:not(.gallery-main) { display: none; }
            .amenities-list { grid-template-columns: 1fr; }
            nav ul { display: none; }
            header.site-header.scrolled { padding: 10px 5%; }
            .room-detail { padding: 20px; }
        }
    </style>

    <?php wp_head(); /* charge le CSS/JS du plugin Nira */ ?>
</head>
<body <?php body_class(); ?>>

    <div id="lightbox" onclick="closeLightbox()">
        <i class="fa-solid fa-xmark" id="lightbox-close"></i>
        <img id="lightbox-img" src="" alt="Aperçu plein écran">
    </div>

    <header id="header" class="site-header">
        <div class="logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img id="logoImg" src="<?php echo esc_url( $IMG['logo_white'] ); ?>" alt="<?php bloginfo( 'name' ); ?>"></a>
        </div>
        <div class="header-nav-container">
            <nav>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Présentation</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/infrastructures' ) ); ?>">Infrastructures</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/debourrage' ) ); ?>">Débourrage et valorisation</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/balneotherapie' ) ); ?>">Balnéothérapie</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/pension-et-tarifs' ) ); ?>">Pension et tarifs</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/gites' ) ); ?>">Gîtes</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact</a></li>
                </ul>
            </nav>
            <div class="header-social">
                <a href="https://www.facebook.com/profile.php?id=100088742455431" target="_blank" rel="noopener" aria-label="Facebook" class="social-circle"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/ecurie_de_nira/" target="_blank" rel="noopener" aria-label="Instagram" class="social-circle"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>
    </header>

    <section class="gites-hero">
        <div class="gites-hero-coords" data-aos="fade-down">
            Deauville · Normandie · 49.36° N
        </div>
        <h1 class="gites-hero-title" data-aos="fade-up">
            Séjourner <em>au cœur des écuries.</em>
        </h1>
        <p class="gites-hero-tagline" data-aos="fade-up" data-aos-delay="100">Une parenthèse entre paddocks et plage</p>
        <p class="gites-hero-desc" data-aos="fade-up" data-aos-delay="200">
            Nos appartements, nichés au-dessus des écuries, accueillent cavaliers en compétition, propriétaires passionnés et amoureux de la Normandie. À 3 kilomètres de la plage de Deauville et 500 m du Pôle International du Cheval.
        </p>

        <div class="gites-hero-image-stage" data-aos="zoom-in" data-aos-delay="300">
            <img src="<?php echo esc_url( $IMG['hero_bg'] ); ?>" alt="Gîtes équestres au-dessus des écuries de Nira">
            <div class="gites-hero-pin gites-hero-pin--tl" data-aos="fade-down-right" data-aos-delay="600">
                <i class="fa-solid fa-umbrella-beach"></i>
                <div><strong>Plage de Deauville</strong> <span>· 5 min en voiture</span></div>
            </div>
            <div class="gites-hero-pin gites-hero-pin--tr" data-aos="fade-down-left" data-aos-delay="700">
                <i class="fa-solid fa-horse"></i>
                <div><strong>Avec votre cheval</strong> <span>· box & paddock</span></div>
            </div>
            <div class="gites-hero-pin gites-hero-pin--bc" data-aos="fade-up" data-aos-delay="800">
                <i class="fa-solid fa-location-dot"></i>
                <div><strong>Bonneville-sur-Touques</strong> <span>· 14800</span></div>
            </div>
        </div>
    </section>

    <section class="gites-selection" id="selection-gites">
        <div class="section-intro" data-aos="fade-up">
            <h2>Nos Hébergements</h2>
            <p>Sélectionnez un hébergement ci-dessous pour découvrir ses équipements, sa galerie photo et réserver votre séjour.</p>
        </div>

        <div class="cards-container" data-aos="fade-up" data-aos-delay="100">
            <div class="room-card active" id="card-appartement" onclick="showRoom('appartement')">
                <div class="room-img-wrapper"><img src="<?php echo esc_url( $IMG['appt_cover'] ); ?>" alt="L'Appartement"></div>
                <div class="room-info">
                    <h3>L'Appartement</h3>
                    <p>7 personnes • 4 Chambres</p>
                </div>
            </div>

            <div class="room-card" id="card-duplex" onclick="showRoom('duplex')">
                <div class="room-img-wrapper"><img src="<?php echo esc_url( $IMG['duplex_cover'] ); ?>" alt="Le Duplex"></div>
                <div class="room-info">
                    <h3>Le Duplex</h3>
                    <p>4 personnes • 2 Chambres</p>
                </div>
            </div>
        </div>
    </section>

    <section class="room-details-container" id="room-details-section">

        <!-- APPARTEMENT -->
        <div class="room-detail active" id="detail-appartement">
            <div class="airbnb-gallery">
                <img src="<?php echo esc_url( $IMG['appt_cover'] ); ?>" alt="Salon Appartement" class="gallery-main" onclick="openLightbox(this.src)">
                <img src="<?php echo esc_url( $IMG['appt_1'] ); ?>" alt="Chambre" onclick="openLightbox(this.src)">
                <img src="<?php echo esc_url( $IMG['appt_2'] ); ?>" alt="Chambre 2" onclick="openLightbox(this.src)">
                <img src="<?php echo esc_url( $IMG['appt_3'] ); ?>" alt="Salle de bain" onclick="openLightbox(this.src)">
                <img src="<?php echo esc_url( $IMG['appt_4'] ); ?>" alt="Cuisine" onclick="openLightbox(this.src)">
            </div>

            <div class="detail-content">
                <div class="detail-text">
                    <h3>L'Appartement</h3>
                    <div class="detail-meta">7 Personnes • 4 Chambres • 1 Salle de bain</div>
                    <p class="detail-desc">Idéal pour les séjours en famille ou entre amis, cet espace chaleureux vous offre une vue imprenable sur le domaine. Profitez de l'indépendance d'un appartement spacieux et tout équipé tout en respirant l'atmosphère paisible de nos écuries.</p>

                    <h4 class="amenities-title">Équipements</h4>
                    <ul class="amenities-list">
                        <li><i class="fa-solid fa-bed"></i> 3 Lits doubles, 1 lit simple</li>
                        <li><i class="fa-solid fa-kitchen-set"></i> Cuisine entièrement équipée</li>
                        <li><i class="fa-solid fa-tv"></i> Télévision & Espace salon</li>
                        <li><i class="fa-solid fa-car"></i> Parking privé avec caméra</li>
                        <li><i class="fa-solid fa-paw"></i> Animaux de compagnie acceptés</li>
                        <li><i class="fa-solid fa-horse-head"></i> Boxes/paddocks en option</li>
                    </ul>

                    <p class="warning-text"><i class="fa-solid fa-circle-info" style="margin-right: 5px;"></i> Draps et couvertures fournis. Attention, les serviettes de toilette ne sont pas incluses.</p>
                </div>

                <div>
                    <div class="booking-card">
                        <?php echo do_shortcode( '[nira_booking slug="appartement"]' ); ?>
                        <?php
                        $appt_property = class_exists( 'Nira_Properties' ) ? Nira_Properties::instance()->get_by_slug( 'appartement' ) : null;
                        $appt_airbnb   = ! empty( $appt_property->airbnb_url ) ? $appt_property->airbnb_url : '';
                        if ( $appt_airbnb ) : ?>
                            <a href="<?php echo esc_url( $appt_airbnb ); ?>" target="_blank" rel="noopener" class="btn-airbnb">
                                <i class="fa-brands fa-airbnb"></i> Voir sur Airbnb
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- DUPLEX -->
        <div class="room-detail" id="detail-duplex">
            <div class="airbnb-gallery">
                <img src="<?php echo esc_url( $IMG['duplex_cover'] ); ?>" alt="Salon Duplex" class="gallery-main" onclick="openLightbox(this.src)">
                <img src="<?php echo esc_url( $IMG['duplex_1'] ); ?>" alt="Chambre" onclick="openLightbox(this.src)">
                <img src="<?php echo esc_url( $IMG['duplex_2'] ); ?>" alt="Chambre 2" onclick="openLightbox(this.src)">
                <img src="<?php echo esc_url( $IMG['duplex_3'] ); ?>" alt="Salle de bain" onclick="openLightbox(this.src)">
                <img src="<?php echo esc_url( $IMG['duplex_4'] ); ?>" alt="Cuisine" onclick="openLightbox(this.src)">
            </div>

            <div class="detail-content">
                <div class="detail-text">
                    <h3>Le Duplex</h3>
                    <div class="detail-meta">4 Personnes • 2 Chambres • 1 Salle de bain</div>
                    <p class="detail-desc">Un cocon intimiste sous les toits, parfait pour un couple de cavaliers ou une petite famille. Son agencement en duplex lui confère un charme atypique, alliant le cachet de la charpente apparente aux standards de confort modernes.</p>

                    <h4 class="amenities-title">Équipements</h4>
                    <ul class="amenities-list">
                        <li><i class="fa-solid fa-bed"></i> 2 Lits doubles</li>
                        <li><i class="fa-solid fa-kitchen-set"></i> Cuisine équipée ouverte</li>
                        <li><i class="fa-solid fa-tv"></i> Télévision & Coin salon</li>
                        <li><i class="fa-solid fa-car"></i> Parking privé avec caméra</li>
                        <li><i class="fa-solid fa-paw"></i> Animaux de compagnie acceptés</li>
                        <li><i class="fa-solid fa-horse-head"></i> Boxes/paddocks en option</li>
                    </ul>

                    <p class="warning-text"><i class="fa-solid fa-circle-info" style="margin-right: 5px;"></i> Draps et couvertures fournis. Attention, les serviettes de toilette ne sont pas incluses.</p>
                </div>

                <div>
                    <div class="booking-card">
                        <?php echo do_shortcode( '[nira_booking slug="duplex"]' ); ?>
                        <?php
                        $duplex_property = class_exists( 'Nira_Properties' ) ? Nira_Properties::instance()->get_by_slug( 'duplex' ) : null;
                        $duplex_airbnb   = ! empty( $duplex_property->airbnb_url ) ? $duplex_property->airbnb_url : '';
                        if ( $duplex_airbnb ) : ?>
                            <a href="<?php echo esc_url( $duplex_airbnb ); ?>" target="_blank" rel="noopener" class="btn-airbnb">
                                <i class="fa-brands fa-airbnb"></i> Voir sur Airbnb
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <footer id="contact" class="site-footer">
        <div style="max-width: 1400px; margin: 0 auto; padding: 60px 5% 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 50px;">
            <div data-aos="fade-up">
                <img src="<?php echo esc_url( $IMG['logo_dark'] ); ?>" alt="<?php bloginfo( 'name' ); ?>" style="height: 70px; margin-bottom: 20px;">
                <p style="font-size: 0.9rem; color: #666; line-height: 1.6;">Pension, valorisation, débourrage, balnéothérapie et gîtes équestres au cœur de la Normandie.</p>
            </div>
            <div data-aos="fade-up" data-aos-delay="100">
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; font-style: italic; color: var(--bordeaux); margin-bottom: 25px; font-weight: 600;">Margaux Duchemin</h3>
                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 15px;">
                    <li style="display: flex; gap: 15px; color: var(--anthracite); font-size: 0.95rem;"><i class="fa-solid fa-location-dot" style="color: var(--bordeaux); margin-top: 4px;"></i><span>609 route de Deauville<br>14800 Bonneville-sur-Touques</span></li>
                    <li style="display: flex; align-items: center; gap: 15px; color: var(--anthracite); font-size: 0.95rem;"><i class="fa-solid fa-phone" style="color: var(--bordeaux);"></i><a href="tel:+33674572819" class="footer-link">06 74 57 28 19</a></li>
                    <li style="display: flex; align-items: center; gap: 15px; color: var(--anthracite); font-size: 0.95rem;"><i class="fa-solid fa-envelope" style="color: var(--bordeaux);"></i><a href="mailto:contact@ecuriedenira.fr" class="footer-link">contact@ecuriedenira.fr</a></li>
                </ul>
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <a href="https://www.facebook.com/profile.php?id=100088742455431" target="_blank" rel="noopener" aria-label="Facebook" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/ecurie_de_nira/" target="_blank" rel="noopener" aria-label="Instagram" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            <div data-aos="fade-up" data-aos-delay="200" style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                <img src="<?php echo esc_url( $IMG['partner_1'] ); ?>" alt="Label EquuRES" style="height: 60px;">
                <img src="<?php echo esc_url( $IMG['partner_2'] ); ?>" alt="Région Normandie" style="height: 60px;">
                <img src="<?php echo esc_url( $IMG['partner_3'] ); ?>" alt="Union européenne" style="height: 60px;">
                <p class="footer-funding" style="flex-basis:100%;width:100%;margin:14px 0 0;font-size:0.78rem;line-height:1.5;color:#777;max-width:560px;">Le projet « Amélioration de la structure – Écurie de Nira » a bénéficié d'une aide financière de la Région Normandie et de l'Union européenne.</p>
            </div>
        </div>
        <div style="border-top: 1px solid rgba(0,0,0,0.05); padding: 25px 5%;">
            <div style="max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div style="flex-basis:100%;width:100%;text-align:center;font-size:0.72rem;color:#999;line-height:1.45;">Projet « Amélioration de la structure – Écurie de Nira » cofinancé par la Région Normandie et l'Union européenne.</div>
                <div><a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>" class="footer-legal-link">Mentions légales et politique de confidentialité</a></div>
                <div style="font-size: 0.8rem; color: #888;">Copyright © <?php echo esc_html( date( 'Y' ) ); ?> Écurie de Nira</div>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        var LOGO_WHITE = <?php echo wp_json_encode( $IMG['logo_white'] ); ?>;
        var LOGO_DARK  = <?php echo wp_json_encode( $IMG['logo_dark'] ); ?>;

        window.onscroll = function() {
            var header = document.getElementById('header');
            var logo = document.getElementById('logoImg');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
                logo.src = LOGO_DARK;
            } else {
                header.classList.remove('scrolled');
                logo.src = LOGO_WHITE;
            }
        };

        function showRoom(roomId) {
            document.querySelectorAll('.room-card').forEach(function(card) { card.classList.remove('active'); });
            document.querySelectorAll('.room-detail').forEach(function(d) { d.classList.remove('active'); });
            document.getElementById('card-' + roomId).classList.add('active');
            document.getElementById('detail-' + roomId).classList.add('active');

            var yOffset = -100;
            var element = document.getElementById('room-details-section');
            var y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }

        var lightbox = document.getElementById('lightbox');
        var lightboxImg = document.getElementById('lightbox-img');

        function openLightbox(src) { lightboxImg.src = src; lightbox.classList.add('active'); }
        function closeLightbox()   { lightbox.classList.remove('active'); }
    </script>

    <?php wp_footer(); /* charge le JS du plugin Nira + Stripe */ ?>
</body>
</html>
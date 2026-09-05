<?php
/**
 * Template Name: Pension et tarifs (Nira)
 * Surcharge des images via options WP (clé : nira_pension_tarifs_<key>).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$IMG_BASE = NIRA_BOOKING_URL . 'assets/img';
if ( ! function_exists( 'nira_img' ) ) {
    function nira_img( $prefix, $key, $default_filename, $base ) {
        $override = get_option( 'nira_' . $prefix . '_' . $key );
        if ( ! empty( $override ) ) return $override;
        return $base . '/' . $default_filename;
    }
}
$IMG = [
    'logo_white'    => nira_img( 'pension_tarifs', 'logo_white',    'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'     => nira_img( 'pension_tarifs', 'logo_dark',     'logo-OK-VECTO.png',        $IMG_BASE ),
    'hero'          => nira_img( 'pension_tarifs', 'hero',          'IMG_7770.jpeg',            $IMG_BASE ),
    'coaching'      => nira_img( 'pension_tarifs', 'coaching',      'Capture-2026-04-10-19.55.09.jpg', $IMG_BASE ),
    'quotidien'     => nira_img( 'pension_tarifs', 'quotidien',     'IMG_1872.jpeg',            $IMG_BASE ),
    'competition'   => nira_img( 'pension_tarifs', 'competition',   '59AFF348-F0C6-4425-8989-B13ECEE3AC69.jpeg', $IMG_BASE ),
    'footer_logo'   => nira_img( 'pension_tarifs', 'footer_logo',   'logo-OK-VECTO.png',        $IMG_BASE ),
    'partner_1'     => nira_img( 'pension_tarifs', 'partner_1',     'macaron-engagement-1-150x150-1.png', $IMG_BASE ),
    'partner_2'     => nira_img( 'pension_tarifs', 'partner_2',     'region-normandie.png',     $IMG_BASE ),
    'partner_3'     => nira_img( 'pension_tarifs', 'partner_3',     'union-europeenne.png',     $IMG_BASE ),
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
            --transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background-color: var(--sand); color: var(--anthracite); overflow-x: hidden; line-height: 1.6; }
        h1, h2, h3 { font-family: 'Playfair Display', serif; font-weight: 700; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }

        header {
            position: fixed; top: 0; width: 100%; z-index: 1000;
            padding: 30px 4%; display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0));
            transition: var(--transition);
        }

        header.scrolled {
            padding: 15px 4%;
            background: rgba(253, 251, 249, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .logo img { height: 60px; transition: var(--transition); }
        header.scrolled .logo img { height: 50px; }

        .header-nav-container { display: flex; align-items: center; gap: 40px; }

        nav ul { display: flex; list-style: none; gap: 25px; align-items: center; }
        nav a { color: var(--white); font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; transition: var(--transition); }
        header.scrolled nav a { color: var(--anthracite); }
        nav a:hover { color: #ccc; }
        header.scrolled nav a:hover { color: var(--bordeaux); }

        .header-social { display: flex; gap: 12px; align-items: center; }
        .social-circle {
            background-color: var(--white); color: #000;
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; transition: var(--transition);
        }
        header.scrolled .social-circle { background-color: var(--anthracite); color: var(--white); }
        .social-circle:hover { transform: scale(1.1); }
        header.scrolled .social-circle:hover { background-color: var(--bordeaux); }

        .editorial-hero-dark {
            padding: 180px 5% 120px;
            background-color: var(--anthracite);
            color: var(--white);
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 80px;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .editorial-hero-dark::before {
            content: "02";
            position: absolute;
            right: 5%;
            top: 20%;
            font-family: 'Playfair Display', serif;
            font-size: 30vw;
            color: rgba(255,255,255,0.03);
            line-height: 1;
            pointer-events: none;
            z-index: 0;
        }

        .editorial-hero-text { position: relative; z-index: 2; max-width: 700px; }

        .breadcrumb { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 30px; color: rgba(255,255,255,0.5); }
        .breadcrumb a:hover { color: var(--white); }

        .editorial-title { font-size: clamp(3rem, 5vw, 5.5rem); line-height: 1.1; margin-bottom: 40px; font-weight: 700; }
        .editorial-title span { font-style: italic; font-weight: 400; font-family: 'Playfair Display', serif; color: var(--bordeaux); }

        .editorial-hero-image { position: relative; z-index: 2; height: 70vh; border-radius: 4px; overflow: hidden; box-shadow: -20px 20px 0 rgba(164, 28, 43, 0.2); }
        .editorial-hero-image img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .programme-section {
            padding: 140px 5%;
            background-color: var(--sand);
        }

        .programme-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto 120px;
        }
        .programme-row:last-child { margin-bottom: 0; }

        .programme-row.reverse .programme-text { order: 2; }
        .programme-row.reverse .programme-image { order: 1; }

        .programme-image {
            position: relative;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        }
        .programme-image img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.8s ease;
        }
        .programme-image:hover img { transform: scale(1.03); }

        .programme-tag {
            display: inline-block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--bordeaux);
            font-weight: 600;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(164, 28, 43, 0.3);
            padding-bottom: 5px;
        }

        .programme-title {
            font-size: 2.2rem;
            color: var(--anthracite);
            margin-bottom: 25px;
            line-height: 1.2;
        }

        .programme-desc {
            font-size: 1.05rem;
            color: #666;
            line-height: 1.8;
            font-weight: 300;
        }

        .footer-link { transition: color 0.3s ease; }
        .footer-link:hover { color: var(--bordeaux); }
        .footer-legal-link { font-size: 0.8rem; color: #888; transition: color 0.3s ease; }
        .footer-legal-link:hover { color: var(--bordeaux); }
        .social-icon { width: 40px; height: 40px; border-radius: 50%; background-color: var(--anthracite); color: var(--white); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.4s ease; }
        .social-icon:hover { background-color: var(--bordeaux); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(164, 28, 43, 0.3); }
        .partner-logo { height: 70px; object-fit: contain; filter: grayscale(100%) opacity(0.8); transition: all 0.4s ease; }
        .partner-logo:hover { filter: grayscale(0%) opacity(1); }
        .back-to-top { position: absolute; bottom: 30px; right: 5%; width: 45px; height: 45px; background-color: rgba(0,0,0,0.05); color: var(--anthracite); display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 1.2rem; transition: all 0.3s ease; z-index: 10; }
        .back-to-top:hover { background-color: var(--bordeaux); color: var(--white); }

        /* ============================================ */
        /* SECTION TARIFS                               */
        /* ============================================ */
        .tarifs-section {
            background-color: var(--white);
            padding: 140px 5%;
            position: relative;
        }
        .tarifs-header { max-width: 800px; margin: 0 auto 90px; text-align: center; }
        .tarifs-eyebrow {
            font-size: 0.8rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 5px; color: var(--bordeaux); margin-bottom: 20px; display: block;
        }
        .tarifs-section-title {
            font-size: clamp(2.2rem, 4vw, 3.4rem); color: var(--anthracite);
            line-height: 1.2; margin-bottom: 25px; font-weight: 700;
        }
        .tarifs-section-title em { font-style: italic; font-weight: 400; color: var(--bordeaux); }
        .tarifs-intro {
            font-size: 1.05rem; color: #666; line-height: 1.8;
            font-weight: 300; max-width: 600px; margin: 0 auto;
        }
        .tarifs-block { max-width: 1300px; margin: 0 auto 90px; }
        .tarifs-block-head {
            display: flex; align-items: center; gap: 25px;
            margin-bottom: 45px; padding-bottom: 25px;
            border-bottom: 1px solid rgba(164, 28, 43, 0.1);
        }
        .tarifs-icon {
            width: 65px; height: 65px; border-radius: 50%;
            background: rgba(164, 28, 43, 0.06); color: var(--bordeaux);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; flex-shrink: 0;
        }
        .tarifs-block-head h3 {
            font-family: 'Playfair Display', serif; font-size: 1.9rem;
            color: var(--anthracite); font-weight: 700; line-height: 1.2; margin-bottom: 6px;
        }
        .tarifs-block-head p { color: #888; font-size: 0.95rem; font-weight: 300; }
        .tarifs-cards {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); gap: 25px;
        }
        .tarif-card {
            background: var(--sand); border-radius: 8px; padding: 38px 32px;
            transition: var(--transition); border: 1px solid rgba(0, 0, 0, 0.04);
            display: flex; flex-direction: column;
        }
        .tarif-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 40px rgba(164, 28, 43, 0.08);
            border-color: rgba(164, 28, 43, 0.15);
        }
        .tarif-card-head { margin-bottom: 25px; }
        .tarif-tag {
            font-family: 'Inter', sans-serif; font-size: 0.7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 2.5px; color: var(--bordeaux);
            padding: 6px 14px; background: var(--white); border-radius: 100px;
            display: inline-block; box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .tarif-list { list-style: none; padding: 0; margin: 0; flex: 1; }
        .tarif-list li {
            display: flex; justify-content: space-between; align-items: baseline;
            padding: 14px 0; border-bottom: 1px dashed rgba(0,0,0,0.07); gap: 15px;
        }
        .tarif-list li:last-child { border-bottom: none; }
        .tarif-list li > span:first-child {
            font-size: 0.93rem; color: var(--anthracite); font-weight: 400;
        }
        .tarif-price {
            font-family: 'Playfair Display', serif; font-size: 1.35rem;
            color: var(--bordeaux); font-weight: 700; white-space: nowrap;
        }
        .tarif-price small {
            font-family: 'Inter', sans-serif; font-size: 0.7rem;
            color: #999; font-weight: 400; margin-left: 3px; letter-spacing: 0.5px;
        }
        .tarif-note {
            margin-top: 18px; padding-top: 14px;
            border-top: 1px solid rgba(0,0,0,0.05);
            font-size: 0.78rem; color: #999; font-style: italic; line-height: 1.5;
        }
        .tarif-card-feature {
            background: var(--anthracite); color: var(--white);
            text-align: center; border: none;
            align-items: center; justify-content: center;
        }
        .tarif-card-feature .tarif-tag {
            background: var(--bordeaux); color: var(--white); box-shadow: none;
        }
        .tarif-feature-price { margin: 20px 0 10px; }
        .tarif-price-big {
            display: block; font-family: 'Playfair Display', serif;
            font-size: 4rem; font-weight: 700; color: var(--white); line-height: 1;
        }
        .tarif-price-unit {
            display: block; font-size: 0.8rem; color: rgba(255,255,255,0.6);
            text-transform: uppercase; letter-spacing: 2px; margin-top: 8px;
        }
        .tarif-card-feature .tarif-note {
            border-top-color: rgba(255,255,255,0.1); color: rgba(255,255,255,0.6);
        }
        .tarifs-extras {
            max-width: 1100px; margin: 0 auto 90px;
            display: grid; grid-template-columns: 1fr 1fr; gap: 25px;
        }
        .tarif-extra-card {
            background: var(--sand); border-radius: 8px; padding: 38px;
            text-align: center; border: 1px solid rgba(0, 0, 0, 0.04);
            transition: var(--transition);
        }
        .tarif-extra-card:hover {
            transform: translateY(-4px); box-shadow: 0 15px 35px rgba(164, 28, 43, 0.06);
        }
        .tarif-extra-card i {
            font-size: 1.8rem; color: var(--bordeaux);
            margin-bottom: 18px; display: block;
        }
        .tarif-extra-card h4 {
            font-family: 'Playfair Display', serif; font-size: 1.5rem;
            color: var(--anthracite); margin-bottom: 12px; font-weight: 700;
        }
        .tarif-extra-card p { font-size: 1.05rem; color: var(--anthracite); margin-bottom: 8px; }
        .tarif-extra-card p strong { color: var(--bordeaux); font-weight: 700; }
        .tarif-extra-note {
            font-size: 0.78rem; color: #888; font-style: italic;
            line-height: 1.5; display: block;
        }
        .tarifs-cta {
            max-width: 800px; margin: 0 auto; text-align: center;
            padding: 50px 30px;
            background: linear-gradient(135deg, rgba(164, 28, 43, 0.03), rgba(164, 28, 43, 0.07));
            border-radius: 8px; border: 1px solid rgba(164, 28, 43, 0.1);
        }
        .tarifs-cta p {
            font-family: 'Playfair Display', serif; font-size: 1.4rem;
            color: var(--anthracite); font-style: italic; margin-bottom: 25px;
        }
        .btn-tarifs {
            display: inline-flex; align-items: center; gap: 12px;
            padding: 16px 38px; background: var(--bordeaux); color: var(--white);
            border-radius: 100px; text-transform: uppercase; font-size: 0.85rem;
            letter-spacing: 2px; font-weight: 600; transition: var(--transition);
            box-shadow: 0 5px 18px rgba(164, 28, 43, 0.25);
        }
        .btn-tarifs:hover {
            background: var(--anthracite); transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(45, 45, 45, 0.25); color: var(--white);
        }

        @media (max-width: 1024px) {
            header .header-nav-container { gap: 20px; }
            nav ul { gap: 15px; }
            nav a { font-size: 0.75rem; }
            .editorial-hero-dark { grid-template-columns: 1fr; padding-top: 150px; }
            .editorial-hero-image { height: 50vh; order: -1; margin-bottom: 50px; }
            .programme-row { gap: 50px; }
            .tarifs-extras { grid-template-columns: 1fr; }
        }
        @media (max-width: 992px) {
            header.scrolled { padding: 10px 5%; }
            nav ul { display: none; }
        }
        @media (max-width: 768px) {
            .programme-row { grid-template-columns: 1fr; margin-bottom: 80px; }
            .programme-row.reverse .programme-text { order: 1; }
            .programme-row.reverse .programme-image { order: 2; }
            .back-to-top { display: none; }
            footer > div:last-child > div { flex-direction: column; text-align: center; justify-content: center; }
            .tarifs-section { padding: 90px 5%; }
            .tarifs-block-head { flex-direction: column; text-align: center; gap: 15px; }
            .tarif-card { padding: 30px 25px; }
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <header id="header">
        <div class="logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img id="logoImg" src="<?php echo esc_url( $IMG['logo_white'] ); ?>" alt="Écurie de Nira">
            </a>
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

    <section class="editorial-hero-dark">
        <div class="editorial-hero-text" data-aos="fade-right">
            <div class="breadcrumb">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a> / <span>Pension &amp; Tarifs</span>
            </div>
            <h1 class="editorial-title">Pension &amp;<br><span style="margin-left: 60px;">Tarifs.</span></h1>
            <p style="font-size: 1.1rem; color: rgba(255,255,255,0.8); font-weight: 300; line-height: 1.8;">
                Pension box ou paddock, travail du cheval, balnéothérapie, débourrage… Découvrez l'ensemble de nos formules et tarifs, dans le cadre paisible et soigné de l'Écurie de Nira.
            </p>
            <a href="#tarifs" style="display:inline-flex;align-items:center;gap:12px;margin-top:35px;padding:16px 38px;background:var(--bordeaux);color:var(--white);border-radius:100px;text-transform:uppercase;font-size:0.85rem;letter-spacing:2px;font-weight:600;box-shadow:0 5px 18px rgba(164,28,43,0.35);">Voir les tarifs ↓</a>
        </div>
        <div class="editorial-hero-image" data-aos="fade-left" data-aos-delay="200">
            <img src="<?php echo esc_url( $IMG['hero'] ); ?>" alt="Travail et Valorisation Écurie de Nira">
        </div>
    </section>

    <section class="programme-section">

        <div class="programme-row">
            <div class="programme-text" data-aos="fade-right">
                <span class="programme-tag">Sur-mesure</span>
                <h2 class="programme-title">Coaching <span style="font-family: 'Playfair Display', serif; font-style: italic; color: var(--bordeaux);">personnalisé.</span></h2>
                <p class="programme-desc">
                    Chaque cheval est unique. Nous définissons ensemble des objectifs clairs et mettons en place un accompagnement adapté à votre couple cavalier-cheval. Nos séances sont pensées pour développer la confiance, la technique et la complicité.
                </p>
            </div>
            <div class="programme-image" data-aos="fade-left">
                <img src="<?php echo esc_url( $IMG['coaching'] ); ?>" alt="Coaching équestre Nira">
            </div>
        </div>

        <div class="programme-row reverse">
            <div class="programme-text" data-aos="fade-left">
                <span class="programme-tag">Performance</span>
                <h2 class="programme-title">Travail quotidien <br><span style="font-family: 'Playfair Display', serif; font-style: italic; font-weight: 400; font-size: 1.8rem;">(Plat & Obstacle).</span></h2>
                <p class="programme-desc">
                    Notre équipe assure le travail quotidien de votre monture. Sur le plat pour asseoir les bases de la gymnastique et de la souplesse, ou à l'obstacle pour parfaire la technique de saut. Un planning rigoureux qui garantit une évolution constante.
                </p>
            </div>
            <div class="programme-image" data-aos="fade-right">
                <img src="<?php echo esc_url( $IMG['quotidien'] ); ?>" alt="Travail obstacle cheval">
            </div>
        </div>

        <div class="programme-row">
            <div class="programme-text" data-aos="fade-right">
                <span class="programme-tag">Objectifs</span>
                <h2 class="programme-title">Sorties en <span style="font-family: 'Playfair Display', serif; font-style: italic; color: var(--bordeaux);">compétition.</span></h2>
                <p class="programme-desc">
                    Nous vous accompagnons et valorisons votre cheval sur les terrains de concours. De l'engagement à la détente, bénéficiez d'un encadrement professionnel pour aborder les épreuves avec sérénité et maximiser vos résultats sportifs.
                </p>
            </div>
            <div class="programme-image" data-aos="fade-left">
                <img src="<?php echo esc_url( $IMG['competition'] ); ?>" alt="Sortie en compétition équitation">
            </div>
        </div>

    </section>

    <!-- ============================================== -->
    <!-- SECTION TARIFS                                  -->
    <!-- ============================================== -->
    <section class="tarifs-section" id="tarifs">
        <div class="tarifs-header" data-aos="fade-up">
            <span class="tarifs-eyebrow">Transparence et clarté</span>
            <h2 class="tarifs-section-title">Nos formules <em>&amp; tarifs.</em></h2>
            <p class="tarifs-intro">Des prix clairs et sans surprise, pour offrir à votre cheval le meilleur cadre de vie. Tous nos tarifs sont indiqués TTC, sauf mention contraire.</p>
        </div>

        <!-- LA PENSION -->
        <div class="tarifs-block" data-aos="fade-up">
            <div class="tarifs-block-head">
                <div class="tarifs-icon"><i class="fa-solid fa-house-chimney-window"></i></div>
                <div>
                    <h3>La pension</h3>
                    <p>Box sur paille ou copeaux, formules adaptées à votre rythme.</p>
                </div>
            </div>
            <div class="tarifs-cards">
                <div class="tarif-card">
                    <div class="tarif-card-head"><span class="tarif-tag">Au mois</span></div>
                    <ul class="tarif-list">
                        <li><span>Box standard sur paille</span><span class="tarif-price">580 €<small>/mois</small></span></li>
                        <li><span>Box standard sur copeaux</span><span class="tarif-price">640 €<small>/mois</small></span></li>
                        <li><span>Grand box sur paille</span><span class="tarif-price">680 €<small>/mois</small></span></li>
                        <li><span>Grand box sur copeaux</span><span class="tarif-price">740 €<small>/mois</small></span></li>
                    </ul>
                </div>
                <div class="tarif-card">
                    <div class="tarif-card-head"><span class="tarif-tag">À la semaine</span></div>
                    <ul class="tarif-list">
                        <li><span>Sur paille</span><span class="tarif-price">200 €<small>/sem.</small></span></li>
                        <li><span>Sur copeaux</span><span class="tarif-price">280 €<small>/sem.</small></span></li>
                    </ul>
                </div>
                <div class="tarif-card">
                    <div class="tarif-card-head"><span class="tarif-tag">À la nuit</span></div>
                    <ul class="tarif-list">
                        <li><span>Box vide</span><span class="tarif-price">30 €<small>/nuit</small></span></li>
                        <li><span>Box sur paille</span><span class="tarif-price">35 €<small>/nuit</small></span></li>
                        <li><span>Box sur copeaux</span><span class="tarif-price">45 €<small>/nuit</small></span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- LE TRAVAIL -->
        <div class="tarifs-block" data-aos="fade-up">
            <div class="tarifs-block-head">
                <div class="tarifs-icon"><i class="fa-solid fa-horse"></i></div>
                <div>
                    <h3>Le travail du cheval</h3>
                    <p>Coaching, débourrage et accès aux infrastructures.</p>
                </div>
            </div>
            <div class="tarifs-cards">
                <div class="tarif-card">
                    <div class="tarif-card-head"><span class="tarif-tag">Coaching</span></div>
                    <ul class="tarif-list">
                        <li><span>À la séance</span><span class="tarif-price">30 €<small>TTC</small></span></li>
                        <li><span>3× / semaine</span><span class="tarif-price">230 €<small>/mois</small></span></li>
                        <li><span>5× / semaine</span><span class="tarif-price">300 €<small>/mois</small></span></li>
                    </ul>
                </div>
                <div class="tarif-card tarif-card-feature">
                    <div class="tarif-card-head"><span class="tarif-tag">Débourrage</span></div>
                    <div class="tarif-feature-price">
                        <span class="tarif-price-big">30 €</span>
                        <span class="tarif-price-unit">TTC / jour</span>
                    </div>
                    <p class="tarif-note">Pension + débourrage inclus<br>(25 € HT)</p>
                </div>
                <div class="tarif-card">
                    <div class="tarif-card-head"><span class="tarif-tag">Infrastructures (extérieurs)</span></div>
                    <ul class="tarif-list">
                        <li><span>À la séance</span><span class="tarif-price">15 €<small>/cheval</small></span></li>
                        <li><span>Au mois (illimité)</span><span class="tarif-price">160 €<small>/cheval</small></span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- BIEN-ÊTRE -->
        <div class="tarifs-block" data-aos="fade-up">
            <div class="tarifs-block-head">
                <div class="tarifs-icon"><i class="fa-solid fa-spa"></i></div>
                <div>
                    <h3>Bien-être &amp; soins</h3>
                    <p>Balnéothérapie, sortie plage, vétérinaire et soins.</p>
                </div>
            </div>
            <div class="tarifs-cards">
                <div class="tarif-card">
                    <div class="tarif-card-head"><span class="tarif-tag">Balnéothérapie</span></div>
                    <ul class="tarif-list">
                        <li><span>Formule à la semaine</span><span class="tarif-price">730 €<small>TTC</small></span></li>
                        <li><span>Formule au mois</span><span class="tarif-price">1 980 €<small>TTC</small></span></li>
                    </ul>
                    <p class="tarif-note">HT : 608 € · 1 650 €</p>
                </div>
                <div class="tarif-card">
                    <div class="tarif-card-head"><span class="tarif-tag">Sortie plage</span></div>
                    <ul class="tarif-list">
                        <li><span>Sortie plage</span><span class="tarif-price">35 €<small>TTC</small></span></li>
                        <li><span>Plage + Solarium</span><span class="tarif-price">55 €<small>TTC</small></span></li>
                    </ul>
                </div>
                <div class="tarif-card">
                    <div class="tarif-card-head"><span class="tarif-tag">Soins</span></div>
                    <ul class="tarif-list">
                        <li><span>Soin du cheval</span><span class="tarif-price">Sur devis</span></li>
                        <li><span>Vétérinaire sur place</span><span class="tarif-price">10 €<small>/20 min</small></span></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- DÉPLACEMENTS -->
        <div class="tarifs-extras" data-aos="fade-up">
            <div class="tarif-extra-card">
                <i class="fa-solid fa-truck-fast"></i>
                <h4>Transport</h4>
                <p><strong>1,08 € / km</strong> TTC (hors péage)</p>
                <span class="tarif-extra-note">Forfait minimum : 30 € TTC · Tarif HT : 0,90 € / km</span>
            </div>
            <div class="tarif-extra-card">
                <i class="fa-solid fa-trophy"></i>
                <h4>Sortie concours</h4>
                <p><strong>50 € TTC</strong> par sortie</p>
                <span class="tarif-extra-note">+ 1,08 € TTC du km au-delà de 30 km A/R (hors péage)</span>
            </div>
        </div>

        <!-- CTA -->
        <div class="tarifs-cta" data-aos="fade-up">
            <p>Une question sur nos formules ? Un projet sur-mesure ?</p>
            <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn-tarifs">Nous contacter →</a>
        </div>
    </section>

    <footer id="contact" style="background-color: var(--white); position: relative; padding-top: 40px;">
        <svg style="position: absolute; top: -1px; left: 0; width: 100%; height: 40px; fill: var(--sand);" viewBox="0 0 1440 100" preserveAspectRatio="none">
            <path d="M0,100 C320,0 420,100 740,50 C1060,0 1120,100 1440,50 L1440,0 L0,0 Z"></path>
        </svg>

        <div style="max-width: 1400px; margin: 0 auto; padding: 60px 5% 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 50px; align-items: flex-start;">

            <div data-aos="fade-up">
                <img src="<?php echo esc_url( $IMG['footer_logo'] ); ?>" alt="Écurie de Nira" style="height: 70px; margin-bottom: 20px;">
                <p style="font-size: 0.9rem; color: #666; line-height: 1.6;">
                    Pension, valorisation, débourrage, balnéothérapie et gîtes équestres au cœur de la Normandie.
                </p>
            </div>

            <div data-aos="fade-up" data-aos-delay="100">
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; font-style: italic; color: var(--bordeaux); margin-bottom: 25px; font-weight: 600;">
                    Margaux Duchemin
                </h3>
                <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 15px;">
                    <li style="display: flex; gap: 15px; color: var(--anthracite); font-size: 0.95rem;">
                        <i class="fa-solid fa-location-dot" style="color: var(--bordeaux); margin-top: 4px; font-size: 1.1rem;"></i>
                        <span>609 route de Deauville<br>14800 Bonneville-sur-Touques</span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 15px; color: var(--anthracite); font-size: 0.95rem;">
                        <i class="fa-solid fa-phone" style="color: var(--bordeaux); font-size: 1.1rem;"></i>
                        <a href="tel:+33674572819" class="footer-link">06 74 57 28 19</a>
                    </li>
                    <li style="display: flex; align-items: center; gap: 15px; color: var(--anthracite); font-size: 0.95rem;">
                        <i class="fa-solid fa-envelope" style="color: var(--bordeaux); font-size: 1.1rem;"></i>
                        <a href="mailto:contact@ecuriedenira.fr" class="footer-link">contact@ecuriedenira.fr</a>
                    </li>
                </ul>
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <a href="https://www.facebook.com/profile.php?id=100088742455431" target="_blank" rel="noopener" aria-label="Facebook" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/ecurie_de_nira/" target="_blank" rel="noopener" aria-label="Instagram" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>

            <div data-aos="fade-up" data-aos-delay="200" style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                <img src="<?php echo esc_url( $IMG['partner_1'] ); ?>" alt="Label EquuRES" class="partner-logo">
                <img src="<?php echo esc_url( $IMG['partner_2'] ); ?>" alt="Région Normandie" class="partner-logo">
                <img src="<?php echo esc_url( $IMG['partner_3'] ); ?>" alt="Financé par l'Union européenne" class="partner-logo">
                <p class="footer-funding" style="flex-basis:100%;width:100%;margin:14px 0 0;font-size:0.78rem;line-height:1.5;color:#777;max-width:560px;">Le projet « Amélioration de la structure – Écurie de Nira » a bénéficié d'une aide financière de la Région Normandie et de l'Union européenne.</p>
            </div>
        </div>

        <div style="border-top: 1px solid rgba(0,0,0,0.06); padding: 25px 5%;">
            <div style="max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                <div style="flex-basis:100%;width:100%;text-align:center;font-size:0.72rem;color:#999;line-height:1.45;">Projet « Amélioration de la structure – Écurie de Nira » cofinancé par la Région Normandie et l'Union européenne.</div>
                <div><a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>" class="footer-legal-link">Mentions légales et politique de confidentialité</a></div>
                <div style="font-size: 0.8rem; color: #888;">Copyright © 2026 Ecurie de Nira</div>
                <div style="font-size: 0.8rem; color: #888;">Créé par NOK'S Consulting</div>
            </div>
        </div>

        <a href="#header" class="back-to-top" id="backToTopBtn"><i class="fa-solid fa-chevron-up"></i></a>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        var LOGO_WHITE = <?php echo wp_json_encode( $IMG['logo_white'] ); ?>;
        var LOGO_DARK  = <?php echo wp_json_encode( $IMG['logo_dark'] ); ?>;

        AOS.init({ duration: 1000, once: true });

        window.onscroll = function() {
            const header = document.getElementById('header');
            const logo = document.getElementById('logoImg');

            if (window.scrollY > 50) {
                header.classList.add('scrolled');
                logo.src = LOGO_DARK;
            } else {
                header.classList.remove('scrolled');
                logo.src = LOGO_WHITE;
            }
        };
    </script>
    <?php wp_footer(); ?>
</body>
</html>

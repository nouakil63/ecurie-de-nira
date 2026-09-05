<?php
/**
 * Template Name: Débourrage (Nira)
 * Surcharge des images via options WP (clé : nira_debourrage_<key>).
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
    'logo_white'    => nira_img( 'debourrage', 'logo_white',    'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'     => nira_img( 'debourrage', 'logo_dark',     'logo-OK-VECTO.png',        $IMG_BASE ),
    'hero_bg'       => nira_img( 'debourrage', 'hero_bg',       'IMG_1872.jpeg',            $IMG_BASE ),
    'method'        => nira_img( 'debourrage', 'method',        'Capture-2026-04-11-11.30.56.jpg', $IMG_BASE ),
    'footer_logo'   => nira_img( 'debourrage', 'footer_logo',   'logo-OK-VECTO.png',        $IMG_BASE ),
    'partner_1'     => nira_img( 'debourrage', 'partner_1',     'macaron-engagement-1-150x150-1.png', $IMG_BASE ),
    'partner_2'     => nira_img( 'debourrage', 'partner_2',     'region-normandie.png',     $IMG_BASE ),
    'partner_3'     => nira_img( 'debourrage', 'partner_3',     'union-europeenne.png',     $IMG_BASE ),
];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( get_the_title() ); ?> | <?php bloginfo( 'name' ); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bordeaux: #8B1A24;
            --anthracite: #2C2A29;
            --sand: #FAF8F5;
            --white: #FFFFFF;
            --text-light: #6C6865;
            --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--sand);
            color: var(--anthracite);
            overflow-x: hidden;
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }

        header {
            position: fixed; top: 0; width: 100%; z-index: 1000;
            padding: 20px 5%; display: flex; justify-content: space-between; align-items: center;
            background-color: rgba(250, 248, 245, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.03);
            transition: var(--transition);
        }

        .logo { display: flex; align-items: center; height: 50px; }
        .logo img { height: 100%; width: auto; }

        .header-nav-container { display: flex; align-items: center; gap: 40px; }

        nav ul { display: flex; list-style: none; gap: 35px; align-items: center; }
        nav a {
            color: var(--anthracite);
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
        }
        nav a::after {
            content: ''; position: absolute; left: 0; bottom: -5px; width: 0; height: 1px;
            background-color: var(--bordeaux); transition: var(--transition);
        }
        nav a:hover::after { width: 100%; }

        .header-social { display: flex; gap: 15px; align-items: center; }
        .social-circle {
            background-color: var(--anthracite); color: var(--white);
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; transition: var(--transition);
        }
        .social-circle:hover { background-color: var(--bordeaux); transform: translateY(-2px); }

        .debourrage-hero {
            background-color: var(--sand);
            padding: 180px 5% 100px;
            position: relative;
            overflow: hidden;
        }
        .debourrage-hero::before {
            content: "";
            position: absolute;
            top: -150px;
            left: -100px;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            background: rgba(139, 26, 36, 0.04);
            z-index: 0;
        }
        .hero-grid {
            max-width: 1300px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 90px;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        .hero-text-col { max-width: 600px; }
        .hero-breadcrumb {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--text-light);
            margin-bottom: 28px;
        }
        .hero-breadcrumb a { color: var(--text-light); text-decoration: none; transition: color 0.3s; }
        .hero-breadcrumb a:hover { color: var(--bordeaux); }
        .hero-breadcrumb span { color: var(--anthracite); }
        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: var(--bordeaux);
            margin-bottom: 24px;
        }
        .hero-eyebrow::before {
            content: "";
            display: inline-block;
            width: 30px;
            height: 1px;
            background: var(--bordeaux);
        }
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.8rem, 5.2vw, 4.6rem);
            line-height: 1.05;
            color: var(--anthracite);
            margin-bottom: 28px;
            font-weight: 700;
        }
        .hero-title em {
            font-style: italic;
            font-weight: 400;
            color: var(--bordeaux);
            display: block;
        }
        .hero-intro {
            font-size: 1.1rem;
            line-height: 1.85;
            color: var(--text-light);
            font-weight: 300;
            margin-bottom: 36px;
        }
        .hero-stats {
            display: flex;
            gap: 36px;
            padding-top: 28px;
            border-top: 1px solid rgba(0,0,0,0.08);
        }
        .hero-stat-item {
            flex: 1;
        }
        .hero-stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 2.6rem;
            color: var(--bordeaux);
            line-height: 1;
            font-weight: 700;
            display: block;
            margin-bottom: 6px;
        }
        .hero-stat-label {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-light);
            font-weight: 500;
        }
        .hero-image-col {
            position: relative;
            height: 580px;
        }
        .hero-image-main {
            position: absolute;
            top: 0;
            right: 0;
            width: 88%;
            height: 100%;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.12);
        }
        .hero-image-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 1.2s ease;
        }
        .hero-image-main:hover img { transform: scale(1.05); }
        .hero-image-tag {
            position: absolute;
            background: var(--white);
            padding: 16px 24px;
            border-radius: 6px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.10);
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 2;
        }
        .hero-image-tag--top { top: 28px; right: -30px; }
        .hero-image-tag--bottom { bottom: 30px; left: -30px; }
        .hero-image-tag i {
            font-size: 1.4rem;
            color: var(--bordeaux);
        }
        .hero-image-tag div { line-height: 1.3; }
        .hero-image-tag strong {
            display: block;
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            color: var(--anthracite);
            font-weight: 700;
        }
        .hero-image-tag span {
            font-size: 0.75rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        @media (max-width: 992px) {
            .debourrage-hero { padding: 140px 5% 70px; }
            .hero-grid { grid-template-columns: 1fr; gap: 60px; }
            .hero-image-col { height: 420px; max-width: 600px; }
            .hero-stats { gap: 24px; }
        }
        @media (max-width: 600px) {
            .hero-stat-num { font-size: 2.1rem; }
            .hero-image-col { height: 380px; margin: 0 auto; padding: 0 10px; }
            .hero-image-main { width: 100%; }
            .hero-image-tag {
                padding: 10px 14px;
                gap: 10px;
                max-width: calc(100% - 24px);
            }
            .hero-image-tag i { font-size: 1.1rem; }
            .hero-image-tag strong { font-size: 0.9rem; }
            .hero-image-tag span { font-size: 0.65rem; letter-spacing: 0.5px; }
            .hero-image-tag--top { top: 12px; right: 12px; left: auto; }
            .hero-image-tag--bottom { bottom: 12px; left: 12px; right: auto; }
        }

        .method-section {
            padding: 100px 5%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .method-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }
        .method-image {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.06);
            height: 600px;
        }
        .method-image img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .method-text h2 {
            font-size: 2.5rem;
            color: var(--anthracite);
            margin-bottom: 30px;
            line-height: 1.2;
        }
        .method-text p {
            color: var(--text-light);
            font-size: 1.05rem;
            margin-bottom: 20px;
            font-weight: 300;
        }
        .method-text strong {
            color: var(--anthracite);
            font-weight: 500;
        }

        .feature-list {
            margin: 30px 0;
            list-style: none;
        }
        .feature-list li {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 1rem;
            color: var(--anthracite);
        }
        .feature-list i {
            color: var(--bordeaux);
            font-size: 1.2rem;
        }

        .highlight-box {
            background-color: var(--white);
            border-left: 4px solid var(--bordeaux);
            padding: 20px 25px;
            margin-top: 40px;
            border-radius: 0 8px 8px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            font-style: italic;
            color: var(--text-light);
        }

        .pricing-section {
            background-color: var(--white);
            padding: 100px 5%;
            text-align: center;
        }
        .pricing-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .pricing-title {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 3rem;
            color: var(--anthracite);
            margin-bottom: 50px;
        }

        .pricing-card {
            background-color: var(--sand);
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 12px;
            padding: 50px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
        }
        .pricing-card::before {
            content: '';
            position: absolute; top: 0; left: 0; width: 100%; height: 5px;
            background-color: var(--bordeaux);
        }

        .pricing-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--anthracite);
            margin-bottom: 10px;
        }
        .pricing-amount {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            color: var(--bordeaux);
            margin-bottom: 5px;
        }
        .pricing-detail {
            font-size: 0.9rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }

        .pricing-inclusions {
            font-size: 1rem;
            color: var(--anthracite);
            padding-top: 30px;
            border-top: 1px solid rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }

        .btn-reserve {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 18px 40px;
            background-color: var(--anthracite);
            color: var(--white);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.85rem;
            border-radius: 4px;
            transition: var(--transition);
            cursor: pointer;
            border: 1px solid var(--anthracite);
        }
        .btn-reserve:hover {
            background-color: var(--bordeaux);
            border-color: var(--bordeaux);
            color: var(--white);
        }

        footer { background-color: var(--sand); padding-top: 80px; }
        .footer-grid { max-width: 1400px; margin: 0 auto; padding: 0 5% 60px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 60px; }
        .footer-logo img { height: 70px; margin-bottom: 20px; }
        .footer-text { font-size: 0.9rem; color: var(--text-light); line-height: 1.6; }

        .footer-title { font-size: 1.6rem; font-style: italic; color: var(--bordeaux); margin-bottom: 25px; }
        .footer-contact-list { list-style: none; display: flex; flex-direction: column; gap: 15px; }
        .footer-contact-list li { display: flex; gap: 15px; color: var(--anthracite); font-size: 0.95rem; }
        .footer-contact-list i { color: var(--bordeaux); margin-top: 4px; }

        .footer-bottom { border-top: 1px solid rgba(0,0,0,0.05); padding: 25px 5%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; max-width: 1400px; margin: 0 auto; font-size: 0.8rem; color: #888; }
        .footer-bottom a { transition: color 0.3s ease; }
        .footer-bottom a:hover { color: var(--bordeaux); }

        @media (max-width: 992px) {
            .method-container { grid-template-columns: 1fr; gap: 50px; }
            .method-image { height: 400px; }
        }
        @media (max-width: 768px) {
            header { padding: 15px 5%; }
            nav ul { display: none; }
            .method-text h2 { font-size: 2rem; }
            .pricing-card { padding: 30px; }
            .footer-bottom { flex-direction: column; text-align: center; }
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

    <header id="header" class="scrolled">
        <div class="logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img id="logoImg" src="<?php echo esc_url( $IMG['logo_dark'] ); ?>" alt="Écurie de Nira">
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

    <section class="debourrage-hero">
        <div class="hero-grid">
            <div class="hero-text-col" data-aos="fade-right">
                <div class="hero-breadcrumb">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a> <span>/ Débourrage</span>
                </div>
                <span class="hero-eyebrow">Notre approche</span>
                <h1 class="hero-title">Débourrage <em>&amp; valorisation.</em></h1>
                <p class="hero-intro">
                    Une étape clé dans la vie de votre cheval, abordée avec patience, douceur et 20 ans d'expérience. Nous construisons les bases d'une relation de confiance durable, dans le respect du rythme et du caractère de chaque équidé.
                </p>
                <div class="hero-stats">
                    <div class="hero-stat-item">
                        <span class="hero-stat-num">20</span>
                        <span class="hero-stat-label">ans d'expérience</span>
                    </div>
                    <div class="hero-stat-item">
                        <span class="hero-stat-num">1500m²</span>
                        <span class="hero-stat-label">paddocks individuels</span>
                    </div>
                    <div class="hero-stat-item">
                        <span class="hero-stat-num">7j/7</span>
                        <span class="hero-stat-label">sorties garanties</span>
                    </div>
                </div>
            </div>
            <div class="hero-image-col" data-aos="fade-left" data-aos-delay="200">
                <div class="hero-image-main">
                    <img src="<?php echo esc_url( $IMG['hero_bg'] ); ?>" alt="Débourrage et valorisation à l'Écurie de Nira">
                </div>
                <div class="hero-image-tag hero-image-tag--top" data-aos="zoom-in" data-aos-delay="500">
                    <i class="fa-solid fa-crown"></i>
                    <div>
                        <strong>Margaux Duchemin</strong>
                        <span>Gérante et cavalière</span>
                    </div>
                </div>
                <div class="hero-image-tag hero-image-tag--bottom" data-aos="zoom-in" data-aos-delay="600">
                    <i class="fa-solid fa-horse-head"></i>
                    <div>
                        <strong>Gabriel Andrieu</strong>
                        <span>Cavalier pro</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="method-section">
        <div class="method-container">
            <div class="method-text" data-aos="fade-right">
                <h2>Débourrage dans le <br><span style="font-family: 'Playfair Display', serif; font-style: italic; color: var(--bordeaux); font-weight: 400;">respect du cheval</span></h2>

                <p>Le débourrage étant une étape particulièrement importante dans la vie d'un cheval, l'écurie de Nira met en place un programme adapté et personnalisé pour chaque équidé.</p>

                <p>Bénéficiez des <strong>20 ans d'expérience de Gabriel Andrieu</strong>, notre cavalier expert en débourrage et jeunes chevaux (chevaux de selle et pur-sang).</p>

                <p>Votre cheval sera accueilli en box/paddock pendant son séjour. Pour garantir son équilibre mental et physique, des sorties sont prévues <strong>tous les jours et toute la journée</strong> dans nos grands paddocks individuels (1500 à 2000m2).</p>

                <ul class="feature-list">
                    <li><i class="fa-solid fa-check"></i> Foin à volonté au box</li>
                    <li><i class="fa-solid fa-check"></i> Nourriture Lambey de haute qualité</li>
                    <li><i class="fa-solid fa-check"></i> Sorties quotidiennes garanties</li>
                </ul>

                <div class="highlight-box">
                    "À la fin du débourrage, les chevaux évoluent aux 3 allures dans la carrière et, au besoin, sautent montés."
                </div>
            </div>

            <div class="method-image" data-aos="fade-left">
                <img src="<?php echo esc_url( $IMG['method'] ); ?>" alt="Débourrage Écurie de Nira">
            </div>
        </div>
    </section>

    <section class="pricing-section" data-aos="fade-up">
        <div class="pricing-container">
            <h2 class="pricing-title">Nos tarifs</h2>

            <div class="pricing-card">
                <div class="pricing-name">Forfait Débourrage</div>
                <div class="pricing-amount">30€ <span style="font-size: 1.2rem; color: var(--anthracite); font-family: 'Inter', sans-serif;">TTC</span></div>
                <div class="pricing-detail">Par jour (25€ HT)</div>

                <div class="pricing-inclusions">
                    <strong>Ce tarif comprend :</strong> La pension complète (Box/Paddock, alimentation Lambey, foin à volonté) <strong>+</strong> Le travail quotidien de débourrage.
                </div>

                <p style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 30px;">
                    <em>Le tarif de cette prestation est facturé au jour, ce qui permet de s'adapter au mieux au rythme et aux besoins spécifiques de chaque cheval.</em>
                </p>

                <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn-reserve">Contacter l'écurie</a>
            </div>
        </div>
    </section>

    <footer id="contact">
        <div class="footer-grid">
            <div data-aos="fade-up">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo"><img src="<?php echo esc_url( $IMG['footer_logo'] ); ?>" alt="Écurie de Nira"></a>
                <p class="footer-text">Pension, valorisation, débourrage, balnéothérapie et gîtes équestres au cœur de la Normandie.</p>
            </div>

            <div data-aos="fade-up" data-aos-delay="100">
                <h3 class="footer-title">Margaux Duchemin</h3>
                <ul class="footer-contact-list">
                    <li><i class="fa-solid fa-location-dot"></i><span>609 route de Deauville<br>14800 Bonneville-sur-Touques</span></li>
                    <li><i class="fa-solid fa-phone"></i><a href="tel:+33674572819">06 74 57 28 19</a></li>
                    <li><i class="fa-solid fa-envelope"></i><a href="mailto:contact@ecuriedenira.fr">contact@ecuriedenira.fr</a></li>
                </ul>
            </div>

            <div data-aos="fade-up" data-aos-delay="200" style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                <img src="<?php echo esc_url( $IMG['partner_1'] ); ?>" alt="Label EquuRES" style="height: 60px;">
                <img src="<?php echo esc_url( $IMG['partner_2'] ); ?>" alt="Région Normandie" style="height: 60px;">
                <img src="<?php echo esc_url( $IMG['partner_3'] ); ?>" alt="Union européenne" style="height: 60px;">
                <p class="footer-funding" style="flex-basis:100%;width:100%;margin:14px 0 0;font-size:0.78rem;line-height:1.5;color:#777;max-width:560px;">Le projet « Amélioration de la structure – Écurie de Nira » a bénéficié d'une aide financière de la Région Normandie et de l'Union européenne.</p>
            </div>
        </div>

        <div class="footer-bottom">
            <div style="flex-basis:100%;width:100%;text-align:center;font-size:0.72rem;color:#999;line-height:1.45;">Projet « Amélioration de la structure – Écurie de Nira » cofinancé par la Région Normandie et l'Union européenne.</div>
            <a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>">Mentions légales et politique de confidentialité</a>
            <div>Copyright © 2026 Ecurie de Nira</div>
            <div>Créé par NOK'S Consulting</div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        var LOGO_WHITE = <?php echo wp_json_encode( $IMG['logo_white'] ); ?>;
        var LOGO_DARK  = <?php echo wp_json_encode( $IMG['logo_dark'] ); ?>;
        AOS.init({ duration: 1000, once: true });

        // La page Débourrage a un hero clair → header en mode "scrolled"
        // dès le chargement (logo dark, fond sand, texte anthracite).
        document.addEventListener('DOMContentLoaded', function() {
            var header = document.getElementById('header');
            var logo = document.getElementById('logoImg');
            if (header) header.classList.add('scrolled');
            if (logo) logo.src = LOGO_DARK;
        });
        window.onscroll = function() {
            var header = document.getElementById('header');
            if (header && !header.classList.contains('scrolled')) {
                header.classList.add('scrolled');
            }
        };
    </script>
    <?php wp_footer(); ?>
</body>
</html>

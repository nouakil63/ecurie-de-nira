<?php
/**
 * Template Name: Pension Travail (Nira)
 * Surcharge des images via options WP (clé : nira_pension_travail_<key>).
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
    'logo_white'    => nira_img( 'pension_travail', 'logo_white',    'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'     => nira_img( 'pension_travail', 'logo_dark',     'logo-OK-VECTO.png',        $IMG_BASE ),
    'hero'          => nira_img( 'pension_travail', 'hero',          '84F201E1-7A80-43B9-805D-E3C8ACBA00F7.jpeg', $IMG_BASE ),
    'coaching'      => nira_img( 'pension_travail', 'coaching',      'IMG_3038.jpeg',            $IMG_BASE ),
    'quotidien'     => nira_img( 'pension_travail', 'quotidien',     'IMG_1872.jpeg',            $IMG_BASE ),
    'competition'   => nira_img( 'pension_travail', 'competition',   'image00032.jpeg',          $IMG_BASE ),
    'footer_logo'   => nira_img( 'pension_travail', 'footer_logo',   'logo-OK-VECTO.png',        $IMG_BASE ),
    'partner_1'     => nira_img( 'pension_travail', 'partner_1',     'macaron-engagement-1-150x150-1.png', $IMG_BASE ),
    'partner_2'     => nira_img( 'pension_travail', 'partner_2',     'region-normandie.png',     $IMG_BASE ),
    'partner_3'     => nira_img( 'pension_travail', 'partner_3',     'union-europeenne.png',     $IMG_BASE ),
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

        @media (max-width: 1024px) {
            header .header-nav-container { gap: 20px; }
            nav ul { gap: 15px; }
            nav a { font-size: 0.75rem; }
            .editorial-hero-dark { grid-template-columns: 1fr; padding-top: 150px; }
            .editorial-hero-image { height: 50vh; order: -1; margin-bottom: 50px; }
            .programme-row { gap: 50px; }
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
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a> / <a href="<?php echo esc_url( home_url( '/pension-et-tarifs' ) ); ?>">Pensions</a> / <span>Travail & Valorisation</span>
            </div>
            <h1 class="editorial-title">Travail &<br><span style="margin-left: 60px;">Valorisation.</span></h1>
            <p style="font-size: 1.1rem; color: rgba(255,255,255,0.8); font-weight: 300; line-height: 1.8;">
                Optimisez le potentiel de votre cheval avec un programme d'entraînement sur-mesure. Une approche sportive respectueuse, encadrée par des professionnels passionnés.
            </p>
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

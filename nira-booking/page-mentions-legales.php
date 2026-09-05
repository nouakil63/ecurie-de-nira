<?php
/**
 * Template Name: Mentions légales (Nira)
 * Page mentions légales & politique de confidentialité.
 * Surcharge des images via options WP (clé : nira_mentions_legales_<key>).
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
    'logo_white'  => nira_img( 'mentions_legales', 'logo_white',  'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'   => nira_img( 'mentions_legales', 'logo_dark',   'logo-OK-VECTO.png',        $IMG_BASE ),
    'footer_logo' => nira_img( 'mentions_legales', 'footer_logo', 'logo-OK-VECTO.png',        $IMG_BASE ),
    'partner_1'   => nira_img( 'mentions_legales', 'partner_1',   'macaron-engagement-1-150x150-1.png', $IMG_BASE ),
    'partner_2'   => nira_img( 'mentions_legales', 'partner_2',   'region-normandie.png',     $IMG_BASE ),
    'partner_3'   => nira_img( 'mentions_legales', 'partner_3',   'union-europeenne.png',     $IMG_BASE ),
];

$annee = date( 'Y' );
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

        /* ===== Hero ===== */
        .legal-hero {
            padding: 180px 5% 90px;
            background-color: var(--anthracite);
            color: var(--white);
            position: relative;
            overflow: hidden;
        }
        .legal-hero::before {
            content: "§";
            position: absolute; right: 5%; top: 10%;
            font-family: 'Playfair Display', serif;
            font-size: 26vw; color: rgba(255,255,255,0.03);
            line-height: 1; pointer-events: none; z-index: 0;
        }
        .legal-hero-inner { position: relative; z-index: 2; max-width: 1200px; margin: 0 auto; }
        .breadcrumb { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 26px; color: rgba(255,255,255,0.5); }
        .breadcrumb a:hover { color: var(--white); }
        .breadcrumb span { color: var(--white); }
        .legal-title { font-size: clamp(2.6rem, 4.5vw, 4.5rem); line-height: 1.1; font-weight: 700; }
        .legal-title span { font-style: italic; font-weight: 400; color: var(--bordeaux); }
        .legal-subtitle { margin-top: 18px; font-size: 1.02rem; color: rgba(255,255,255,0.7); max-width: 640px; }

        /* ===== Contenu ===== */
        .legal-content { max-width: 880px; margin: 0 auto; padding: 80px 5% 100px; }
        .legal-block { margin-bottom: 50px; }
        .legal-block:last-child { margin-bottom: 0; }
        .legal-tag {
            display: inline-block; font-size: 0.72rem; text-transform: uppercase;
            letter-spacing: 3px; color: var(--bordeaux); font-weight: 600;
            margin-bottom: 14px; border-bottom: 1px solid rgba(164,28,43,0.3); padding-bottom: 5px;
        }
        .legal-block h2 { font-size: 1.9rem; color: var(--anthracite); margin-bottom: 18px; line-height: 1.25; }
        .legal-block h3 { font-size: 1.15rem; color: var(--anthracite); margin: 22px 0 8px; }
        .legal-block p, .legal-block li { font-size: 1rem; color: #555; line-height: 1.8; }
        .legal-block p { margin-bottom: 14px; }
        .legal-block ul { padding-left: 22px; margin-bottom: 14px; }
        .legal-block li { margin-bottom: 6px; }
        .legal-block a { color: var(--bordeaux); }
        .legal-block a:hover { text-decoration: underline; }
        .legal-meta { background: var(--white); border: 1px solid rgba(164,28,43,0.10); border-radius: 12px; padding: 28px 32px; }
        .legal-meta p { margin-bottom: 8px; }
        .placeholder { background: #FFF6D6; color: #8a6d00; padding: 1px 7px; border-radius: 4px; font-size: 0.85em; font-weight: 600; }

        /* Encart financement */
        .funding-box {
            background: linear-gradient(180deg, #FFFFFF 0%, var(--sand) 100%);
            border: 1px solid rgba(164,28,43,0.12);
            border-radius: 14px; padding: 36px 36px 30px;
            margin-top: 10px;
        }
        .funding-logos { display: flex; gap: 26px; align-items: center; flex-wrap: wrap; margin-top: 22px; }
        .funding-logos img { height: 64px; width: auto; object-fit: contain; }

        /* ===== Footer ===== */
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
        }
        @media (max-width: 992px) {
            header.scrolled { padding: 10px 5%; }
            nav ul { display: none; }
        }
        @media (max-width: 768px) {
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

    <section class="legal-hero">
        <div class="legal-hero-inner" data-aos="fade-up">
            <div class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Accueil</a> &nbsp;/&nbsp; <span>Mentions légales</span></div>
            <h1 class="legal-title">Mentions <span>légales</span></h1>
            <p class="legal-subtitle">Informations légales, politique de confidentialité et mentions de financement relatives au site de l'Écurie de Nira.</p>
        </div>
    </section>

    <main class="legal-content">

        <div class="legal-block" data-aos="fade-up">
            <span class="legal-tag">Éditeur du site</span>
            <h2>Identité</h2>
            <div class="legal-meta">
                <p><strong>Écurie de Nira</strong> — Margaux Duchemin</p>
                <p>609 route de Deauville, 14800 Bonneville-sur-Touques, France</p>
                <p>Téléphone : <a href="tel:+33674572819">06 74 57 28 19</a></p>
                <p>E-mail : <a href="mailto:contact@ecuriedenira.fr">contact@ecuriedenira.fr</a></p>
                <p>Forme juridique : <span class="placeholder">[À COMPLÉTER]</span></p>
                <p>SIRET : <span class="placeholder">[À COMPLÉTER]</span></p>
                <p>N° de TVA intracommunautaire : <span class="placeholder">[À COMPLÉTER (si applicable)]</span></p>
                <p>Directeur de la publication : Margaux Duchemin</p>
            </div>
        </div>

        <div class="legal-block" data-aos="fade-up">
            <span class="legal-tag">Hébergement</span>
            <h2>Hébergeur du site</h2>
            <p>Le site est hébergé par : <span class="placeholder">[À COMPLÉTER — nom de l'hébergeur]</span>, <span class="placeholder">[adresse]</span>, <span class="placeholder">[téléphone]</span>.</p>
        </div>

        <div class="legal-block" data-aos="fade-up">
            <span class="legal-tag">Financement</span>
            <h2>Aide de la Région Normandie et de l'Union européenne</h2>
            <div class="funding-box">
                <p>Le projet <strong>« Amélioration de la structure – Écurie de Nira »</strong> a bénéficié d'une aide financière octroyée par la <strong>Région Normandie</strong> et l'<strong>Union européenne</strong>.</p>
                <p style="margin-bottom:0;">L'Écurie de Nira remercie ses partenaires institutionnels pour leur soutien dans le développement et l'amélioration de ses infrastructures.</p>
                <div class="funding-logos">
                    <img src="<?php echo esc_url( $IMG['partner_2'] ); ?>" alt="Région Normandie">
                    <img src="<?php echo esc_url( $IMG['partner_3'] ); ?>" alt="Union européenne">
                    <img src="<?php echo esc_url( $IMG['partner_1'] ); ?>" alt="Label EquuRES">
                </div>
            </div>
        </div>

        <div class="legal-block" data-aos="fade-up">
            <span class="legal-tag">Propriété intellectuelle</span>
            <h2>Droits d'auteur</h2>
            <p>L'ensemble des contenus présents sur ce site (textes, photographies, logos, éléments graphiques, structure) est, sauf mention contraire, la propriété de l'Écurie de Nira ou de ses partenaires. Toute reproduction, représentation, modification ou diffusion, totale ou partielle, sans autorisation préalable écrite, est interdite et constituerait une contrefaçon sanctionnée par le Code de la propriété intellectuelle.</p>
            <p>Les logos de la Région Normandie, de l'Union européenne et du label EquuRES restent la propriété de leurs détenteurs respectifs et sont utilisés dans le cadre des obligations de publicité liées au financement reçu.</p>
        </div>

        <div class="legal-block" data-aos="fade-up">
            <span class="legal-tag">Données personnelles</span>
            <h2>Politique de confidentialité</h2>
            <p>Les informations recueillies via les formulaires du site (contact, réservation) font l'objet d'un traitement destiné à répondre à vos demandes, gérer vos réservations et assurer le suivi de la relation client. Le responsable du traitement est l'Écurie de Nira.</p>
            <h3>Destinataires des données</h3>
            <p>Les données sont destinées à l'Écurie de Nira. Les données de paiement sont traitées de manière sécurisée par notre prestataire <strong>Stripe</strong> ; aucune donnée bancaire n'est conservée sur ce site.</p>
            <h3>Durée de conservation</h3>
            <p>Les données sont conservées pendant la durée nécessaire au traitement de votre demande, puis archivées conformément aux obligations légales.</p>
            <h3>Vos droits</h3>
            <p>Conformément au Règlement général sur la protection des données (RGPD) et à la loi « Informatique et Libertés », vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation, de portabilité et d'opposition sur vos données. Vous pouvez exercer ces droits en écrivant à <a href="mailto:contact@ecuriedenira.fr">contact@ecuriedenira.fr</a>. Vous pouvez également introduire une réclamation auprès de la CNIL (<a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>).</p>
        </div>

        <div class="legal-block" data-aos="fade-up">
            <span class="legal-tag">Cookies</span>
            <h2>Gestion des cookies</h2>
            <p>Ce site peut déposer des cookies nécessaires à son bon fonctionnement ainsi que, le cas échéant, des cookies de mesure d'audience. Vous pouvez configurer votre navigateur pour accepter ou refuser les cookies. Le refus de certains cookies peut limiter l'accès à certaines fonctionnalités.</p>
        </div>

        <div class="legal-block" data-aos="fade-up">
            <span class="legal-tag">Responsabilité</span>
            <h2>Limitation de responsabilité</h2>
            <p>L'Écurie de Nira s'efforce d'assurer l'exactitude des informations diffusées sur ce site mais ne saurait être tenue responsable des erreurs, omissions ou d'une indisponibilité temporaire. Les liens externes éventuels n'engagent pas la responsabilité de l'Écurie de Nira quant à leur contenu.</p>
        </div>

    </main>

    <footer id="contact-footer" style="background-color: var(--white); position: relative; padding-top: 40px;">
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
                <div style="font-size: 0.8rem; color: #888;">Copyright © <?php echo esc_html( $annee ); ?> Ecurie de Nira</div>
                <div style="font-size: 0.8rem; color: #888;">Créé par NOK'S Consulting</div>
            </div>
        </div>

        <a href="#header" class="back-to-top" id="backToTopBtn"><i class="fa-solid fa-chevron-up"></i></a>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        var LOGO_WHITE = <?php echo wp_json_encode( $IMG['logo_white'] ); ?>;
        var LOGO_DARK  = <?php echo wp_json_encode( $IMG['logo_dark'] ); ?>;

        if (typeof AOS !== 'undefined') { AOS.init({ duration: 1000, once: true }); }

        window.onscroll = function() {
            var header = document.getElementById('header');
            var logo = document.getElementById('logoImg');
            if (!header || !logo) return;
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

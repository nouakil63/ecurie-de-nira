<?php
/**
 * Template Name: Contact (Nira)
 * Surcharge des images via options WP (clé : nira_contact_<key>).
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
    'logo_white'    => nira_img( 'contact', 'logo_white',    'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'     => nira_img( 'contact', 'logo_dark',     'logo-OK-VECTO.png',        $IMG_BASE ),
    'hero_bg'       => nira_img( 'contact', 'hero_bg',       'IMG_6526-scaled.jpeg',     $IMG_BASE ),
    'footer_logo'   => nira_img( 'contact', 'footer_logo',   'logo-OK-VECTO.png',        $IMG_BASE ),
    'partner_1'     => nira_img( 'contact', 'partner_1',     'macaron-engagement-1-150x150-1.png', $IMG_BASE ),
    'partner_2'     => nira_img( 'contact', 'partner_2',     'region-normandie.png',     $IMG_BASE ),
    'partner_3'     => nira_img( 'contact', 'partner_3',     'union-europeenne.png',     $IMG_BASE ),
];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( get_the_title() ); ?> | <?php bloginfo( 'name' ); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- VARIABLES DE MARQUE --- */
        :root {
            --bordeaux: #8B1A24;
            --anthracite: #2C2A29;
            --sand: #FAF8F5;
            --white: #FFFFFF;
            --text-light: #6C6865;
            --transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }

        /* --- BASES & RESET --- */
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

        /* --- HEADER --- */
        header {
            position: fixed; top: 0; width: 100%; z-index: 1000;
            padding: 30px 4%; display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0));
            transition: var(--transition);
        }
        header.scrolled {
            padding: 15px 4%; background: rgba(253, 251, 249, 0.98);
            backdrop-filter: blur(10px); box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .logo img { height: 60px; transition: var(--transition); }
        header.scrolled .logo img { height: 50px; }
        .header-nav-container { display: flex; align-items: center; gap: 40px; }
        nav ul { display: flex; list-style: none; gap: 25px; align-items: center; }
        nav a { color: var(--white); font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; transition: var(--transition); position: relative; }
        header.scrolled nav a { color: var(--anthracite); }
        nav a::after { content: ''; position: absolute; left: 0; bottom: -5px; width: 0; height: 1px; background-color: var(--bordeaux); transition: var(--transition); }
        nav a:hover::after { width: 100%; }
        .header-social { display: flex; gap: 12px; align-items: center; }
        .social-circle { background-color: var(--white); color: #000; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: var(--transition); }
        header.scrolled .social-circle { background-color: var(--anthracite); color: var(--white); }
        .social-circle:hover { transform: scale(1.1); }
        header.scrolled .social-circle:hover { background-color: var(--bordeaux); color: var(--white); }

        /* --- 1. HERO SECTION --- */
        .hero-section {
            position: relative; height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center; overflow: hidden;
        }
        .hero-bg {
            position: absolute; inset: 0; z-index: 1;
            background: url('<?php echo esc_url( $IMG['hero_bg'] ); ?>') center/cover no-repeat;
            transform: scale(1.05); animation: slowZoom 20s infinite alternate ease-in-out;
            background-color: #333;
        }
        @keyframes slowZoom { 0% { transform: scale(1.05); } 100% { transform: scale(1); } }
        .hero-overlay { position: absolute; inset: 0; z-index: 2; background: rgba(0,0,0,0.5); }
        .hero-content { position: relative; z-index: 3; color: var(--white); margin-top: 60px; }
        .hero-subtitle { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 5px; margin-bottom: 20px; display: block; font-weight: 500; }
        .hero-title { font-size: clamp(3.5rem, 6vw, 5.5rem); font-style: italic; font-weight: 400; line-height: 1.1; margin: 0; }

        /* --- 2. CONTACT SECTION --- */
        .contact-section {
            padding: 120px 5%;
            background-color: var(--sand);
        }
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: 1fr 1.2fr;
        }

        .contact-info-panel {
            background-color: var(--anthracite);
            color: var(--white);
            padding: 60px 50px;
            position: relative;
            overflow: hidden;
        }
        .contact-info-panel::before {
            content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px;
            border-radius: 50%; background: rgba(255,255,255,0.03);
        }
        .info-title {
            font-size: 2.5rem; color: var(--white); margin-bottom: 20px; line-height: 1.2;
        }
        .info-desc {
            font-size: 1rem; color: rgba(255,255,255,0.7); margin-bottom: 50px; font-weight: 300; line-height: 1.8;
        }
        .info-list {
            list-style: none;
        }
        .info-list li {
            display: flex; align-items: flex-start; gap: 20px; margin-bottom: 30px;
        }
        .info-list i {
            font-size: 1.2rem; color: var(--bordeaux); margin-top: 5px;
        }
        .info-list h5 {
            font-family: 'Inter', sans-serif; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; color: rgba(255,255,255,0.5); margin-bottom: 5px;
        }
        .info-list p, .info-list a {
            font-size: 1.05rem; color: var(--white); font-weight: 400; transition: var(--transition);
        }
        .info-list a:hover {
            color: var(--bordeaux);
        }

        .contact-form-panel {
            padding: 60px 50px;
            background: var(--white);
        }
        .form-header h3 {
            font-size: 2rem; color: var(--anthracite); margin-bottom: 10px;
        }
        .form-header p {
            font-size: 0.95rem; color: var(--text-light); margin-bottom: 40px; font-weight: 300;
        }

        .form-group {
            margin-bottom: 25px;
        }
        .form-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;
        }
        .form-label {
            display: block; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--anthracite); margin-bottom: 8px; font-weight: 600;
        }
        .form-control {
            width: 100%; padding: 15px 20px; background-color: var(--sand); border: 1px solid rgba(0,0,0,0.05);
            border-radius: 4px; font-family: 'Inter', sans-serif; font-size: 0.95rem; color: var(--anthracite);
            transition: var(--transition);
        }
        .form-control:focus {
            outline: none; border-color: var(--bordeaux); background-color: var(--white); box-shadow: 0 5px 15px rgba(139, 26, 36, 0.05);
        }
        textarea.form-control {
            resize: vertical; min-height: 150px;
        }

        .btn-submit {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            padding: 18px 40px; background-color: var(--bordeaux); color: var(--white);
            font-family: 'Inter', sans-serif; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; font-size: 0.85rem;
            border-radius: 4px; transition: var(--transition); cursor: pointer; border: 2px solid var(--bordeaux); width: 100%;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: transparent; color: var(--bordeaux);
        }

        /* Notice succès / erreur formulaire */
        .nira-form-notice {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 18px 22px; border-radius: 6px; margin-bottom: 28px;
            font-size: 0.95rem; line-height: 1.5;
            border: 1px solid transparent;
        }
        .nira-form-notice i { font-size: 1.2rem; flex-shrink: 0; margin-top: 2px; }
        .nira-form-notice--success {
            background: rgba(46, 160, 67, 0.08); color: #1a6b2a; border-color: rgba(46, 160, 67, 0.25);
        }
        .nira-form-notice--success i { color: #2ea043; }
        .nira-form-notice--error {
            background: rgba(164, 28, 43, 0.06); color: var(--bordeaux); border-color: rgba(164, 28, 43, 0.2);
        }
        .nira-form-notice--error i { color: var(--bordeaux); }

        /* --- 3. CARTE / LOCALISATION --- */
        .map-section {
            width: 100%; height: 50vh; min-height: 400px; filter: grayscale(100%) contrast(1.1); transition: var(--transition);
        }
        .map-section:hover {
            filter: grayscale(0%);
        }
        .map-section iframe {
            width: 100%; height: 100%; border: none; display: block;
        }

        /* --- FOOTER --- */
        footer { background-color: var(--white); padding-top: 80px; }
        .footer-grid { max-width: 1400px; margin: 0 auto; padding: 0 5% 60px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 60px; }
        .footer-logo img { height: 70px; margin-bottom: 20px; }
        .footer-text { font-size: 0.9rem; color: var(--text-light); line-height: 1.6; }
        .footer-title { font-size: 1.6rem; font-style: italic; color: var(--bordeaux); margin-bottom: 25px; }
        .footer-contact-list { list-style: none; display: flex; flex-direction: column; gap: 15px; }
        .footer-contact-list li { display: flex; gap: 15px; color: var(--anthracite); font-size: 0.95rem; }
        .footer-contact-list i { color: var(--bordeaux); margin-top: 4px; }
        .footer-bottom { border-top: 1px solid rgba(0,0,0,0.05); padding: 25px 5%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; max-width: 1400px; margin: 0 auto; font-size: 0.8rem; color: #888; }

        /* --- RESPONSIVE --- */
        @media (max-width: 1024px) {
            header .header-nav-container { gap: 20px; }
            nav ul { gap: 15px; }
            nav a { font-size: 0.75rem; }
            .contact-container { grid-template-columns: 1fr; }
            .contact-info-panel { padding: 50px 5%; }
            .contact-form-panel { padding: 50px 5%; }
        }
        @media (max-width: 768px) {
            header { padding: 15px 5%; }
            nav ul { display: none; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .hero-section { height: 50vh; }
            .footer-bottom { flex-direction: column; text-align: center; }
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

    <section class="hero-section">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content" data-aos="fade-up">
            <span class="hero-subtitle">Nous rencontrer</span>
            <h1 class="hero-title"><span>Contact</span></h1>
        </div>
    </section>

    <section class="contact-section">
        <div class="contact-container" data-aos="fade-up">

            <div class="contact-info-panel">
                <h2 class="info-title">Discutons de<br>votre projet.</h2>
                <p class="info-desc">Que ce soit pour une demande de pension, un séjour dans nos gîtes ou des renseignements sur la valorisation de votre cheval, nous sommes à votre écoute.</p>

                <ul class="info-list">
                    <li>
                        <i class="fa-solid fa-location-dot"></i>
                        <div>
                            <h5>Domaine de Nira</h5>
                            <p>609 route de deauville<br>14800 Bonneville-sur-Touques, France</p>
                        </div>
                    </li>
                    <li>
                        <i class="fa-solid fa-phone"></i>
                        <div>
                            <h5>Par téléphone</h5>
                            <a href="tel:+33674572819">06 74 57 28 19</a>
                        </div>
                    </li>
                    <li>
                        <i class="fa-solid fa-envelope"></i>
                        <div>
                            <h5>Par email</h5>
                            <a href="mailto:contact@ecuriedenira.fr">contact@ecuriedenira.fr</a>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="contact-form-panel" id="contact-form">
                <div class="form-header">
                    <h3>Envoyez un message</h3>
                    <p>Remplissez le formulaire ci-dessous, Margaux Duchemin vous répondra dans les plus brefs délais.</p>
                </div>

                <?php
                $nira_msg = isset( $_GET['nira_msg'] ) ? sanitize_key( $_GET['nira_msg'] ) : '';
                if ( $nira_msg === 'sent' ) : ?>
                    <div class="nira-form-notice nira-form-notice--success" role="status">
                        <i class="fa-solid fa-circle-check"></i>
                        Merci, votre message a bien été envoyé. Margaux vous répondra rapidement.
                    </div>
                <?php elseif ( $nira_msg === 'invalid' ) : ?>
                    <div class="nira-form-notice nira-form-notice--error" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Merci de vérifier votre nom, votre email et votre message.
                    </div>
                <?php elseif ( $nira_msg === 'nonce' || $nira_msg === 'error' ) : ?>
                    <div class="nira-form-notice nira-form-notice--error" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Une erreur est survenue. Merci de réessayer ou de nous contacter par téléphone.
                    </div>
                <?php endif; ?>

                <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" novalidate>
                    <input type="hidden" name="action" value="<?php echo esc_attr( Nira_Contact::ACTION ); ?>">
                    <?php wp_nonce_field( Nira_Contact::ACTION, Nira_Contact::NONCE ); ?>
                    <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                        <label>Site web<input type="text" name="nira_website" tabindex="-1" autocomplete="off"></label>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="nira_name">Nom complet</label>
                            <input id="nira_name" name="nira_name" type="text" class="form-control" placeholder="Ex: Jean Dupont" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="nira_phone">Téléphone</label>
                            <input id="nira_phone" name="nira_phone" type="tel" class="form-control" placeholder="Votre numéro">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nira_email">Adresse Email</label>
                        <input id="nira_email" name="nira_email" type="email" class="form-control" placeholder="votre@email.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nira_subject">Sujet de votre demande</label>
                        <select id="nira_subject" name="nira_subject" class="form-control" required>
                            <option value="" disabled selected>Choisissez une option...</option>
                            <option value="Pension pour cheval">Pension pour cheval</option>
                            <option value="Réservation Gîte / Airbnb">Réservation Gîte / Airbnb</option>
                            <option value="Balnéothérapie">Balnéothérapie</option>
                            <option value="Débourrage / Valorisation">Débourrage / Valorisation</option>
                            <option value="Autre demande">Autre demande</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nira_message">Votre message</label>
                        <textarea id="nira_message" name="nira_message" class="form-control" placeholder="Décrivez vos besoins..." required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">Envoyer la demande</button>
                </form>
            </div>

        </div>
    </section>

    <section class="map-section" data-aos="fade-in">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d20893.3039868779!2d0.0935570535359998!3d49.33649479340911!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e1cd82b4676161%3A0x40c14484fbccba0!2s14800%20Bonneville-sur-Touques!5e0!3m2!1sfr!2sfr!4v1712836200000!5m2!1sfr!2sfr"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </section>

    <footer id="contact-footer">
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
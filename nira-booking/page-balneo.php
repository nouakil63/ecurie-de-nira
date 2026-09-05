<?php
/**
 * Template Name: Balnéothérapie (Nira)
 * Surcharge des images via options WP (clé : nira_balneo_<key>).
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
    'logo_white'   => nira_img( 'balneo', 'logo_white',   'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'    => nira_img( 'balneo', 'logo_dark',    'logo-OK-VECTO.png',        $IMG_BASE ),
    'hero_bg'      => nira_img( 'balneo', 'hero_bg',      'IMG_2533-scaled.jpg',      $IMG_BASE ),
    'collage_1'    => nira_img( 'balneo', 'collage_1',    'IMG_2459-scaled.jpg',      $IMG_BASE ),
    'collage_2'    => nira_img( 'balneo', 'collage_2',    'new.jpg',                  $IMG_BASE ),
    'collage_3'    => nira_img( 'balneo', 'collage_3',    '20260305-Deauville-LucieJOUR-5.jpeg', $IMG_BASE ),
    'article_1'    => nira_img( 'balneo', 'article_1',    'adc09e0b-95d0-4042-8d1f-f670ac7e2456v2.jpg', $IMG_BASE ),
    'article_2'    => nira_img( 'balneo', 'article_2',    'dfbbe465-6d40-4dfc-b6d0-a7aab2ed805c-600x600.jpeg', $IMG_BASE ),
    'footer_logo'  => nira_img( 'balneo', 'footer_logo',  'logo-OK-VECTO.png',        $IMG_BASE ),
    'partner_1'    => nira_img( 'balneo', 'partner_1',    'macaron-engagement-1-150x150-1.png', $IMG_BASE ),
    'partner_2'    => nira_img( 'balneo', 'partner_2',    'region-normandie.png',     $IMG_BASE ),
    'partner_3'    => nira_img( 'balneo', 'partner_3',    'union-europeenne.png',     $IMG_BASE ),
];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( get_the_title() ); ?> | <?php bloginfo( 'name' ); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,500;0,700;1,400;1,500&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bordeaux: #8B1A24;
            --anthracite: #1C1B1A;
            --sand: #F7F5F0;
            --white: #FFFFFF;
            --text-light: #5A5856;
            --transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--sand);
            color: var(--anthracite);
            overflow-x: hidden;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }

        header {
            position: fixed; top: 0; width: 100%; z-index: 1000;
            padding: 30px 4%; display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0));
            transition: var(--transition);
        }
        header.scrolled {
            padding: 15px 4%;
            background: rgba(247, 245, 240, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
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
        .social-circle {
            background-color: var(--white); color: #000;
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; transition: var(--transition);
        }
        header.scrolled .social-circle { background-color: var(--anthracite); color: var(--white); }
        .social-circle:hover { transform: scale(1.1); }
        header.scrolled .social-circle:hover { background-color: var(--bordeaux); color: var(--white); }

        .hero-cinematic {
            position: relative; height: 85vh; display: flex; align-items: center; justify-content: center; overflow: hidden;
        }
        .hero-bg {
            position: absolute; inset: 0; z-index: 1;
            background: url('<?php echo esc_url( $IMG['hero_bg'] ); ?>') center/cover no-repeat;
            transform: scale(1.05); animation: slowZoom 20s infinite alternate ease-in-out;
        }
        @keyframes slowZoom { 0% { transform: scale(1.05); } 100% { transform: scale(1); } }
        .hero-overlay { position: absolute; inset: 0; z-index: 2; background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.6)); }
        .hero-content { position: relative; z-index: 3; color: var(--white); text-align: center; margin-top: 50px; }
        .hero-content h1 { font-size: clamp(4rem, 8vw, 7rem); font-weight: 400; line-height: 1; letter-spacing: -2px; margin-bottom: 20px; }
        .hero-subtitle { font-family: 'Inter', sans-serif; font-size: 1rem; text-transform: uppercase; letter-spacing: 5px; color: rgba(255,255,255,0.8); }

        .editorial-poem {
            padding: 160px 5%;
            background-color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .editorial-poem::before {
            content: '&';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Playfair Display', serif;
            font-size: 50vw;
            color: var(--sand);
            opacity: 0.6;
            z-index: 0;
            pointer-events: none;
        }

        .poem-container {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .poem-intro {
            text-align: center;
            margin-bottom: 100px;
        }
        .poem-intro p {
            font-family: 'Inter', sans-serif;
            font-size: 1.1rem;
            color: var(--text-light);
            font-weight: 300;
            line-height: 1.8;
            max-width: 650px;
            margin: 0 auto;
            position: relative;
            display: inline-block;
        }
        .poem-intro p::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 1px;
            background-color: rgba(139, 26, 36, 0.4);
        }

        .poem-line {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            color: var(--anthracite);
            line-height: 1;
            margin: 0;
            position: relative;
        }

        .line-1 { text-align: left; margin-bottom: 30px; }
        .line-2 { text-align: right; margin-bottom: 60px; }
        .line-3 { text-align: center; }

        .poem-text-wrap { position: relative; display: inline-block; }

        .poem-math {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 1px solid rgba(139, 26, 36, 0.3);
            font-family: 'Inter', sans-serif;
            font-size: 1.5rem;
            font-weight: 300;
            color: var(--bordeaux);
            background: var(--white);
            position: absolute;
        }

        .line-1 .poem-math { right: -65px; bottom: -45px; }
        .line-2 .poem-math { left: 50%; bottom: -55px; transform: translateX(-50%); }

        .poem-result {
            display: block;
            font-style: italic;
            color: var(--bordeaux);
            font-size: clamp(2.2rem, 4vw, 3.5rem);
            line-height: 1.3;
        }

        @media (max-width: 768px) {
            .poem-line { text-align: center; margin-bottom: 60px; white-space: normal; font-size: 2.2rem; }
            .poem-math { position: relative; right: 0; bottom: 0; left: 0; transform: none; margin: 20px auto 0; }
            .line-1 .poem-math, .line-2 .poem-math { right: auto; bottom: auto; left: auto; transform: none; display: flex; margin: 30px auto -30px; }
        }

        .magazine-services { max-width: 1400px; margin: 0 auto; padding: 60px 5% 120px; display: grid; grid-template-columns: 1fr 1.2fr; gap: 100px; align-items: center; }
        .services-menu h2 { font-size: 1rem; text-transform: uppercase; letter-spacing: 3px; color: var(--text-light); margin-bottom: 40px; border-bottom: 1px solid rgba(0,0,0,0.1); padding-bottom: 20px; }
        .service-item { padding: 25px 0; border-bottom: 1px solid rgba(0,0,0,0.1); display: flex; align-items: baseline; gap: 20px; }
        .service-item:last-child { border-bottom: none; }
        .service-item i { color: var(--bordeaux); font-size: 1.5rem; margin-top: 5px; }
        .service-item h3 { font-family: 'Inter', sans-serif; font-size: 1.8rem; font-weight: 500; color: var(--anthracite); margin-bottom: 10px; }
        .service-item p { font-size: 0.95rem; color: var(--text-light); }

        .collage-wrapper { position: relative; height: 700px; width: 100%; }
        .collage-img { position: absolute; background-color: #ddd; background-size: cover; background-position: center; box-shadow: 0 20px 40px rgba(0,0,0,0.1); transition: var(--transition); border-radius: 4px; }
        .collage-img:hover { z-index: 10 !important; transform: scale(1.05); }

        .img-1 { width: 60%; height: 50%; top: 0; left: 0; z-index: 2; background-image: url('<?php echo esc_url( $IMG['collage_1'] ); ?>'); }
        .img-2 { width: 50%; height: 45%; top: 20%; right: 0; z-index: 3; background-image: url('<?php echo esc_url( $IMG['collage_2'] ); ?>'); }
        .img-3 { width: 65%; height: 40%; bottom: 0; left: 15%; z-index: 1; background-image: url('<?php echo esc_url( $IMG['collage_3'] ); ?>'); }

        .adaptation-band {
            background-color: var(--white);
            padding: 120px 5%;
        }
        .adaptation-content {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 60px;
        }
        .adaptation-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 3.5vw, 2.8rem);
            color: var(--anthracite);
            max-width: 450px;
            line-height: 1.2;
            font-weight: 700;
        }
        .adaptation-tags {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            max-width: 550px;
        }
        .tag {
            padding: 12px 24px;
            border: 1px solid rgba(139, 26, 36, 0.7);
            color: var(--bordeaux);
            border-radius: 50px;
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 500;
            background-color: transparent;
            transition: var(--transition);
            cursor: default;
        }
        .tag:hover {
            background-color: var(--bordeaux);
            color: var(--white);
        }

        .luxury-feature {
            padding: 80px 0 160px;
            background-color: var(--white);
        }
        .luxury-feature-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 5%;
        }
        .luxury-article {
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            gap: 100px;
            align-items: center;
            margin-bottom: 160px;
        }
        .luxury-article:last-child {
            margin-bottom: 0;
        }
        .luxury-article.reverse {
            grid-template-columns: 1.1fr 1fr;
        }
        .luxury-article.reverse .article-text {
            order: 1;
        }
        .luxury-article.reverse .article-img-container {
            order: 2;
        }

        .article-img-container {
            position: relative;
            height: 600px;
            z-index: 1;
        }
        .article-img {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 4px;
            overflow: hidden;
            z-index: 2;
        }
        .article-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 1s ease;
        }
        .luxury-article:hover .article-img img {
            transform: scale(1.05);
        }
        .img-accent-frame {
            position: absolute;
            top: -25px;
            left: -25px;
            width: 100%;
            height: 100%;
            border: 1px solid var(--bordeaux);
            z-index: 1;
            opacity: 0.3;
            pointer-events: none;
            transition: var(--transition);
        }
        .luxury-article.reverse .img-accent-frame {
            left: auto;
            right: -25px;
            top: 25px;
        }
        .luxury-article:hover .img-accent-frame {
            opacity: 0.6;
            transform: translate(10px, 10px);
        }
        .luxury-article.reverse:hover .img-accent-frame {
            transform: translate(-10px, -10px);
        }

        .article-subtitle {
            font-family: 'Inter', sans-serif;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: var(--bordeaux);
            display: block;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .article-text h3 {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            color: var(--anthracite);
            line-height: 1.1;
            margin-bottom: 35px;
            font-weight: 500;
        }
        .article-text h3 span {
            font-style: italic;
            color: var(--bordeaux);
            font-weight: 400;
            display: block;
        }
        .article-text p {
            font-size: 1.1rem;
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 20px;
            font-weight: 300;
        }

        .dark-expertise { background-color: var(--anthracite); color: var(--sand); padding: 120px 5%; }
        .dark-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.2fr; gap: 80px; }
        .dark-col-title { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 4px; color: var(--bordeaux); margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .diploma-block { margin-bottom: 40px; }
        .diploma-block h4 { font-size: 2rem; font-family: 'Inter', sans-serif; font-weight: 300; margin-bottom: 10px; color: var(--white); }
        .diploma-block p { font-size: 0.95rem; color: rgba(255,255,255,0.6); line-height: 1.6; max-width: 400px; }
        .sejour-intro { font-size: 1.2rem; font-family: 'Playfair Display', serif; font-style: italic; margin-bottom: 30px; color: var(--white); }
        .sejour-list { list-style: none; }
        .sejour-list li { display: flex; align-items: flex-start; gap: 15px; margin-bottom: 20px; font-size: 1.05rem; }
        .sejour-list i { color: var(--bordeaux); margin-top: 5px; }
        .sejour-options { margin-top: 40px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.9rem; color: rgba(255,255,255,0.5); }
        .sejour-options span { color: var(--bordeaux); text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; display: block; margin-bottom: 10px;}

        .minimal-pricing { padding: 120px 5%; background-color: var(--sand); text-align: center; }
        .minimal-pricing h2 { font-size: 3rem; color: var(--anthracite); margin-bottom: 80px; }
        .price-wrap { display: flex; justify-content: center; gap: 80px; flex-wrap: wrap; max-width: 1000px; margin: 0 auto 80px; }
        .price-item { flex: 1; min-width: 300px; padding: 40px; background: var(--white); border: 1px solid rgba(0,0,0,0.05); }
        .p-title { font-family: 'Inter', sans-serif; font-size: 1rem; text-transform: uppercase; letter-spacing: 3px; color: var(--text-light); margin-bottom: 20px; }
        .p-amount { font-family: 'Playfair Display', serif; font-size: 4rem; color: var(--anthracite); line-height: 1; margin-bottom: 10px; }
        .p-ht { font-size: 0.9rem; color: var(--bordeaux); }
        .call-btn { display: inline-flex; align-items: center; justify-content: center; gap: 15px; padding: 20px 50px; background-color: transparent; color: var(--anthracite); font-weight: 500; text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem; border: 1px solid var(--anthracite); transition: var(--transition); cursor: pointer; }
        .call-btn:hover { background-color: var(--anthracite); color: var(--white); }

        footer { background-color: var(--white); padding-top: 80px; border-top: 1px solid rgba(0,0,0,0.05); }
        .footer-grid { max-width: 1400px; margin: 0 auto; padding: 0 5% 60px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 60px; }
        .footer-logo img { height: 70px; margin-bottom: 20px; }
        .footer-text { font-size: 0.9rem; color: var(--text-light); line-height: 1.6; }
        .footer-title { font-size: 1.6rem; font-style: italic; color: var(--bordeaux); margin-bottom: 25px; font-weight: 700; }
        .footer-contact-list { list-style: none; display: flex; flex-direction: column; gap: 15px; }
        .footer-contact-list li { display: flex; gap: 15px; color: var(--anthracite); font-size: 0.95rem; }
        .footer-contact-list i { color: var(--bordeaux); margin-top: 4px; }
        .footer-bottom { border-top: 1px solid rgba(0,0,0,0.05); padding: 25px 5%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; max-width: 1400px; margin: 0 auto; font-size: 0.8rem; color: #888; }
        .footer-bottom a { transition: color 0.3s ease; }
        .footer-bottom a:hover { color: var(--bordeaux); }

        @media (max-width: 1024px) {
            header .header-nav-container { gap: 20px; }
            nav ul { gap: 15px; }
            nav a { font-size: 0.75rem; }
            .magazine-services { grid-template-columns: 1fr; gap: 60px; }
            .collage-wrapper { height: 500px; }

            .luxury-article, .luxury-article.reverse { grid-template-columns: 1fr; gap: 60px; margin-bottom: 100px; }
            .luxury-article .article-text, .luxury-article.reverse .article-text { order: 1; }
            .luxury-article .article-img-container, .luxury-article.reverse .article-img-container { order: 2; height: 500px; }
            .img-accent-frame { display: none; }

            .dark-grid { grid-template-columns: 1fr; gap: 60px; }
            .adaptation-content { gap: 40px; }
        }
        @media (max-width: 768px) {
            header { padding: 15px 5%; }
            nav ul { display: none; }
            .adaptation-content { flex-direction: column; text-align: center; }
            .adaptation-tags { justify-content: center; }
            .price-wrap { gap: 30px; }
            .footer-bottom { flex-direction: column; text-align: center; }
            .equation-text { font-size: 2rem; }
            .equation-text br { display: none; }
            .article-img-container { height: 400px; }
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

    <section class="hero-cinematic">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1500">
            <div class="hero-subtitle">Soin & Récupération</div>
            <h1>Balnéothérapie.</h1>
        </div>
    </section>

    <section class="editorial-poem">
        <div class="poem-container">

            <div class="poem-intro" data-aos="fade-down">
                <p>Faites profiter à votre cheval des bienfaits de la mer associés aux savoir-faire de Justine en Shiatsu et Marie en massage et algothérapie.</p>
            </div>
            <div class="poem-line line-1" data-aos="fade-right" data-aos-delay="100">
                <span class="poem-text-wrap">L'eau de mer<span class="poem-math">+</span></span>
            </div>
            <div class="poem-line line-2" data-aos="fade-left" data-aos-delay="300">
                <span class="poem-text-wrap">un nouvel environnement<span class="poem-math">=</span></span>
            </div>
            <div class="poem-line line-3" data-aos="fade-up" data-aos-delay="500">
                <span class="poem-result">Un cheval ressourcé,<br>bien dans sa tête et dans son corps.</span>
            </div>
        </div>
    </section>

    <section class="magazine-services">
        <div class="services-menu" data-aos="fade-right">
            <h2>Notre carte de soins</h2>

            <div class="service-item">
                <i class="fa-solid fa-hands"></i>
                <div>
                    <h3>Shiatsu & Massage</h3>
                    <p>Techniques manuelles pour dénouer les tensions et relancer la circulation énergétique.</p>
                </div>
            </div>

            <div class="service-item">
                <i class="fa-solid fa-leaf"></i>
                <div>
                    <h3>Algothérapie</h3>
                    <p>Application d'algues marines reminéralisantes pour les muscles et les articulations.</p>
                </div>
            </div>

            <div class="service-item">
                <i class="fa-solid fa-sun"></i>
                <div>
                    <h3>Solarium</h3>
                    <p>Chaleur infrarouge apaisante pour une préparation ou une récupération optimale.</p>
                </div>
            </div>

            <div class="service-item">
                <i class="fa-solid fa-water"></i>
                <div>
                    <h3>Sorties à la Plage</h3>
                    <p>Travail doux dans l'eau de mer à Deauville. Effet drainant et rupture de routine.</p>
                </div>
            </div>
        </div>

        <div class="collage-wrapper" data-aos="fade-left">
            <div class="collage-img img-1"></div>
            <div class="collage-img img-2"></div>
            <div class="collage-img img-3"></div>
        </div>
    </section>

    <section class="adaptation-band" data-aos="fade-up">
        <div class="adaptation-content">
            <h2 class="adaptation-title">Nous adaptons ces soins à vos objectifs.</h2>
            <div class="adaptation-tags">
                <span class="tag">Récupération</span>
                <span class="tag">Remise en forme</span>
                <span class="tag">Préparation sportive</span>
                <span class="tag">Rééducation</span>
            </div>
        </div>
    </section>

    <section class="luxury-feature">
        <div class="luxury-feature-container">

            <div class="luxury-article" data-aos="fade-up">
                <div class="article-img-container">
                    <div class="img-accent-frame"></div>
                    <div class="article-img">
                        <img src="<?php echo esc_url( $IMG['article_1'] ); ?>" alt="Soins et accompagnement">
                    </div>
                </div>
                <div class="article-text">
                    <span class="article-subtitle">Approche individuelle</span>
                    <h3>Un accompagnement <span>sur-mesure.</span></h3>
                    <p>Chaque cheval possède une physiologie et un historique uniques. Avant de débuter une cure, nous prenons le temps d'évaluer ses besoins spécifiques en concertation avec vous et vos professionnels de santé (vétérinaire, ostéopathe).</p>
                    <p>Ce bilan nous permet d'établir un protocole de soins précis, alliant les bienfaits drainants de l'eau de mer aux vertus thérapeutiques de l'algothérapie et des massages manuels.</p>
                </div>
            </div>

            <div class="luxury-article reverse" data-aos="fade-up">
                <div class="article-text">
                    <span class="article-subtitle">Sérénité & Convalescence</span>
                    <h3>Le pouvoir de <span>l'apaisement.</span></h3>
                    <p>La balnéothérapie ne se limite pas à la récupération physique. C'est également une véritable bulle de décompression mentale pour les chevaux soumis à l'effort intensif ou en convalescence.</p>
                    <p>Le calme de nos installations, associé à la chaleur réconfortante de notre solarium et à l'attention constante de notre équipe, garantit une rupture bénéfique avec le stress quotidien de la compétition.</p>
                </div>
                <div class="article-img-container">
                    <div class="img-accent-frame"></div>
                    <div class="article-img">
                        <img src="<?php echo esc_url( $IMG['article_2'] ); ?>" alt="Bien-être et apaisement équin">
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="dark-expertise">
        <div class="dark-grid">

            <div data-aos="fade-up">
                <div class="dark-col-title">Les Expertes</div>

                <div class="diploma-block">
                    <h4>Marie.</h4>
                    <p>Diplômée de la formation Equiphysio (Formation professionnelle supérieure en massage animalier) & spécialisée en algothérapie.</p>
                </div>

                <div class="diploma-block">
                    <h4>Justine.</h4>
                    <p>Diplômée de l'école de Shiatsu Équin Pôle Normandie. Spécialiste de l'apaisement par la circulation énergétique.</p>
                </div>
            </div>

            <div data-aos="fade-up" data-aos-delay="100">
                <div class="dark-col-title">Le Séjour</div>
                <div class="sejour-intro">Une attention portée sur l'équilibre physique et mental de votre cheval.</div>

                <ul class="sejour-list">
                    <li><i class="fa-solid fa-check"></i> Sortie quotidienne garantie dans de grands paddocks individuels (1500 à 2000m²).</li>
                    <li><i class="fa-solid fa-check"></i> Foin de très haute qualité distribué à volonté au box.</li>
                    <li><i class="fa-solid fa-check"></i> Alimentation premium de la marque Lambey.</li>
                </ul>

                <div class="sejour-options">
                    <span>Options complémentaires</span>
                    Nébulisation disponible pour les chevaux emphysémateux.<br>
                    Utilisation de guêtres Ice Vibe sur demande.
                </div>
            </div>

        </div>
    </section>

    <section class="minimal-pricing" id="tarifs">
        <h2 data-aos="fade-up">Investir dans le bien-être.</h2>

        <div class="price-wrap" data-aos="fade-up" data-aos-delay="100">
            <div class="price-item">
                <div class="p-title">Cure à la semaine</div>
                <div class="p-amount">730€</div>
                <div class="p-ht">Soit 608€ HT</div>
            </div>

            <div class="price-item">
                <div class="p-title">Cure au mois</div>
                <div class="p-amount">1980€</div>
                <div class="p-ht">Soit 1650€ HT</div>
            </div>
        </div>

        <a href="tel:+33674572819" class="call-btn" data-aos="fade-up" data-aos-delay="200">
            <i class="fa-solid fa-phone"></i> Réserver au 06 74 57 28 19
        </a>
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

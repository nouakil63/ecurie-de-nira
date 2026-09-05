<?php
/**
 * Template Name: Accueil (Nira)
 *
 * Page d'accueil livrée par le plugin Nira Booking.
 * Surcharge des images via options WP (clé : nira_accueil_<key>).
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
    'logo_white'     => nira_img( 'accueil', 'logo_white',     'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'      => nira_img( 'accueil', 'logo_dark',      'logo-OK-VECTO.png', $IMG_BASE ),
    'hero_bg'        => nira_img( 'accueil', 'hero_bg',        'IMG_6526-scaled.jpeg', $IMG_BASE ),
    'ecurie_large'   => nira_img( 'accueil', 'ecurie_large',   '20260305-Deauville-LucieJOUR-6-scaled.jpeg', $IMG_BASE ),
    'ecurie_small'   => nira_img( 'accueil', 'ecurie_small',   'a-mettre-pour-valorisation-et-debourrage-1.jpg', $IMG_BASE ),
    'pension_box'    => nira_img( 'accueil', 'pension_box',    'IMG_1872.jpeg', $IMG_BASE ),
    'pension_travail'=> nira_img( 'accueil', 'pension_travail','IMG_7770.jpeg', $IMG_BASE ),
    'pension_passage'=> nira_img( 'accueil', 'pension_passage','IMG_5156.jpeg', $IMG_BASE ),
    'situation_large'=> nira_img( 'accueil', 'situation_large','IMG_5156.jpeg', $IMG_BASE ),
    'situation_small'=> nira_img( 'accueil', 'situation_small','IMG_7770-scaled.jpeg', $IMG_BASE ),
    'label_1'        => nira_img( 'accueil', 'label_1',        'macaron-engagement-1-150x150-1.png', $IMG_BASE ),
    'label_2'        => nira_img( 'accueil', 'label_2',        'EquuRES241220-Certificat-Engagement-Les-Ecuries-de-Nira-1-300x212-1.jpg', $IMG_BASE ),
    'label_3'        => nira_img( 'accueil', 'label_3',        'union-europeenne.png', $IMG_BASE ),
    'gallery_1'      => nira_img( 'accueil', 'gallery_1',      '385320245_290035883964491_4208930849250831231_n-1024x768-1.jpg', $IMG_BASE ),
    'gallery_2'      => nira_img( 'accueil', 'gallery_2',      '385037290_290035957297817_8425971591010244386_n.jpg', $IMG_BASE ),
    'gallery_3'      => nira_img( 'accueil', 'gallery_3',      'IMG_6516-scaled.jpeg', $IMG_BASE ),
    'gallery_4'      => nira_img( 'accueil', 'gallery_4',      'dfbbe465-6d40-4dfc-b6d0-a7aab2ed805c-600x600-2.jpeg', $IMG_BASE ),
    'gallery_5'      => nira_img( 'accueil', 'gallery_5',      'IMG_6527-scaled.jpeg', $IMG_BASE ),
    'partner_1'      => nira_img( 'accueil', 'partner_1',      'macaron-engagement-1-150x150-1.png', $IMG_BASE ),
    'partner_2'      => nira_img( 'accueil', 'partner_2',      'region-normandie.png', $IMG_BASE ),
    'partner_3'      => nira_img( 'accueil', 'partner_3',      'union-europeenne.png', $IMG_BASE ),
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
            --transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body.nira-accueil-body { font-family: 'Inter', sans-serif; background-color: var(--sand); color: var(--anthracite); overflow-x: hidden; line-height: 1.6; }
        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; }
        a { text-decoration: none; color: inherit; transition: var(--transition); }
        img { max-width: 100%; display: block; }
        ul { list-style: none; }

        .nira-header {
            position: fixed; top: 0; left: 0; width: 100%; z-index: 1000;
            padding: 30px 4%; display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6), rgba(0,0,0,0));
            transition: var(--transition);
        }
        body.admin-bar .nira-header { top: 32px; }
        .nira-header.scrolled { padding: 15px 4%; background: rgba(253, 251, 249, 0.98); backdrop-filter: blur(10px); box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .logo img { height: 60px; width: auto; transition: var(--transition); }
        .nira-header.scrolled .logo img { height: 50px; }
        .header-nav-container { display: flex; align-items: center; gap: 40px; }
        nav ul { display: flex; gap: 25px; align-items: center; }
        nav a { color: var(--white); font-size: 0.85rem; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; transition: var(--transition); font-family: 'Inter', sans-serif; }
        .nira-header.scrolled nav a { color: var(--anthracite); }
        nav a:hover { color: #ccc; }
        .nira-header.scrolled nav a:hover { color: var(--bordeaux); }
        .header-social { display: flex; gap: 12px; align-items: center; }
        .social-circle { background-color: var(--white); color: #000; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: var(--transition); }
        .nira-header.scrolled .social-circle { background-color: var(--anthracite); color: var(--white); }
        .social-circle:hover { transform: scale(1.1); }
        .nira-header.scrolled .social-circle:hover { background-color: var(--bordeaux); }

        .hero-immersif-premium { height: 100vh; min-height: 760px; position: relative; overflow: hidden; display: flex; align-items: center; }
        .hero-bg { position: absolute; inset: 0; z-index: 1; background: url('<?php echo esc_url( $IMG['hero_bg'] ); ?>') center center / cover no-repeat; animation: niraKenBurns 20s infinite alternate; transform-origin: center center; }
        @keyframes niraKenBurns { 0% { transform: scale(1); } 100% { transform: scale(1.08); } }
        .hero-overlay { position: absolute; inset: 0; z-index: 2; background: linear-gradient(to right, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.20) 58%, rgba(0,0,0,0.35) 100%); }
        .hero-bg-text { position: absolute; left: -5%; bottom: -10%; font-family: 'Playfair Display', serif; font-size: 40vw; font-weight: 700; color: rgba(255,255,255,0.03); -webkit-text-stroke: 1px rgba(255,255,255,0.08); letter-spacing: -2vw; z-index: 1; white-space: nowrap; pointer-events: none; line-height: 1; }
        .hero-content { position: relative; z-index: 3; width: 90%; max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1.2fr 1fr; color: var(--white); }
        .hero-text-block { max-width: 680px; }
        .hero-badge { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; color: var(--white); opacity: 0.85; text-transform: uppercase; letter-spacing: 4px; font-size: 0.8rem; font-family: 'Inter', sans-serif; }
        .hero-badge span { width: 40px; height: 1px; background-color: rgba(255,255,255,0.5); }
        .hero-title { font-size: clamp(4rem, 8vw, 6.5rem); line-height: 0.95; margin-bottom: 30px; font-weight: 700; color: var(--white); }
        .hero-title span { font-style: italic; font-weight: 400; font-family: 'Playfair Display', serif; }
        .hero-body { font-size: 1.2rem; color: #EEE; font-weight: 300; line-height: 1.8; margin-bottom: 50px; max-width: 720px; font-family: 'Inter', sans-serif; }
        .btn-premium { display: inline-flex; align-items: center; gap: 15px; padding: 20px 45px; background-color: var(--bordeaux); color: var(--white); border-radius: 100px; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 2px; font-weight: 600; border: 2px solid var(--bordeaux); transition: var(--transition); box-shadow: 0 5px 20px rgba(164, 28, 43, 0.3); font-family: 'Inter', sans-serif; }
        .btn-premium:hover { background-color: transparent; color: var(--white); border-color: var(--white); transform: translateY(-5px); box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1); }
        .hero-gps { position: absolute; bottom: 40px; left: 5%; color: var(--white); font-size: 0.75rem; letter-spacing: 3px; font-weight: 300; opacity: 0.6; z-index: 3; text-transform: uppercase; font-family: 'Inter', sans-serif; }
        .hero-scroll { position: absolute; bottom: 40px; left: 50%; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; gap: 15px; color: var(--white); font-size: 0.7rem; font-weight: 300; text-transform: uppercase; letter-spacing: 2px; opacity: 0.8; z-index: 3; font-family: 'Inter', sans-serif; }
        .hero-scroll i { font-size: 1.2rem; animation: niraScrollDown 2s infinite ease-in-out; }
        @keyframes niraScrollDown { 0% { transform: translateY(0); opacity: 0; } 30% { opacity: 1; } 70% { opacity: 1; } 100% { transform: translateY(15px); opacity: 0; } }
        .hero-transition-wave { position: absolute; bottom: -2px; left: 0; width: 100%; height: 80px; z-index: 4; fill: var(--sand); }

        #ecurie { padding: 140px 5%; background-color: var(--sand); position: relative; overflow: hidden; }
        .ecurie-deco { position: absolute; top: 10%; right: -5%; width: 300px; height: 300px; border-radius: 50%; background: rgba(164, 28, 43, 0.03); z-index: 1; }
        .ecurie-grid { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 100px; align-items: center; position: relative; z-index: 2; }
        .ecurie-visual { position: relative; height: 600px; }
        .ecurie-image-large { position: absolute; top: 50%; left: 0; transform: translateY(-50%); width: 90%; height: 350px; border-radius: 4px; overflow: hidden; box-shadow: 20px 20px 60px rgba(0,0,0,0.08); }
        .ecurie-image-large img { width: 100%; height: 100%; object-fit: cover; }
        .ecurie-image-large::after { content: ""; position: absolute; inset: 0; background-color: var(--bordeaux); opacity: 0.05; }
        .ecurie-image-small { position: absolute; top: 0; right: 0; width: 45%; height: 550px; border: 15px solid var(--white); border-radius: 4px; overflow: hidden; box-shadow: 10px 10px 40px rgba(0,0,0,0.12); }
        .ecurie-image-small img { width: 100%; height: 100%; object-fit: cover; }
        .section-topline { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .section-topline span { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 4px; color: var(--bordeaux); font-family: 'Inter', sans-serif; }
        .section-topline div { flex-grow: 1; height: 1px; background: rgba(164, 28, 43, 0.15); }
        .section-title { font-size: clamp(2.5rem, 4vw, 3.5rem); color: var(--anthracite); line-height: 1.2; margin-bottom: 35px; font-weight: 700; }
        .section-title em { font-family: 'Playfair Display', serif; font-weight: 400; font-style: italic; color: var(--bordeaux); }
        .section-text { color: #666; font-size: 1.1rem; line-height: 1.8; margin-bottom: 50px; font-weight: 300; font-family: 'Inter', sans-serif; }
        .section-text p { margin-bottom: 25px; }
        .mini-features { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 60px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 40px; }
        .mini-feature { display: flex; align-items: center; gap: 15px; }
        .mini-feature-icon { font-size: 1.5rem; color: var(--bordeaux); }
        .mini-feature h4 { font-family: 'Inter', sans-serif; font-size: 1.3rem; color: var(--anthracite); font-weight: 600; margin-bottom: 0; }
        .mini-feature p { font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 2px; font-family: 'Inter', sans-serif; }

        #pensions { padding: 140px 5%; background-color: var(--white); overflow: hidden; }
        .container-xl { max-width: 1400px; margin: 0 auto; }
        .section-center { text-align: center; margin-bottom: 80px; }
        .eyebrow { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 5px; color: var(--bordeaux); margin-bottom: 20px; font-family: 'Inter', sans-serif; }
        .section-center h2 { font-size: clamp(2.2rem, 3.5vw, 3rem); color: var(--anthracite); line-height: 1.2; }
        .section-center h2 em { font-style: italic; font-weight: 400; }
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 30px; }
        .card-premium { position: relative; height: 500px; background: var(--sand); border-radius: 4px; overflow: hidden; display: flex; align-items: flex-end; transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1); cursor: pointer; }
        .card-bg { position: absolute; inset: 0; background-size: cover; background-position: center; filter: grayscale(100%); opacity: 0.1; transition: all 0.8s ease; transform: scale(1); }
        .card-content { position: relative; z-index: 2; padding: 50px; width: 100%; }
        .card-number { font-family: 'Playfair Display', serif; font-size: 4rem; font-weight: 700; color: var(--anthracite); opacity: 0.05; position: absolute; top: 20px; right: 30px; line-height: 1; }
        .card-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; margin-bottom: 20px; color: var(--anthracite); }
        .card-text { color: #777; margin-bottom: 30px; max-height: 0; opacity: 0; overflow: hidden; transition: all 0.5s ease; font-family: 'Inter', sans-serif; }
        .card-list { margin-bottom: 30px; opacity: 0.8; }
        .card-list li { margin-bottom: 10px; display: flex; align-items: center; gap: 10px; font-size: 0.9rem; font-family: 'Inter', sans-serif; }
        .card-btn { display: inline-block; padding: 12px 25px; background: var(--bordeaux); color: var(--white); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; border-radius: 2px; transform: translateY(20px); opacity: 0; transition: all 0.5s ease; font-family: 'Inter', sans-serif; }
        .card-premium.active { background: var(--anthracite); }
        .card-premium:hover { transform: translateY(-10px); background: var(--anthracite); }
        .card-premium:hover .card-bg { opacity: 0.22; filter: grayscale(35%) brightness(0.75); transform: scale(1.08); }
        .card-premium:hover .card-text { max-height: 100px; opacity: 1; margin-top: 10px; color: rgba(255,255,255,0.78); }
        .card-premium:hover .card-btn { transform: translateY(0); opacity: 1; }
        .card-premium:hover .card-title { color: var(--white); }
        .card-premium:hover .card-list { color: rgba(255,255,255,0.85); opacity: 1; }
        .card-premium:hover .card-list li { color: rgba(255,255,255,0.85); }
        .card-premium:hover .card-list i { color: var(--bordeaux); }
        .card-premium:hover .card-number { color: var(--white); opacity: 0.12; }
        .btn-light { background: var(--white); color: var(--anthracite); }
        .btn-light:hover { background: var(--bordeaux); color: var(--white); }

        #situation { padding: 120px 5%; background-color: var(--white); position: relative; overflow: hidden; }
        .situation-grid { max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 100px; align-items: center; }
        .situation-header { text-align: center; margin-bottom: 80px; }
        .situation-header h2 { font-size: clamp(2.2rem, 4vw, 3.2rem); color: var(--anthracite); font-weight: 700; line-height: 1.2; }
        .situation-header h2 em { font-style: italic; color: var(--bordeaux); }
        .situation-header h3 { font-size: clamp(1.5rem, 2.5vw, 2.2rem); color: #666; font-style: italic; font-weight: 400; margin-top: 15px; }
        .situation-images { position: relative; width: 100%; padding-bottom: 100%; }
        .circle-large { position: absolute; top: 0; left: 0; width: 85%; height: 85%; border-radius: 50%; overflow: hidden; box-shadow: 15px 15px 50px rgba(0,0,0,0.08); z-index: 1; }
        .circle-small { position: absolute; bottom: 0; right: 0; width: 55%; height: 55%; border-radius: 50%; border: 15px solid var(--white); overflow: hidden; box-shadow: 10px 10px 40px rgba(0,0,0,0.15); z-index: 2; transition: transform 0.5s ease; }
        .circle-small:hover { transform: scale(1.05); }
        .circle-large img, .circle-small img { width: 100%; height: 100%; object-fit: cover; }
        .situation-lead { font-family: 'Inter', sans-serif; font-size: 1.3rem; color: var(--anthracite); font-weight: 600; margin-bottom: 40px; line-height: 1.5; }
        .situation-points { display: flex; flex-direction: column; gap: 30px; }
        .situation-point { display: flex; align-items: center; gap: 20px; }
        .icon-hover { width: 60px; height: 60px; border-radius: 50%; background: rgba(164, 28, 43, 0.05); display: flex; align-items: center; justify-content: center; color: var(--bordeaux); font-size: 1.8rem; flex-shrink: 0; transition: var(--transition); }
        .icon-hover:hover { background-color: var(--bordeaux) !important; color: var(--white) !important; transform: translateY(-5px); }
        .situation-point h4 { font-family: 'Inter', sans-serif; font-size: 0.95rem; font-weight: 700; color: var(--bordeaux); text-transform: uppercase; letter-spacing: 1px; line-height: 1.4; }
        .labels-row { margin-top: 50px; padding-top: 40px; border-top: 1px solid rgba(0,0,0,0.08); display: flex; align-items: center; gap: 40px; flex-wrap: wrap; }
        .label-img { height: 90px; filter: grayscale(100%) opacity(0.7); transition: var(--transition); cursor: pointer; width: auto; }
        .label-img:hover { filter: grayscale(0%) opacity(1); }

        #avis { padding: 120px 0; background-color: var(--white); overflow: hidden; position: relative; }
        .avis-header { text-align: center; margin-bottom: 60px; padding: 0 5%; }
        .avis-header h2 { font-size: clamp(2.2rem, 3.5vw, 3rem); color: var(--anthracite); line-height: 1.2; font-weight: 700; }
        .avis-header h2 em { font-style: italic; font-weight: 400; color: var(--bordeaux); }
        .avis-slider-container { position: relative; width: 100%; padding: 20px 0; display: flex; align-items: center; }
        .avis-fade-left, .avis-fade-right { position: absolute; top: 0; width: 15%; height: 100%; z-index: 10; pointer-events: none; }
        .avis-fade-left { left: 0; background: linear-gradient(to right, var(--white), transparent); }
        .avis-fade-right { right: 0; background: linear-gradient(to left, var(--white), transparent); }
        .avis-slider-track { display: flex; gap: 30px; width: max-content; animation: niraScrollInfinite 30s linear infinite; }
        .avis-slider-container:hover .avis-slider-track { animation-play-state: paused; }
        @keyframes niraScrollInfinite { 0% { transform: translateX(0); } 100% { transform: translateX(calc(-50% - 15px)); } }
        .avis-card { width: 380px; background: var(--sand); padding: 40px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); flex-shrink: 0; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid rgba(0,0,0,0.02); transition: var(--transition); }
        .avis-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(164, 28, 43, 0.08); }
        .avis-stars { color: var(--bordeaux); font-size: 0.9rem; margin-bottom: 20px; display: flex; gap: 5px; }
        .avis-text { font-family: 'Inter', sans-serif; font-size: 1rem; color: var(--anthracite); font-style: italic; line-height: 1.7; margin-bottom: 30px; font-weight: 300; }
        .avis-author h4 { font-family: 'Inter', sans-serif; font-size: 1rem; color: var(--anthracite); font-weight: 700; margin-bottom: 2px; }
        .avis-author span { font-family: 'Inter', sans-serif; font-size: 0.8rem; color: #888; text-transform: uppercase; letter-spacing: 1px; }

        #galerie { padding: 120px 0; background-color: var(--sand); overflow: hidden; }
        .gallery-header-wrap { max-width: 1400px; margin: 0 auto; padding: 0 5%; }
        .gallery-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 60px; gap: 30px; }
        .gallery-header-left { max-width: 600px; }
        .gallery-header-left h2 { font-size: clamp(2rem, 3vw, 2.8rem); color: var(--anthracite); line-height: 1.2; }
        .gallery-header-left h2 em { font-style: italic; font-weight: 400; }
        .gallery-link a { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; color: var(--anthracite); border-bottom: 2px solid var(--bordeaux); padding-bottom: 5px; transition: var(--transition); font-family: 'Inter', sans-serif; }
        .gallery-grid { display: grid; grid-template-columns: repeat(4, 1fr); grid-auto-rows: 280px; gap: 20px; padding: 0 5%; }
        .gallery-item { position: relative; overflow: hidden; border-radius: 4px; background-color: #eee; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 1.5s cubic-bezier(0.23, 1, 0.32, 1); }
        .gallery-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(164, 28, 43, 0.8), transparent); display: flex; align-items: flex-end; padding: 30px; opacity: 0; transition: all 0.6s ease; }
        .gallery-overlay span { color: var(--white); text-transform: uppercase; letter-spacing: 3px; font-size: 0.8rem; font-weight: 600; transform: translateY(15px); transition: all 0.6s ease; font-family: 'Inter', sans-serif; }
        .gallery-item:hover img { transform: scale(1.1); }
        .gallery-item:hover .gallery-overlay { opacity: 1; }
        .gallery-item:hover .gallery-overlay span { transform: translateY(0); }
        .gallery-item.tall { grid-row: span 2; }
        .gallery-item.wide { grid-column: span 2; }

        .nira-footer { background-color: var(--sand); position: relative; padding-top: 40px; margin-top: 60px; }
        .footer-wave { position: absolute; top: -1px; left: 0; width: 100%; height: 40px; fill: var(--white); }
        .footer-grid { max-width: 1400px; margin: 0 auto; padding: 60px 5% 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 50px; align-items: flex-start; }
        .footer-about img { height: 70px; width: auto; filter: brightness(0) invert(0.2); margin-bottom: 20px; }
        .footer-about p { font-size: 0.9rem; color: #666; line-height: 1.6; font-family: 'Inter', sans-serif; }
        .footer-contact h3 { font-size: 1.8rem; font-style: italic; color: var(--bordeaux); margin-bottom: 25px; font-weight: 600; }
        .footer-contact ul { display: flex; flex-direction: column; gap: 15px; }
        .footer-contact li { display: flex; gap: 15px; color: var(--anthracite); font-size: 0.95rem; font-family: 'Inter', sans-serif; }
        .footer-contact li i { color: var(--bordeaux); margin-top: 4px; font-size: 1.1rem; min-width: 16px; }
        .footer-link { transition: color 0.3s ease; }
        .footer-link:hover { color: var(--bordeaux); }
        .footer-socials { display: flex; gap: 15px; margin-top: 30px; }
        .social-icon { width: 40px; height: 40px; border-radius: 50%; background-color: var(--anthracite); color: var(--white); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: all 0.4s ease; }
        .social-icon:hover { background-color: var(--bordeaux); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(164, 28, 43, 0.3); }
        .footer-partners { display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
        .partner-logo { height: 70px; object-fit: contain; filter: grayscale(100%) opacity(0.8); transition: all 0.4s ease; width: auto; }
        .partner-logo:hover { filter: grayscale(0%) opacity(1); }
        .footer-bottom { border-top: 1px solid rgba(0,0,0,0.06); padding: 25px 5%; }
        .footer-bottom-inner { max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; }
        .footer-legal-link, .footer-bottom-inner div { font-size: 0.8rem; color: #888; font-family: 'Inter', sans-serif; }
        .footer-legal-link:hover { color: var(--bordeaux); }
        .back-to-top { position: absolute; bottom: 30px; right: 5%; width: 45px; height: 45px; background-color: rgba(0,0,0,0.05); color: var(--anthracite); display: flex; align-items: center; justify-content: center; border-radius: 4px; font-size: 1.2rem; transition: all 0.3s ease; z-index: 10; }
        .back-to-top:hover { background-color: var(--bordeaux); color: var(--white); }

        @media (max-width: 1200px) {
            .hero-title { font-size: clamp(3.6rem, 7vw, 5.2rem); }
            .header-nav-container { gap: 25px; }
            nav ul { gap: 18px; }
            nav a { font-size: 0.76rem; }
        }
        @media (max-width: 1024px) {
            .gallery-grid { grid-template-columns: repeat(2, 1fr); }
            .gallery-item.wide { grid-column: span 2; }
        }
        @media (max-width: 992px) {
            .nira-header.scrolled { padding: 10px 5%; }
            nav ul { display: none; }
            .hero-content, .ecurie-grid, .situation-grid { grid-template-columns: 1fr; }
            .hero-title { font-size: 3.5rem; }
            .hero-gps { left: 50%; transform: translateX(-50%); bottom: 100px; text-align: center; }
            .hero-transition-wave { height: 40px; }
            .ecurie-grid, .situation-grid { gap: 60px; }
            .situation-images { max-width: 500px; margin: 0 auto; }
        }
        @media (max-width: 768px) {
            body.admin-bar .nira-header { top: 46px; }
            .hero-immersif-premium { min-height: 700px; }
            .logo img { height: 52px; }
            .nira-header.scrolled .logo img { height: 44px; }
            .hero-title { font-size: 3rem; }
            .hero-body { font-size: 1rem; }
            .btn-premium { padding: 18px 30px; font-size: 0.82rem; }
            .mini-features, .cards-grid { grid-template-columns: 1fr; }
            .card-premium { height: auto; min-height: 420px; }
            .avis-card { width: 300px; padding: 30px; }
            .avis-fade-left, .avis-fade-right { width: 10%; }
            .gallery-header { flex-direction: column; align-items: flex-start; }
            .back-to-top { display: none; }
            .footer-bottom-inner { flex-direction: column; text-align: center; justify-content: center; }
        }
        @media (max-width: 600px) {
            .gallery-grid { grid-template-columns: 1fr; }
            .gallery-item.tall, .gallery-item.wide { grid-column: span 1; grid-row: span 1; }
            .hero-title { font-size: 2.6rem; }
            .hero-badge { letter-spacing: 2px; font-size: 0.72rem; }
            .ecurie-visual { height: 460px; }
            .ecurie-image-large { height: 240px; }
            .ecurie-image-small { height: 360px; width: 50%; }
        }
    </style>

    <?php wp_head(); ?>
</head>
<body <?php body_class( 'nira-accueil-body' ); ?>>

    <header class="nira-header" id="niraHeader">
        <div class="logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img id="niraLogo" src="<?php echo esc_url( $IMG['logo_white'] ); ?>" alt="<?php bloginfo( 'name' ); ?>">
            </a>
        </div>
        <div class="header-nav-container">
            <nav>
                <ul>
                    <li><a href="#presentation">Présentation</a></li>
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

    <section class="hero-immersif-premium" id="presentation">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-bg-text" data-aos="fade-right" data-aos-duration="2000">NIRA</div>

        <div class="hero-content">
            <div class="hero-text-block">
                <div class="hero-badge" data-aos="fade-up">
                    <span></span> DEAUVILLE • NORMANDIE
                </div>
                <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">
                    Une écurie <span>familiale</span> et chaleureuse.
                </h1>
                <p class="hero-body" data-aos="fade-up" data-aos-delay="400">
                    À deux pas de Deauville, l'Écurie de Nira accueille vos chevaux dans un cadre calme, soigné et rassurant, avec une vraie attention portée à leur bien-être chaque jour.
                </p>
                <div data-aos="fade-up" data-aos-delay="600">
                    <a href="#ecurie" class="btn-premium">Découvrir les lieux →</a>
                </div>
            </div>
        </div>

        <div class="hero-gps">49.3620° N, 0.1065° E</div>
        <div class="hero-scroll">
            <i class="fa-solid fa-angle-down"></i>
        </div>

        <svg class="hero-transition-wave" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <path d="M0,160L80,181.3C160,203,320,245,480,240C640,235,800,181,960,160C1120,139,1280,149,1360,154.7L1440,160L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path>
        </svg>
    </section>

    <section id="ecurie">
        <div class="ecurie-deco"></div>
        <div class="ecurie-grid">
            <div class="ecurie-visual" data-aos="fade-right">
                <div class="ecurie-image-large">
                    <img src="<?php echo esc_url( $IMG['ecurie_large'] ); ?>" alt="Installations Écurie de Nira">
                </div>
                <div class="ecurie-image-small" data-aos="fade-up" data-aos-delay="400">
                    <img src="<?php echo esc_url( $IMG['ecurie_small'] ); ?>" alt="Bien-être équin à Nira">
                </div>
            </div>
            <div data-aos="fade-left" data-aos-delay="200">
                <div class="section-topline">
                    <span>Notre façon d'accueillir</span>
                    <div></div>
                </div>
                <h2 class="section-title">
                    Une écurie pensée pour le <em>bien-être.</em>
                </h2>
                <div class="section-text">
                    <p>L'Écurie de Nira est un lieu de vie où les chevaux évoluent dans un environnement calme, fonctionnel et adapté à leurs besoins. Située près de Deauville, la structure réunit confort, sérieux et attention au quotidien.</p>
                    <p>Ici, nous accordons de l'importance à chaque détail : la qualité des soins, le rythme de vie des chevaux, les installations et la relation de confiance avec les propriétaires, le tout dans une ambiance simple, familiale et authentique.</p>
                </div>
                <div class="mini-features">
                    <div class="mini-feature">
                        <div class="mini-feature-icon"><i class="fa-solid fa-medal"></i></div>
                        <div>
                            <h4>Installations de qualité</h4>
                            <p>Confort au quotidien</p>
                        </div>
                    </div>
                    <div class="mini-feature">
                        <div class="mini-feature-icon"><i class="fa-solid fa-eye"></i></div>
                        <div>
                            <h4>Présence continue</h4>
                            <p>Surveillance</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="pensions">
        <div class="container-xl">
            <div class="section-center" data-aos="fade-up">
                <h3 class="eyebrow">Des solutions simples et adaptées</h3>
                <h2>Nos formules de <em>pension.</em></h2>
            </div>
            <div class="cards-grid">
                <div class="card-premium" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-bg" style="background-image: url('<?php echo esc_url( $IMG['pension_box'] ); ?>');"></div>
                    <div class="card-content">
                        <span class="card-number">01</span>
                        <h4 class="card-title">Pension Box & Paddock</h4>
                        <p class="card-text">Une formule idéale pour offrir à votre cheval confort, sorties régulières et repères au quotidien.</p>
                        <ul class="card-list">
                            <li><i class="fa-solid fa-circle-check"></i> Sorties quotidiennes</li>
                            <li><i class="fa-solid fa-circle-check"></i> Alimentation Lambey</li>
                            <li><i class="fa-solid fa-circle-check"></i> Accès carrières & manège</li>
                        </ul>
                        <a href="<?php echo esc_url( home_url( '/pension-box-paddock' ) ); ?>" class="card-btn">En savoir plus</a>
                    </div>
                </div>
                <div class="card-premium active" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-bg" style="background-image: url('<?php echo esc_url( $IMG['pension_travail'] ); ?>');"></div>
                    <div class="card-content">
                        <span class="card-number" style="color: var(--white); opacity: 0.2;">02</span>
                        <h4 class="card-title" style="color: var(--white);">Travail & Valorisation</h4>
                        <p class="card-text" style="color: rgba(255,255,255,0.7);">Un accompagnement régulier et adapté à votre cheval, dans le respect de son rythme et de ses besoins.</p>
                        <ul class="card-list" style="color: rgba(255,255,255,0.8);">
                            <li><i class="fa-solid fa-circle-check" style="color: var(--bordeaux);"></i> Suivi individualisé</li>
                            <li><i class="fa-solid fa-circle-check" style="color: var(--bordeaux);"></i> Travail quotidien (plat/obstacle)</li>
                            <li><i class="fa-solid fa-circle-check" style="color: var(--bordeaux);"></i> Sorties en compétition</li>
                        </ul>
                        <a href="<?php echo esc_url( home_url( '/pension-et-tarifs' ) ); ?>" class="card-btn btn-light">Voir le détail</a>
                    </div>
                </div>
                <div class="card-premium" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-bg" style="background-image: url('<?php echo esc_url( $IMG['pension_passage'] ); ?>');"></div>
                    <div class="card-content">
                        <span class="card-number">03</span>
                        <h4 class="card-title">Séjours de passage</h4>
                        <p class="card-text">Une solution pratique et accueillante pour quelques jours sur place, que ce soit pour un stage, un concours ou une pause en Normandie.</p>
                        <ul class="card-list">
                            <li><i class="fa-solid fa-circle-check"></i> Accueil cavaliers extérieurs</li>
                            <li><i class="fa-solid fa-circle-check"></i> Box de passage</li>
                            <li><i class="fa-solid fa-circle-check"></i> Accès plage (Deauville)</li>
                        </ul>
                        <a href="<?php echo esc_url( home_url( '/pension-et-tarifs' ) ); ?>" class="card-btn">Découvrir</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="situation">
        <div class="situation-header" data-aos="fade-up">
            <h2>Un cadre de vie <em>agréable.</em></h2>
            <h3>Un environnement calme, pratique et rassurant au quotidien.</h3>
        </div>
        <div class="situation-grid">
            <div class="situation-images" data-aos="fade-right">
                <div class="circle-large">
                    <img src="<?php echo esc_url( $IMG['situation_large'] ); ?>" alt="Cheval en liberté dans les prés de Nira">
                </div>
                <div class="circle-small">
                    <img src="<?php echo esc_url( $IMG['situation_small'] ); ?>" alt="Cavalière à l'obstacle">
                </div>
            </div>
            <div data-aos="fade-left" data-aos-delay="200">
                <p class="situation-lead">
                    Aux portes de Deauville,<br>
                    sur la commune de Bonneville-sur-Touques
                </p>
                <div class="situation-points">
                    <div class="situation-point">
                        <div class="icon-hover"><i class="fa-solid fa-horse"></i></div>
                        <h4>À 500m du Pôle International<br>du Cheval de Deauville</h4>
                    </div>
                    <div class="situation-point">
                        <div class="icon-hover"><i class="fa-solid fa-umbrella-beach"></i></div>
                        <h4>À 3kms de la plage<br>de Deauville</h4>
                    </div>
                    <div class="situation-point">
                        <div class="icon-hover"><i class="fa-solid fa-road"></i></div>
                        <h4>Accès direct par l'autoroute<br>(A13 à 4 kms)</h4>
                    </div>
                </div>
                <div class="labels-row">
                    <img src="<?php echo esc_url( $IMG['label_1'] ); ?>" alt="Certificat EquuRES" class="label-img">
                    <img src="<?php echo esc_url( $IMG['label_2'] ); ?>" alt="Labellisé EquuRES Environnement Bien-être Animal" class="label-img">
                    <img src="<?php echo esc_url( $IMG['label_3'] ); ?>" alt="Logo partenaire" class="label-img">
                </div>
            </div>
        </div>
    </section>

    <section id="avis">
        <div class="avis-header" data-aos="fade-up">
            <h3 class="eyebrow">Quelques mots de nos clients</h3>
            <h2>Ce qu'ils disent de <em>l'écurie.</em></h2>
        </div>
        <div class="avis-slider-container">
            <div class="avis-fade-left"></div>
            <div class="avis-fade-right"></div>
            <div class="avis-slider-track">
                <?php
                $nira_avis = [
                    [
                        'name' => 'Loran',
                        'role' => 'Avis Google',
                        'text' => "Très bon accueil et très bien situé aux portes de Deauville. Parfait pour le gîte comme pour la pension des chevaux.",
                    ],
                    [
                        'name' => 'Cécile Dard',
                        'role' => 'Avis Google',
                        'text' => "Je suis avec mes chevaux chez Margaux depuis l'ouverture de son écurie, je les lui confie les yeux fermés. Tout est conçu pour qu'ils aient une vie de cheval.",
                    ],
                    [
                        'name' => 'Céline Obedia',
                        'role' => 'Avis Google',
                        'text' => "Écuries au top ! Mon cheval y est depuis mi-février et je ne peux que recommander. Margaux est aux petits soins.",
                    ],
                    [
                        'name' => 'Maya Fillols',
                        'role' => 'Avis Google',
                        'text' => "Une écurie exceptionnelle ! L'ambiance est au top, chaleureuse et motivante, et la qualité des prestations est irréprochable. Les installations sont toujours impeccables.",
                    ],
                    [
                        'name' => 'Céline Tourres',
                        'role' => 'Avis Google',
                        'text' => "Écurie au top ! Super installations, chevaux au top et ambiance géniale !",
                    ],
                ];
                // Boucle ×2 pour assurer l'effet de scroll infini
                for ( $i = 0; $i < 2; $i++ ) {
                    foreach ( $nira_avis as $a ) : ?>
                        <div class="avis-card">
                            <div class="avis-stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
                            <p class="avis-text">« <?php echo esc_html( $a['text'] ); ?> »</p>
                            <div class="avis-author"><h4><?php echo esc_html( $a['name'] ); ?></h4><span><?php echo esc_html( $a['role'] ); ?></span></div>
                        </div>
                    <?php endforeach;
                }
                ?>
            </div>
        </div>
    </section>

    <section id="galerie">
        <div class="gallery-header-wrap">
            <div class="gallery-header" data-aos="fade-up">
                <div class="gallery-header-left">
                    <h3 class="eyebrow">La vie à l'écurie</h3>
                    <h2>Quelques images du <em>quotidien.</em></h2>
                </div>
                <div class="gallery-link">
                    <a href="#">Voir la galerie</a>
                </div>
            </div>
        </div>
        <style>
            .nira-photos {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 16px;
                padding: 0 5%;
                max-width: 1400px;
                margin: 0 auto;
            }
            .nira-photo {
                position: relative;
                width: 100%;
                aspect-ratio: 4 / 3;
                overflow: hidden;
                border-radius: 8px;
                background: #eee no-repeat center / cover;
                cursor: pointer;
                transition: transform 0.4s ease;
            }
            .nira-photo:hover { transform: translateY(-4px); }
            .nira-photo::after {
                content: "";
                position: absolute; inset: 0;
                background: linear-gradient(to top, rgba(164,28,43,0.65), transparent 55%);
                opacity: 0;
                transition: opacity 0.4s ease;
            }
            .nira-photo:hover::after { opacity: 1; }
            .nira-photo span {
                position: absolute;
                left: 22px; bottom: 18px;
                color: #fff;
                font-family: 'Inter', sans-serif;
                font-size: 0.78rem;
                text-transform: uppercase;
                letter-spacing: 2.5px;
                font-weight: 600;
                opacity: 0;
                transform: translateY(12px);
                transition: all 0.4s ease;
                z-index: 2;
            }
            .nira-photo:hover span { opacity: 1; transform: translateY(0); }
            @media (max-width: 1024px) {
                .nira-photos { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            }
            @media (max-width: 600px) {
                .nira-photos { grid-template-columns: 1fr; gap: 12px; }
            }
        </style>
        <div class="nira-photos">
            <div class="nira-photo" data-aos="zoom-in" data-aos-delay="100" style="background-image: url('<?php echo esc_url( $IMG['gallery_1'] ); ?>');">
                <span>Les installations</span>
            </div>
            <div class="nira-photo" data-aos="zoom-in" data-aos-delay="200" style="background-image: url('<?php echo esc_url( $IMG['gallery_2'] ); ?>');">
                <span>Le lien cheval / cavalier</span>
            </div>
            <div class="nira-photo" data-aos="zoom-in" data-aos-delay="300" style="background-image: url('<?php echo esc_url( $IMG['gallery_3'] ); ?>');">
                <span>Les extérieurs</span>
            </div>
            <div class="nira-photo" data-aos="zoom-in" data-aos-delay="400" style="background-image: url('<?php echo esc_url( $IMG['gallery_4'] ); ?>');">
                <span>Bien-être</span>
            </div>
            <div class="nira-photo" data-aos="zoom-in" data-aos-delay="500" style="background-image: url('<?php echo esc_url( $IMG['gallery_5'] ); ?>');">
                <span>Un cadre simple et soigné</span>
            </div>
        </div>
    </section>

    <footer class="nira-footer" id="contact">
        <svg class="footer-wave" viewBox="0 0 1440 100" preserveAspectRatio="none">
            <path d="M0,100 C320,0 420,100 740,50 C1060,0 1120,100 1440,50 L1440,0 L0,0 Z"></path>
        </svg>
        <div class="footer-grid">
            <div class="footer-about" data-aos="fade-up">
                <img src="<?php echo esc_url( $IMG['logo_white'] ); ?>" alt="<?php bloginfo( 'name' ); ?>">
                <p>Pension, travail, débourrage, balnéothérapie et séjours équestres dans un cadre familial au cœur de la Normandie.</p>
            </div>
            <div class="footer-contact" data-aos="fade-up" data-aos-delay="100">
                <h3>Margaux Duchemin</h3>
                <ul>
                    <li><i class="fa-solid fa-location-dot"></i><span>609 route de Deauville<br>14800 Bonneville-sur-Touques</span></li>
                    <li><i class="fa-solid fa-phone"></i><a href="tel:+33674572819" class="footer-link">06 74 57 28 19</a></li>
                    <li><i class="fa-solid fa-envelope"></i><a href="mailto:contact@ecuriedenira.fr" class="footer-link">contact@ecuriedenira.fr</a></li>
                </ul>
                <div class="footer-socials">
                    <a href="https://www.facebook.com/profile.php?id=100088742455431" target="_blank" rel="noopener" aria-label="Facebook" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://www.instagram.com/ecurie_de_nira/" target="_blank" rel="noopener" aria-label="Instagram" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-partners" data-aos="fade-up" data-aos-delay="200">
                <img src="<?php echo esc_url( $IMG['partner_1'] ); ?>" alt="Label EquuRES" class="partner-logo">
                <img src="<?php echo esc_url( $IMG['partner_2'] ); ?>" alt="Région Normandie" class="partner-logo">
                <img src="<?php echo esc_url( $IMG['partner_3'] ); ?>" alt="Union Européenne" class="partner-logo">
                <p class="footer-funding" style="flex-basis:100%;width:100%;margin:14px 0 0;font-size:0.78rem;line-height:1.5;color:#777;max-width:560px;">Le projet « Amélioration de la structure – Écurie de Nira » a bénéficié d'une aide financière de la Région Normandie et de l'Union européenne.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-bottom-inner">
                <div style="flex-basis:100%;width:100%;text-align:center;font-size:0.72rem;color:#999;line-height:1.45;">Projet « Amélioration de la structure – Écurie de Nira » cofinancé par la Région Normandie et l'Union européenne.</div>
                <div><a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>" class="footer-legal-link">Mentions légales et politique de confidentialité</a></div>
                <div>Copyright © <?php echo esc_html( date( 'Y' ) ); ?> Ecurie de Nira</div>
                <div>Créé par NOK'S Consulting</div>
            </div>
        </div>
        <a href="#presentation" class="back-to-top">
            <i class="fa-solid fa-chevron-up"></i>
        </a>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        var LOGO_WHITE = <?php echo wp_json_encode( $IMG['logo_white'] ); ?>;
        var LOGO_DARK  = <?php echo wp_json_encode( $IMG['logo_dark'] ); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof AOS !== 'undefined') {
                AOS.init({ duration: 1000, once: true });
            }
            var header = document.getElementById('niraHeader');
            var logo = document.getElementById('niraLogo');
            if (!header || !logo) return;

            function updateHeaderOnScroll() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                    logo.src = LOGO_DARK;
                } else {
                    header.classList.remove('scrolled');
                    logo.src = LOGO_WHITE;
                }
            }
            updateHeaderOnScroll();
            window.addEventListener('scroll', updateHeaderOnScroll);
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>

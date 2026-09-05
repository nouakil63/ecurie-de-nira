<?php
/**
 * Template Name: Pension Box/Paddock (Nira)
 * Surcharge des images via options WP (clé : nira_pension_box_<key>).
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
    'logo_white'  => nira_img( 'pension_box', 'logo_white',  'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'   => nira_img( 'pension_box', 'logo_dark',   'logo-OK-VECTO.png',        $IMG_BASE ),
    'hero_img'    => nira_img( 'pension_box', 'hero_img',    'IMG-2991-scaled.jpg',      $IMG_BASE ),
    'atmosphere'  => nira_img( 'pension_box', 'atmosphere',  '385037290_290035957297817_8425971591010244386_n.jpg', $IMG_BASE ),
    'facilities'  => nira_img( 'pension_box', 'facilities',  'IMG_7770.jpeg',            $IMG_BASE ),
    'partner_1'   => nira_img( 'pension_box', 'partner_1',   'macaron-engagement-1-150x150.png', $IMG_BASE ),
    'partner_2'   => nira_img( 'pension_box', 'partner_2',   'region-normandie.png',     $IMG_BASE ),
    'partner_3'   => nira_img( 'pension_box', 'partner_3',   'union-europeenne.png',     $IMG_BASE ),
    'footer_logo' => nira_img( 'pension_box', 'footer_logo', 'logo-OK-VECTO.png',        $IMG_BASE ),
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
        :root{--bordeaux:#A41C2B;--anthracite:#2D2D2D;--sand:#FDFBF9;--white:#FFFFFF;--transition:all 0.5s cubic-bezier(0.23,1,0.32,1);}
        *{margin:0;padding:0;box-sizing:border-box;}html{scroll-behavior:smooth;}
        body{font-family:'Inter',sans-serif;background-color:var(--sand);color:var(--anthracite);overflow-x:hidden;line-height:1.6;}
        h1,h2,h3{font-family:'Playfair Display',serif;font-weight:700;}a{text-decoration:none;color:inherit;transition:var(--transition);}
        header{position:fixed;top:0;width:100%;z-index:1000;padding:30px 4%;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(to bottom,rgba(0,0,0,0.6),rgba(0,0,0,0));transition:var(--transition);}
        header.scrolled{padding:15px 4%;background:rgba(253,251,249,0.98);backdrop-filter:blur(10px);box-shadow:0 5px 20px rgba(0,0,0,0.05);}
        .logo img{height:60px;transition:var(--transition);}header.scrolled .logo img{height:50px;}
        .header-nav-container{display:flex;align-items:center;gap:40px;}
        nav ul{display:flex;list-style:none;gap:25px;align-items:center;}
        nav a{color:var(--white);font-size:0.85rem;font-weight:500;text-transform:uppercase;letter-spacing:1px;transition:var(--transition);}
        header.scrolled nav a{color:var(--anthracite);}nav a:hover{color:#ccc;}header.scrolled nav a:hover{color:var(--bordeaux);}
        .header-social{display:flex;gap:12px;align-items:center;}
        .social-circle{background-color:var(--white);color:#000;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:var(--transition);}
        header.scrolled .social-circle{background-color:var(--anthracite);color:var(--white);}.social-circle:hover{transform:scale(1.1);}
        header.scrolled .social-circle:hover{background-color:var(--bordeaux);}
        .editorial-hero{padding:180px 5% 120px;background-color:var(--white);display:grid;grid-template-columns:1.2fr 1fr;gap:80px;align-items:center;}
        .breadcrumb{font-size:0.75rem;text-transform:uppercase;letter-spacing:3px;margin-bottom:30px;color:#888;}
        .editorial-title{font-size:clamp(3.5rem,6vw,6rem);line-height:1;margin-bottom:40px;font-weight:700;}
        .editorial-title span{font-style:italic;font-weight:400;font-family:'Playfair Display',serif;color:var(--bordeaux);}
        .editorial-hero-image{position:relative;height:70vh;border-radius:4px;overflow:hidden;box-shadow:30px 30px 0 var(--sand);}
        .editorial-hero-image img{width:100%;height:100%;object-fit:cover;display:block;}
        .pension-specs{padding:140px 5%;background-color:var(--sand);}
        .specs-grid{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:40px;}
        .spec-item{background:var(--white);padding:50px;border-radius:4px;text-align:center;border:1px solid rgba(0,0,0,0.03);transition:var(--transition);}
        .spec-item:hover{transform:translateY(-10px);box-shadow:0 20px 50px rgba(0,0,0,0.05);}
        .spec-icon{font-size:2rem;color:var(--bordeaux);margin-bottom:30px;}
        .spec-title{font-size:1.4rem;font-weight:600;margin-bottom:15px;color:var(--anthracite);}
        .spec-desc{font-size:0.95rem;color:#666;font-weight:300;}
        .atmosphere-immersion{height:100vh;position:relative;background-image:url('<?php echo esc_url( $IMG['atmosphere'] ); ?>');background-size:cover;background-position:center;background-attachment:fixed;display:flex;align-items:center;justify-content:flex-end;padding:0 5%;}
        .atmosphere-overlay{position:absolute;inset:0;background:linear-gradient(to right,rgba(0,0,0,0.5),transparent);}
        .atmosphere-text-box{position:relative;z-index:2;max-width:500px;padding:60px;background-color:var(--white);border-radius:4px;box-shadow:0 30px 60px rgba(0,0,0,0.1);}
        .atmosphere-title{font-size:2.2rem;color:var(--anthracite);margin-bottom:25px;}
        .atmosphere-desc{font-size:1rem;color:#666;line-height:1.8;font-weight:300;}.atmosphere-desc strong{color:var(--bordeaux);font-weight:600;}
        .routine-section{padding:120px 5%;background-color:var(--white);}
        .routine-header{text-align:center;margin-bottom:70px;}.routine-header h2{font-size:2.8rem;color:var(--anthracite);margin-bottom:15px;}
        .routine-grid{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(3,1fr);gap:30px;}
        .routine-card{background:var(--sand);padding:40px;border-top:4px solid var(--bordeaux);border-radius:4px;transition:var(--transition);}
        .routine-card:hover{transform:translateY(-5px);box-shadow:0 15px 40px rgba(0,0,0,0.08);}
        .routine-time{font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--bordeaux);font-style:italic;margin-bottom:15px;display:block;}
        .routine-title{font-size:1.2rem;color:var(--anthracite);margin-bottom:15px;font-weight:600;}
        .routine-text{font-size:0.95rem;color:#666;line-height:1.7;}
        .facilities-section{padding:120px 5%;background-color:var(--sand);}
        .fac-container{max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;}
        .fac-img{position:relative;border-radius:4px;overflow:hidden;box-shadow:20px 20px 0 var(--white);height:500px;}
        .fac-img img{width:100%;height:100%;object-fit:cover;display:block;}
        .fac-content h2{font-size:2.8rem;color:var(--anthracite);margin-bottom:25px;line-height:1.2;}
        .fac-content p{font-size:1.05rem;color:#666;margin-bottom:30px;line-height:1.8;}
        .fac-list{list-style:none;margin-bottom:40px;}
        .fac-list li{display:flex;align-items:center;gap:15px;margin-bottom:15px;font-size:1rem;color:var(--anthracite);font-weight:500;}
        .fac-list i{color:var(--bordeaux);font-size:1.2rem;width:25px;text-align:center;}
        .btn-outline{display:inline-block;padding:15px 35px;border:2px solid var(--bordeaux);color:var(--bordeaux);text-transform:uppercase;letter-spacing:2px;font-size:0.85rem;font-weight:600;border-radius:4px;transition:var(--transition);}
        .btn-outline:hover{background-color:var(--bordeaux);color:var(--white);}
        .footer-link{transition:color 0.3s ease;}.footer-link:hover{color:var(--bordeaux);}
        .footer-legal-link{font-size:0.8rem;color:#888;}.footer-legal-link:hover{color:var(--bordeaux);}
        .social-icon{width:40px;height:40px;border-radius:50%;background-color:var(--anthracite);color:var(--white);display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:all 0.4s ease;}
        .social-icon:hover{background-color:var(--bordeaux);transform:translateY(-3px);}
        .partner-logo{height:70px;object-fit:contain;filter:grayscale(100%) opacity(0.8);transition:all 0.4s ease;}.partner-logo:hover{filter:grayscale(0%) opacity(1);}
        .back-to-top{position:absolute;bottom:30px;right:5%;width:45px;height:45px;background-color:rgba(0,0,0,0.05);color:var(--anthracite);display:flex;align-items:center;justify-content:center;border-radius:4px;font-size:1.2rem;transition:all 0.3s ease;z-index:10;}
        .back-to-top:hover{background-color:var(--bordeaux);color:var(--white);}
        @media(max-width:1024px){.editorial-hero{grid-template-columns:1fr;padding-top:150px;}.editorial-hero-image{height:50vh;order:-1;margin-bottom:50px;}.specs-grid{grid-template-columns:repeat(2,1fr);}.routine-grid{grid-template-columns:1fr;}.fac-container{grid-template-columns:1fr;}.fac-img{height:400px;order:-1;}}
        @media(max-width:992px){nav ul{display:none;}}
        @media(max-width:768px){.specs-grid{grid-template-columns:1fr;}.atmosphere-immersion{background-attachment:scroll;}.back-to-top{display:none;}}
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header id="header">
    <div class="logo"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img id="logoImg" src="<?php echo esc_url( $IMG['logo_white'] ); ?>" alt="<?php bloginfo('name'); ?>"></a></div>
    <div class="header-nav-container">
        <nav><ul>
            <li><a href="<?php echo esc_url( home_url('/') ); ?>">Présentation</a></li>
            <li><a href="<?php echo esc_url( home_url('/infrastructures') ); ?>">Infrastructures</a></li>
            <li><a href="<?php echo esc_url( home_url('/debourrage') ); ?>">Débourrage et valorisation</a></li>
            <li><a href="<?php echo esc_url( home_url('/balneotherapie') ); ?>">Balnéothérapie</a></li>
            <li><a href="<?php echo esc_url( home_url('/pension-et-tarifs') ); ?>">Pension et tarifs</a></li>
            <li><a href="<?php echo esc_url( home_url('/gites') ); ?>">Gîtes</a></li>
            <li><a href="<?php echo esc_url( home_url('/contact') ); ?>">Contact</a></li>
        </ul></nav>
        <div class="header-social">
            <a href="https://www.facebook.com/profile.php?id=100088742455431" target="_blank" rel="noopener" aria-label="Facebook" class="social-circle"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/ecurie_de_nira/" target="_blank" rel="noopener" aria-label="Instagram" class="social-circle"><i class="fa-brands fa-instagram"></i></a>
        </div>
    </div>
</header>

<section class="editorial-hero">
    <div class="editorial-hero-text" data-aos="fade-right">
        <div class="breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Accueil</a> / <a href="<?php echo esc_url(home_url('/pension-et-tarifs')); ?>">Pensions</a> / <span>Box & Paddock</span></div>
        <h1 class="editorial-title">Pension<br><span style="margin-left:60px">Box &</span><br>Paddock.</h1>
        <p style="font-size:1.1rem;color:#666;font-weight:300;line-height:1.8;">L'équilibre parfait entre le confort intérieur d'une écurie haut de gamme et la liberté des prairies normandes. Conçue pour la sérénité du cheval et la performance du cavalier.</p>
    </div>
    <div class="editorial-hero-image" data-aos="fade-left" data-aos-delay="200">
        <img src="<?php echo esc_url( $IMG['hero_img'] ); ?>" alt="Pension Box Écurie de Nira">
    </div>
</section>

<section class="pension-specs">
    <div class="specs-grid">
        <div class="spec-item" data-aos="fade-up" data-aos-delay="100"><div class="spec-icon"><i class="fa-solid fa-home"></i></div><h3 class="spec-title">Hébergement premium</h3><p class="spec-desc">Boxes spacieux et aérés, entretenus quotidiennement pour une hygiène irréprochable.</p></div>
        <div class="spec-item" data-aos="fade-up" data-aos-delay="200"><div class="spec-icon"><i class="fa-solid fa-seedling"></i></div><h3 class="spec-title">Liberté quotidienne</h3><p class="spec-desc">Sorties surveillées en paddocks sécurisés, 7 jours sur 7, pour un moral d'acier.</p></div>
        <div class="spec-item" data-aos="fade-up" data-aos-delay="300"><div class="spec-icon"><i class="fa-solid fa-wheat-awn"></i></div><h3 class="spec-title">Alimentation fibrée</h3><p class="spec-desc">Foin de qualité produit au domaine et alimentation premium adaptée aux besoins physiologiques.</p></div>
    </div>
</section>

<section class="atmosphere-immersion">
    <div class="atmosphere-overlay"></div>
    <div class="atmosphere-text-box" data-aos="fade-left" data-aos-delay="200">
        <h2 class="atmosphere-title">Un sanctuaire au naturel.</h2>
        <p class="atmosphere-desc">Au Domaine de Nira, nous considérons que le bien-être physique et mental de votre cheval est la clé de toute progression. Notre formule <strong>Box & Paddock</strong> respecte ses besoins fondamentaux au grand air normand, tout en offrant aux propriétaires un suivi personnalisé et une attention de chaque instant.</p>
    </div>
</section>

<section class="routine-section">
    <div class="routine-header" data-aos="fade-up">
        <h2>Le rythme d'une journée</h2>
        <p style="color:#666;max-width:600px;margin:0 auto;">Une organisation rigoureuse pour respecter l'horloge biologique de votre cheval, lui assurant une stabilité et une sérénité totales.</p>
    </div>
    <div class="routine-grid">
        <div class="routine-card" data-aos="fade-up" data-aos-delay="100"><span class="routine-time">Matin</span><h3 class="routine-title">Réveil & Nutrition</h3><p class="routine-text">La journée commence par une surveillance matinale attentive et la distribution de la première ration de floconnés. Les boxes sont ensuite paillés ou curés selon votre choix de litière.</p></div>
        <div class="routine-card" data-aos="fade-up" data-aos-delay="200"><span class="routine-time">Journée</span><h3 class="routine-title">Espace & Liberté</h3><p class="routine-text">Votre cheval est sorti individuellement dans nos vastes paddocks en herbe (1500 à 2000m²). Il profite du grand air normand, favorisant sa locomotion naturelle et son équilibre mental.</p></div>
        <div class="routine-card" data-aos="fade-up" data-aos-delay="300"><span class="routine-time">Soir</span><h3 class="routine-title">Repos au chaud</h3><p class="routine-text">Retour au Barn dans un box propre et confortable. Distribution de la seconde ration et mise à disposition de foin à volonté pour la nuit. L'écurie s'apaise sous surveillance vidéo.</p></div>
    </div>
</section>

<section class="facilities-section">
    <div class="fac-container">
        <div class="fac-content" data-aos="fade-right">
            <h2>Conçu pour les <span style="font-family:'Playfair Display',serif;font-style:italic;font-weight:400;color:var(--bordeaux);">passionnés.</span></h2>
            <p>En choisissant notre formule Box & Paddock, vous bénéficiez non seulement d'un hébergement premium pour votre cheval, mais également d'un accès illimité à l'ensemble de nos installations de travail et de détente.</p>
            <ul class="fac-list">
                <li><i class="fa-solid fa-check"></i> Accès à la grande carrière en sable fibré</li>
                <li><i class="fa-solid fa-check"></i> Sellerie propriétaire sécurisée par digicode</li>
                <li><i class="fa-solid fa-check"></i> Aires de pansage avec douche eau chaude</li>
                <li><i class="fa-solid fa-check"></i> Club House chauffé avec vue sur la carrière</li>
                <li><i class="fa-solid fa-check"></i> Gestion des couvertures par nos soins</li>
            </ul>
            <a href="<?php echo esc_url( home_url('/pension-et-tarifs') ); ?>" class="btn-outline">Consulter les tarifs</a>
        </div>
        <div class="fac-img" data-aos="fade-left" data-aos-delay="200">
            <img src="<?php echo esc_url( $IMG['facilities'] ); ?>" alt="Installations Écurie de Nira">
        </div>
    </div>
</section>

<footer id="contact" style="background-color:var(--sand);position:relative;padding-top:40px;">
    <div style="max-width:1400px;margin:0 auto;padding:60px 5% 40px;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:50px;align-items:flex-start;">
        <div data-aos="fade-up">
            <img src="<?php echo esc_url( $IMG['footer_logo'] ); ?>" alt="<?php bloginfo('name'); ?>" style="height:70px;margin-bottom:20px;">
            <p style="font-size:0.9rem;color:#666;line-height:1.6;">Pension, valorisation, débourrage, balnéothérapie et gîtes équestres au cœur de la Normandie.</p>
        </div>
        <div data-aos="fade-up" data-aos-delay="100">
            <h3 style="font-family:'Playfair Display',serif;font-size:1.8rem;font-style:italic;color:var(--bordeaux);margin-bottom:25px;">Margaux Duchemin</h3>
            <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:15px;">
                <li style="display:flex;gap:15px;color:var(--anthracite);font-size:0.95rem;"><i class="fa-solid fa-location-dot" style="color:var(--bordeaux);margin-top:4px;"></i><span>609 route de Deauville<br>14800 Bonneville-sur-Touques</span></li>
                <li style="display:flex;align-items:center;gap:15px;font-size:0.95rem;"><i class="fa-solid fa-phone" style="color:var(--bordeaux);"></i><a href="tel:+33674572819" class="footer-link">06 74 57 28 19</a></li>
                <li style="display:flex;align-items:center;gap:15px;font-size:0.95rem;"><i class="fa-solid fa-envelope" style="color:var(--bordeaux);"></i><a href="mailto:contact@ecuriedenira.fr" class="footer-link">contact@ecuriedenira.fr</a></li>
            </ul>
            <div style="display:flex;gap:15px;margin-top:30px;">
                <a href="https://www.facebook.com/profile.php?id=100088742455431" target="_blank" rel="noopener" aria-label="Facebook" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/ecurie_de_nira/" target="_blank" rel="noopener" aria-label="Instagram" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
            </div>
        </div>
        <div data-aos="fade-up" data-aos-delay="200" style="display:flex;gap:20px;align-items:center;flex-wrap:wrap;">
            <img src="<?php echo esc_url( $IMG['partner_1'] ); ?>" alt="Label EquuRES" class="partner-logo">
            <img src="<?php echo esc_url( $IMG['partner_2'] ); ?>" alt="Région Normandie" class="partner-logo">
            <img src="<?php echo esc_url( $IMG['partner_3'] ); ?>" alt="Union européenne" class="partner-logo">
            <p class="footer-funding" style="flex-basis:100%;width:100%;margin:14px 0 0;font-size:0.78rem;line-height:1.5;color:#777;max-width:560px;">Le projet « Amélioration de la structure – Écurie de Nira » a bénéficié d'une aide financière de la Région Normandie et de l'Union européenne.</p>
        </div>
    </div>
    <div style="border-top:1px solid rgba(0,0,0,0.06);padding:25px 5%;">
        <div style="max-width:1400px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px;">
            <div style="flex-basis:100%;width:100%;text-align:center;font-size:0.72rem;color:#999;line-height:1.45;">Projet « Amélioration de la structure – Écurie de Nira » cofinancé par la Région Normandie et l'Union européenne.</div>
            <a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>" class="footer-legal-link">Mentions légales et politique de confidentialité</a>
            <span style="font-size:0.8rem;color:#888;">Copyright © <?php echo date('Y'); ?> <?php bloginfo('name'); ?></span>
            <span style="font-size:0.8rem;color:#888;">Créé par NOK'S Consulting</span>
        </div>
    </div>
    <a href="#header" class="back-to-top"><i class="fa-solid fa-chevron-up"></i></a>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 1000, once: true });
    var LOGO_WHITE = <?php echo wp_json_encode( $IMG['logo_white'] ); ?>;
    var LOGO_DARK  = <?php echo wp_json_encode( $IMG['logo_dark'] ); ?>;
    window.onscroll = function() {
        var h = document.getElementById('header'), l = document.getElementById('logoImg');
        if (window.scrollY > 50) { h.classList.add('scrolled'); l.src = LOGO_DARK; }
        else { h.classList.remove('scrolled'); l.src = LOGO_WHITE; }
    };
</script>
<?php wp_footer(); ?>
</body>
</html>

<?php
/**
 * Template Name: Infrastructures (Nira)
 * Surcharge des images via options WP (clé : nira_infrastructure_<key>).
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
    'logo_white'  => nira_img( 'infrastructure', 'logo_white',  'cropped-logo-blanc-1.png', $IMG_BASE ),
    'logo_dark'   => nira_img( 'infrastructure', 'logo_dark',   'logo-OK-VECTO.png',        $IMG_BASE ),
    'carriere'    => nira_img( 'infrastructure', 'carriere',    'IMG_7770.jpeg',             $IMG_BASE ),
    'boxes'       => nira_img( 'infrastructure', 'boxes',       'image00018.jpeg',           $IMG_BASE ),
    'paddocks'    => nira_img( 'infrastructure', 'paddocks',    'IMG_5156.jpeg',             $IMG_BASE ),
    'gallery_1'   => nira_img( 'infrastructure', 'gallery_1',   'image00018.jpeg',           $IMG_BASE ),
    'gallery_2'   => nira_img( 'infrastructure', 'gallery_2',   'image00019.jpeg',           $IMG_BASE ),
    'gallery_3'   => nira_img( 'infrastructure', 'gallery_3',   '385320245_290035883964491_4208930849250831231_n-1024x768.jpg', $IMG_BASE ),
    'partner_1'   => nira_img( 'infrastructure', 'partner_1',   'macaron-engagement-1-150x150.png', $IMG_BASE ),
    'partner_2'   => nira_img( 'infrastructure', 'partner_2',   'region-normandie.png',      $IMG_BASE ),
    'partner_3'   => nira_img( 'infrastructure', 'partner_3',   'union-europeenne.png',      $IMG_BASE ),
    'footer_logo' => nira_img( 'infrastructure', 'footer_logo', 'logo-OK-VECTO.png',         $IMG_BASE ),
];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( get_the_title() ); ?> | <?php bloginfo('name'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--bordeaux:#8B1A24;--anthracite:#2C2A29;--sand:#FAF8F5;--white:#FFFFFF;--text-light:#6C6865;--transition:all 0.4s cubic-bezier(0.25,0.46,0.45,0.94);}
        *{margin:0;padding:0;box-sizing:border-box;}html{scroll-behavior:smooth;}
        body{font-family:'Inter',sans-serif;background-color:var(--sand);color:var(--anthracite);overflow-x:hidden;line-height:1.7;-webkit-font-smoothing:antialiased;}
        h1,h2,h3,h4{font-family:'Playfair Display',serif;}a{text-decoration:none;color:inherit;transition:var(--transition);}
        header{position:fixed;top:0;width:100%;z-index:1000;padding:30px 4%;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(to bottom,rgba(0,0,0,0.6),rgba(0,0,0,0));transition:var(--transition);}
        header.scrolled{padding:15px 4%;background:rgba(253,251,249,0.98);backdrop-filter:blur(10px);box-shadow:0 5px 20px rgba(0,0,0,0.05);}
        .logo img{height:60px;transition:var(--transition);}header.scrolled .logo img{height:50px;}
        .header-nav-container{display:flex;align-items:center;gap:40px;}
        nav ul{display:flex;list-style:none;gap:25px;align-items:center;}
        nav a{color:var(--white);font-size:0.85rem;font-weight:500;text-transform:uppercase;letter-spacing:1px;transition:var(--transition);}
        header.scrolled nav a{color:var(--anthracite);}nav a:hover{color:#ccc;}header.scrolled nav a:hover{color:var(--bordeaux);}
        .header-social{display:flex;gap:12px;align-items:center;}
        .social-circle{background-color:var(--white);color:#000;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:var(--transition);}
        header.scrolled .social-circle{background-color:var(--anthracite);color:var(--white);}.social-circle:hover{transform:scale(1.1);}header.scrolled .social-circle:hover{background-color:var(--bordeaux);}
        .page-header{padding:180px 5% 60px;text-align:center;}
        .page-header h2{font-size:0.9rem;text-transform:uppercase;letter-spacing:4px;color:var(--bordeaux);margin-bottom:15px;font-family:'Inter',sans-serif;font-weight:500;}
        .page-header h1{font-size:clamp(3rem,5vw,4.5rem);color:var(--anthracite);font-weight:600;line-height:1.1;}
        .page-header h1 span{font-style:italic;font-weight:400;color:var(--bordeaux);}
        .stats-band{padding:20px 5% 80px;max-width:1000px;margin:0 auto;}
        .stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;text-align:center;border-top:1px solid rgba(0,0,0,0.05);border-bottom:1px solid rgba(0,0,0,0.05);padding:30px 0;}
        .stat-item h4{font-size:1.5rem;color:var(--anthracite);margin-bottom:5px;}
        .stat-item p{font-family:'Inter',sans-serif;font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-light);}
        .infra-container{max-width:1200px;margin:0 auto;padding:0 5% 100px;}
        .infra-block{display:grid;grid-template-columns:1.2fr 1fr;gap:80px;align-items:center;margin-bottom:120px;}
        .infra-block:last-child{margin-bottom:0;}
        .infra-block.reverse{grid-template-columns:1fr 1.2fr;}
        .infra-block.reverse .infra-image-col{order:2;}.infra-block.reverse .infra-text-col{order:1;}
        .infra-image-wrapper{position:relative;width:100%;height:550px;border-radius:12px;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,0.04);}
        .infra-image-wrapper img{width:100%;height:100%;object-fit:cover;transition:transform 1s ease;}
        .infra-block:hover .infra-image-wrapper img{transform:scale(1.03);}
        .infra-text-col{padding:20px 0;}
        .infra-tag{display:inline-block;font-size:0.8rem;text-transform:uppercase;letter-spacing:3px;color:var(--text-light);margin-bottom:20px;border-bottom:1px solid rgba(0,0,0,0.1);padding-bottom:5px;}
        .infra-title{font-size:2.5rem;color:var(--anthracite);margin-bottom:25px;line-height:1.2;}
        .infra-desc{color:var(--text-light);font-size:1.05rem;line-height:1.8;font-weight:300;margin-bottom:40px;}
        .infra-list{list-style:none;display:grid;grid-template-columns:1fr;gap:15px;}
        .infra-list li{display:flex;align-items:center;gap:15px;font-size:0.95rem;color:var(--anthracite);}
        .infra-list i{color:var(--bordeaux);font-size:1.1rem;width:20px;text-align:center;}
        .services-section{background-color:var(--white);padding:100px 5%;margin-bottom:80px;}
        .services-container{max-width:1200px;margin:0 auto;}
        .services-header{text-align:center;margin-bottom:60px;max-width:700px;margin-left:auto;margin-right:auto;}
        .services-header h2{font-size:2.8rem;color:var(--anthracite);margin-bottom:20px;line-height:1.1;}
        .services-header p{color:var(--text-light);font-size:1.05rem;font-weight:300;line-height:1.8;}
        .services-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:30px;border-top:1px solid rgba(0,0,0,0.05);padding-top:60px;}
        .service-box{text-align:center;padding:30px 20px;border-radius:8px;transition:var(--transition);border:1px solid transparent;}
        .service-box:hover{background-color:var(--sand);border-color:rgba(0,0,0,0.03);transform:translateY(-5px);}
        .service-icon{width:60px;height:60px;margin:0 auto 20px;background-color:rgba(139,26,36,0.05);color:var(--bordeaux);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;transition:var(--transition);}
        .service-box:hover .service-icon{background-color:var(--bordeaux);color:var(--white);}
        .service-box h4{font-size:1.2rem;color:var(--anthracite);margin-bottom:15px;font-family:'Inter',sans-serif;font-weight:600;}
        .service-box p{font-size:0.9rem;color:#777;line-height:1.6;font-weight:300;}
        .bottom-gallery{padding:0 5% 100px;max-width:1400px;margin:0 auto;}
        .gallery-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;}
        .gallery-grid img{width:100%;height:300px;object-fit:cover;border-radius:8px;transition:var(--transition);}
        .gallery-grid img:hover{opacity:0.8;}
        footer{background-color:var(--white);padding-top:80px;border-top:1px solid rgba(0,0,0,0.05);}
        .footer-grid{max-width:1400px;margin:0 auto;padding:0 5% 60px;display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:60px;}
        .footer-logo img{height:70px;margin-bottom:20px;}
        .footer-text{font-size:0.9rem;color:var(--text-light);line-height:1.6;}
        .footer-title{font-size:1.6rem;font-style:italic;color:var(--bordeaux);margin-bottom:25px;}
        .footer-contact-list{list-style:none;display:flex;flex-direction:column;gap:15px;}
        .footer-contact-list li{display:flex;gap:15px;color:var(--anthracite);font-size:0.95rem;}
        .footer-contact-list i{color:var(--bordeaux);margin-top:4px;}
        .footer-bottom{border-top:1px solid rgba(0,0,0,0.05);padding:25px 5%;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px;max-width:1400px;margin:0 auto;font-size:0.8rem;color:#888;}
        .footer-bottom a{transition:color 0.3s ease;}.footer-bottom a:hover{color:var(--bordeaux);}
        @media(max-width:992px){.infra-block,.infra-block.reverse{grid-template-columns:1fr;gap:40px;margin-bottom:80px;}.infra-block.reverse .infra-image-col{order:1;}.infra-block.reverse .infra-text-col{order:2;}.infra-image-wrapper{height:400px;}.stats-grid{grid-template-columns:repeat(2,1fr);}.services-grid{grid-template-columns:repeat(2,1fr);}.gallery-grid{grid-template-columns:repeat(2,1fr);}.nav ul{display:none;}}
        @media(max-width:768px){.page-header{padding-top:120px;}.infra-image-wrapper{height:300px;}.services-grid{grid-template-columns:1fr;}.gallery-grid{grid-template-columns:1fr;}.footer-bottom{flex-direction:column;text-align:center;}}
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header id="header">
    <div class="logo"><a href="<?php echo esc_url( home_url('/') ); ?>"><img id="logoImg" src="<?php echo esc_url( $IMG['logo_white'] ); ?>" alt="<?php bloginfo('name'); ?>"></a></div>
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

<section class="page-header">
    <h2 data-aos="fade-up">Le Domaine de Nira</h2>
    <h1 data-aos="fade-up" data-aos-delay="100">Nos <span>Infrastructures.</span></h1>
</section>

<section class="stats-band">
    <div class="stats-grid" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-item"><h4>7 ha</h4><p>De domaine</p></div>
        <div class="stat-item"><h4>EquuRES</h4><p>Label bien-être</p></div>
        <div class="stat-item"><h4>24/7</h4><p>Surveillance</p></div>
        <div class="stat-item"><h4>3 kms</h4><p>De la plage</p></div>
    </div>
</section>

<section class="infra-container">
    <div class="infra-block" data-aos="fade-up">
        <div class="infra-image-col">
            <div class="infra-image-wrapper">
                <img src="<?php echo esc_url( $IMG['carriere'] ); ?>" alt="Carrière Écurie de Nira">
            </div>
        </div>
        <div class="infra-text-col">
            <span class="infra-tag">Sport & Performance</span>
            <h3 class="infra-title">Espaces de travail optimisés</h3>
            <p class="infra-desc">Pour l'entraînement quotidien ou la préparation aux compétitions, nous mettons à votre disposition des aires de travail sécurisées. Notre sol technique est entretenu tous les jours pour préserver la locomotion et les articulations de vos chevaux.</p>
            <ul class="infra-list">
                <li><i class="fa-solid fa-check"></i> Grande carrière éclairée</li>
                <li><i class="fa-solid fa-check"></i> Sable de Fontainebleau fibré</li>
                <li><i class="fa-solid fa-check"></i> Parc d'obstacles complet à disposition</li>
                <li><i class="fa-solid fa-check"></i> Situé à 500m du Pôle International du Cheval</li>
            </ul>
        </div>
    </div>

    <div class="infra-block reverse" data-aos="fade-up">
        <div class="infra-image-col">
            <div class="infra-image-wrapper">
                <img src="<?php echo esc_url( $IMG['boxes'] ); ?>" alt="Boxes Écurie de Nira">
            </div>
        </div>
        <div class="infra-text-col">
            <span class="infra-tag">Confort intérieur</span>
            <h3 class="infra-title">Écuries haut de gamme</h3>
            <p class="infra-desc">L'architecture de nos écuries a été pensée pour favoriser la clarté et la ventilation naturelle. Les boxes offrent un volume généreux pour que votre cheval puisse se reposer sereinement après sa séance de travail ou sa sortie au paddock.</p>
            <ul class="infra-list">
                <li><i class="fa-solid fa-check"></i> Boxes spacieux et parfaitement aérés</li>
                <li><i class="fa-solid fa-check"></i> Litière entretenue méticuleusement</li>
                <li><i class="fa-solid fa-check"></i> Salles de soins et de pansage équipées</li>
                <li><i class="fa-solid fa-check"></i> Selleries sécurisées pour les propriétaires</li>
            </ul>
        </div>
    </div>

    <div class="infra-block" data-aos="fade-up">
        <div class="infra-image-col">
            <div class="infra-image-wrapper">
                <img src="<?php echo esc_url( $IMG['paddocks'] ); ?>" alt="Paddocks Écurie de Nira">
            </div>
        </div>
        <div class="infra-text-col">
            <span class="infra-tag">Vie au naturel</span>
            <h3 class="infra-title">Paddocks & Herbe normande</h3>
            <p class="infra-desc">Le bien-être mental passe par la liberté. Sur nos 7 hectares de domaine labellisés EquuRES, les chevaux profitent de paddocks sécurisés et de l'herbe normande tout au long de l'année. Les sorties quotidiennes sont garanties et surveillées.</p>
            <ul class="infra-list">
                <li><i class="fa-solid fa-check"></i> Vastes paddocks en herbe clôturés</li>
                <li><i class="fa-solid fa-check"></i> Sorties quotidiennes adaptées (seul ou en groupe)</li>
                <li><i class="fa-solid fa-check"></i> Herbe de qualité normande garantie</li>
                <li><i class="fa-solid fa-check"></i> Foin de qualité supérieure</li>
            </ul>
        </div>
    </div>
</section>

<section class="services-section" data-aos="fade-up">
    <div class="services-container">
        <div class="services-header">
            <h2>Le sens du <span style="font-family:'Playfair Display',serif;font-style:italic;color:var(--bordeaux);font-weight:400;">détail.</span></h2>
            <p>Parce que le bien-être se cache souvent dans les petites attentions, l'Écurie de Nira propose un ensemble de services complémentaires pensés pour faciliter votre quotidien et celui de votre cheval.</p>
        </div>
        <div class="services-grid">
            <div class="service-box"><div class="service-icon"><i class="fa-solid fa-shirt"></i></div><h4>Service Couvertures</h4><p>Gestion quotidienne (mise et retrait) des couvertures et chemises selon la météo.</p></div>
            <div class="service-box"><div class="service-icon"><i class="fa-solid fa-truck-medical"></i></div><h4>Coordination Soins</h4><p>Gestion des rendez-vous et assistance lors des visites du vétérinaire, maréchal ou ostéopathe.</p></div>
            <div class="service-box"><div class="service-icon"><i class="fa-solid fa-van-shuttle"></i></div><h4>Transport</h4><p>Possibilité de transport pour vos chevaux vers les cliniques ou les terrains de concours.</p></div>
        </div>
    </div>
</section>

<section class="bottom-gallery" data-aos="fade-up">
    <div class="gallery-grid">
        <img src="<?php echo esc_url( $IMG['gallery_1'] ); ?>" alt="Cheval au box">
        <img src="<?php echo esc_url( $IMG['gallery_2'] ); ?>" alt="Cheval en liberté">
        <img src="<?php echo esc_url( $IMG['gallery_3'] ); ?>" alt="Vue des écuries">
    </div>
</section>

<footer id="contact">
    <div class="footer-grid">
        <div data-aos="fade-up">
            <a href="<?php echo esc_url( home_url('/') ); ?>" class="footer-logo"><img src="<?php echo esc_url( $IMG['footer_logo'] ); ?>" alt="<?php bloginfo('name'); ?>"></a>
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
        <div data-aos="fade-up" data-aos-delay="200" style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap;">
            <img src="<?php echo esc_url( $IMG['partner_1'] ); ?>" alt="Label EquuRES" style="height:60px;">
            <img src="<?php echo esc_url( $IMG['partner_2'] ); ?>" alt="Région Normandie" style="height:60px;">
            <img src="<?php echo esc_url( $IMG['partner_3'] ); ?>" alt="Union européenne" style="height:60px;">
            <p class="footer-funding" style="flex-basis:100%;width:100%;margin:14px 0 0;font-size:0.78rem;line-height:1.5;color:#777;max-width:560px;">Le projet « Amélioration de la structure – Écurie de Nira » a bénéficié d'une aide financière de la Région Normandie et de l'Union européenne.</p>
        </div>
    </div>
    <div class="footer-bottom">
        <div style="flex-basis:100%;width:100%;text-align:center;font-size:0.72rem;color:#999;line-height:1.45;">Projet « Amélioration de la structure – Écurie de Nira » cofinancé par la Région Normandie et l'Union européenne.</div>
        <a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>">Mentions légales et politique de confidentialité</a>
        <div>Copyright © <?php echo date('Y'); ?> <?php bloginfo('name'); ?></div>
        <div>Créé par NOK'S Consulting</div>
    </div>
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

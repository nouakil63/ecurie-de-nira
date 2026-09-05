<?php
/**
 * Plugin Name:       Nira Booking — Écuries de Nira
 * Plugin URI:        https://ecuriedenira.fr
 * Description:       Système de réservation complet pour les gîtes : calendrier Airbnb-like, paiement Stripe, synchronisation iCal, tarifs saisonniers, horaires et annulations entièrement configurables.
 * Version:           2.0.44
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            NOK'S Consulting
 * Author URI:        https://ecuriedenira.fr
 * Text Domain:       nira-booking
 * Domain Path:       /languages
 * License:           GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'NIRA_BOOKING_VERSION', '2.0.44' );
define( 'NIRA_BOOKING_FILE',    __FILE__ );
define( 'NIRA_BOOKING_PATH',    plugin_dir_path( __FILE__ ) );
define( 'NIRA_BOOKING_URL',     plugin_dir_url( __FILE__ ) );
define( 'NIRA_BOOKING_BASENAME', plugin_basename( __FILE__ ) );

require_once NIRA_BOOKING_PATH . 'includes/class-nira-db.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-settings.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-properties.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-pricing.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-availability.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-booking.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-stripe.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-ical.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-email.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-ajax.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-rest.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-shortcode.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-admin.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-page-images.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-contact.php';
require_once NIRA_BOOKING_PATH . 'includes/class-nira-public-actions.php';

final class Nira_Booking_Plugin {

    private static $instance;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook( NIRA_BOOKING_FILE, [ 'Nira_DB', 'install' ] );
        register_deactivation_hook( NIRA_BOOKING_FILE, [ $this, 'on_deactivate' ] );

        add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
        add_action( 'init',           [ $this, 'boot' ] );

        add_action( 'wp_enqueue_scripts',    [ $this, 'enqueue_frontend' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin' ] );

        add_action( 'nira_ical_sync', [ 'Nira_Ical', 'sync_all' ] );
        add_action( 'nira_daily_cleanup', [ 'Nira_Booking', 'cleanup_expired_holds' ] );
        add_action( 'nira_daily_cleanup', [ 'Nira_Booking', 'send_pending_balance_requests' ] );

        // Enregistre le template de page "Gîtes & Séjours (Nira)" directement
        // depuis le plugin — permet à l'admin de le choisir sans toucher au thème.
        add_filter( 'theme_page_templates', [ $this, 'register_page_template' ] );
        add_filter( 'template_include',     [ $this, 'load_page_template' ] );

        // CSS global pour corriger l'affichage header/footer sur les pages Nira
        // (compensation barre d'admin WP, flex-wrap, alignements).
        add_action( 'wp_head', [ $this, 'nira_global_inline_css' ], 99 );

        // SEO : JSON-LD + meta complémentaires + fallback Yoast metadesc.
        add_action( 'wp_head', [ $this, 'nira_global_seo' ], 5 );
        add_filter( 'wpseo_metadesc', [ $this, 'nira_metadesc_fallback' ], 10, 1 );
        add_filter( 'document_title_separator', [ $this, 'nira_title_separator' ] );

        // Hamburger menu mobile : injecte un bouton + drawer via JS.
        add_action( 'wp_footer', [ $this, 'nira_mobile_nav_js' ], 99 );
    }

    public function nira_mobile_nav_js() {
        if ( ! is_page() ) return;
        $chosen = get_page_template_slug( get_queried_object_id() );
        $nira_templates = [
            'nira-gites', 'nira-accueil', 'nira-infrastructure', 'nira-debourrage',
            'nira-balneo', 'nira-pension-tarifs', 'nira-pension-box',
            'nira-pension-travail', 'nira-contact', 'nira-mentions-legales',
        ];
        if ( ! in_array( $chosen, $nira_templates, true ) ) return;
        ?>
<script id="nira-mobile-nav">
(function () {
    function build() {
        var header = document.querySelector('header#header, header.site-header, header.nira-header');
        if (!header) return;
        var nav = header.querySelector('nav');
        if (!nav || header.dataset.niraBurger === '1') return;
        header.dataset.niraBurger = '1';

        var btn = document.createElement('button');
        btn.className = 'nira-burger';
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Menu');
        btn.innerHTML = '<span></span><span></span><span></span>';

        // Insère avant les social icons (s'ils existent), sinon en fin de header
        var social = header.querySelector('.header-social');
        if (social && social.parentNode) {
            social.parentNode.insertBefore(btn, social);
        } else {
            header.appendChild(btn);
        }

        // Construit le drawer
        var drawer = document.createElement('div');
        drawer.className = 'nira-mobile-drawer';
        drawer.setAttribute('role', 'dialog');
        drawer.setAttribute('aria-modal', 'true');

        var navHTML = nav.innerHTML;
        var socialHTML = social ? social.innerHTML : '';

        drawer.innerHTML =
            '<button type="button" class="nira-drawer-close" aria-label="Fermer">×</button>' +
            '<nav>' + navHTML + '</nav>' +
            (socialHTML ? '<div class="nira-drawer-socials">' + socialHTML + '</div>' : '');
        document.body.appendChild(drawer);

        var closeBtn = drawer.querySelector('.nira-drawer-close');

        function open() {
            drawer.classList.add('is-open');
            btn.classList.add('is-active');
            btn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }
        function close() {
            drawer.classList.remove('is-open');
            btn.classList.remove('is-active');
            btn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
        btn.addEventListener('click', function () {
            drawer.classList.contains('is-open') ? close() : open();
        });
        if (closeBtn) closeBtn.addEventListener('click', close);

        var links = drawer.querySelectorAll('nav a');
        for (var i = 0; i < links.length; i++) {
            links[i].addEventListener('click', close);
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && drawer.classList.contains('is-open')) close();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', build);
    } else {
        build();
    }
})();
</script>
        <?php
    }

    /**
     * Injecte un <style> inline sur les pages utilisant un template Nira.
     * Corrige :
     *  - header masqué par la barre d'admin WordPress (top: 0 → 32/46px)
     *  - flex-wrap du header qui fait passer le menu sous le logo
     *  - alignement / espacement du footer
     */
    public function nira_global_inline_css() {
        if ( ! is_page() ) return;
        $chosen = get_page_template_slug( get_queried_object_id() );
        $nira_templates = [
            'nira-gites', 'nira-accueil', 'nira-infrastructure', 'nira-debourrage',
            'nira-balneo', 'nira-pension-tarifs', 'nira-pension-box',
            'nira-pension-travail', 'nira-contact', 'nira-mentions-legales',
        ];
        if ( ! in_array( $chosen, $nira_templates, true ) ) return;
        ?>
<style id="nira-global-fixes">
/* ===== Compensation barre d'admin WP pour les headers position:fixed ===== */
body.admin-bar header#header { top: 32px !important; }
@media screen and (max-width: 782px) {
    body.admin-bar header#header { top: 46px !important; }
}

/* ===== Header : forcer layout row + no-wrap + alignement logo | menu | social ===== */
body header#header {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    justify-content: flex-start !important;
    padding: 20px 4% !important;
    gap: 30px !important;
}
body header#header.scrolled {
    padding: 12px 4% !important;
}
body header#header .logo {
    flex: 0 0 auto !important;
    margin: 0 !important;
}
body header#header .logo img {
    display: block !important;
    max-height: 55px !important;
    height: auto !important;
    width: auto !important;
}
body header#header.scrolled .logo img {
    max-height: 45px !important;
}
body header#header .header-nav-container {
    flex: 1 1 auto !important;
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    align-items: center !important;
    justify-content: flex-end !important;
    gap: 30px !important;
    margin-left: auto !important;
    min-width: 0 !important;
}
body header#header nav {
    flex: 0 1 auto !important;
    min-width: 0 !important;
}
body header#header nav ul {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    list-style: none !important;
    gap: 22px !important;
    align-items: center !important;
    margin: 0 !important;
    padding: 0 !important;
}
body header#header nav ul li {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
}
body header#header nav a {
    white-space: nowrap !important;
    font-size: 0.8rem !important;
}
body header#header .header-social {
    flex: 0 0 auto !important;
    display: flex !important;
    gap: 10px !important;
}

/* Breakpoint tablette : on réduit la taille du menu avant de passer en mobile */
@media (max-width: 1280px) {
    body header#header nav ul { gap: 16px !important; }
    body header#header nav a { font-size: 0.72rem !important; letter-spacing: 0.5px !important; }
    body header#header .header-nav-container { gap: 20px !important; }
}
@media (max-width: 960px) {
    body header#header nav ul { display: none !important; }
}

/* === Hamburger menu mobile (injecté via JS) === */
.nira-burger {
    display: none;
    background: transparent;
    border: 0;
    cursor: pointer;
    padding: 8px;
    margin-left: 4px;
    width: 42px;
    height: 42px;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
    z-index: 1001;
}
.nira-burger span {
    display: block;
    width: 24px;
    height: 2px;
    background: #2D2D2D;
    border-radius: 2px;
    transition: transform 0.32s, opacity 0.2s, background 0.3s;
}
.nira-burger.is-active span { background: #fff !important; }
.nira-burger.is-active span:nth-child(1) { transform: translate(0, 7px) rotate(45deg); }
.nira-burger.is-active span:nth-child(2) { opacity: 0; }
.nira-burger.is-active span:nth-child(3) { transform: translate(0, -7px) rotate(-45deg); }

.nira-mobile-drawer {
    position: fixed; inset: 0;
    background: linear-gradient(160deg, #2D2D2D 0%, #1a1a1a 100%);
    z-index: 1000;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 28px;
    opacity: 0; visibility: hidden; transform: scale(1.04);
    transition: opacity 0.35s ease, transform 0.35s ease, visibility 0s linear 0.35s;
    padding: 80px 20px 40px;
}
.nira-drawer-close {
    position: absolute; top: 20px; right: 20px;
    width: 44px; height: 44px;
    background: rgba(255,255,255,0.06);
    border: 0; border-radius: 50%;
    color: #fff; font-size: 1.5rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.3s, transform 0.3s;
    z-index: 10;
    line-height: 1;
}
.nira-drawer-close:hover { background: rgba(255,255,255,0.15); transform: rotate(90deg); }
.nira-mobile-drawer.is-open {
    opacity: 1; visibility: visible; transform: scale(1);
    transition: opacity 0.35s ease, transform 0.35s ease, visibility 0s;
}
.nira-mobile-drawer nav { width: 100%; }
.nira-mobile-drawer nav ul {
    list-style: none; padding: 0; margin: 0;
    display: flex; flex-direction: column;
    align-items: center; gap: 22px;
}
.nira-mobile-drawer nav li { list-style: none; }
.nira-mobile-drawer nav a {
    color: #fff !important;
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.6rem !important;
    font-weight: 400 !important;
    text-transform: none !important;
    letter-spacing: 0 !important;
    text-decoration: none;
    padding: 6px 14px;
    transition: color 0.3s;
}
.nira-mobile-drawer nav a:hover { color: #A41C2B !important; }
.nira-mobile-drawer .nira-drawer-socials {
    display: flex; gap: 18px; margin-top: 14px;
}
.nira-mobile-drawer .nira-drawer-socials a {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    transition: background 0.3s, transform 0.3s;
}
.nira-mobile-drawer .nira-drawer-socials a:hover {
    background: #A41C2B;
    transform: translateY(-3px);
}

@media (max-width: 960px) {
    body .nira-burger,
    body header .nira-burger,
    body header#header .nira-burger,
    body header.site-header .nira-burger,
    body .nira-header .nira-burger {
        display: flex !important;
        flex-direction: column !important;
    }
}

/* === Galerie : grille simple, cellules de hauteur fixe, images qui remplissent === */
body .gallery-grid {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    grid-auto-rows: 320px !important;
    gap: 16px !important;
    padding: 0 5% !important;
    max-width: 1400px !important;
    margin: 0 auto !important;
    column-count: unset !important;
    column-gap: unset !important;
}
body .gallery-item,
body .gallery-item.tall,
body .gallery-item.wide {
    width: 100% !important;
    height: 100% !important;
    aspect-ratio: auto !important;
    margin: 0 !important;
    grid-column: auto !important;
    grid-row: auto !important;
    break-inside: auto !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    position: relative !important;
    background-color: #eee !important;
    display: block !important;
}
body .gallery-item > img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    object-position: center !important;
    display: block !important;
    aspect-ratio: auto !important;
    max-width: none !important;
}
@media (max-width: 1024px) {
    body .gallery-grid { grid-template-columns: repeat(2, 1fr) !important; grid-auto-rows: 280px !important; gap: 12px !important; }
}
@media (max-width: 600px) {
    body .gallery-grid { grid-template-columns: 1fr !important; grid-auto-rows: 240px !important; gap: 12px !important; padding: 0 5% !important; }
    body .gallery-overlay { padding: 18px !important; }
    body .gallery-header-wrap { padding: 0 5% !important; }
    body .gallery-header { flex-direction: column !important; align-items: flex-start !important; gap: 14px !important; }
}

/* ===== Footer : grille plus robuste + alignement propre ===== */
body footer .footer-grid {
    display: grid !important;
    grid-template-columns: 1.2fr 1.4fr 1fr !important;
    gap: 60px !important;
    align-items: start !important;
}
body footer .footer-grid > div { min-width: 0 !important; }
body footer .footer-bottom {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 20px !important;
}
@media (max-width: 1024px) {
    body footer .footer-grid { grid-template-columns: 1fr 1fr !important; }
}
@media (max-width: 768px) {
    body footer .footer-grid { grid-template-columns: 1fr !important; text-align: center !important; }
    body footer .footer-grid > div { display: flex !important; flex-direction: column !important; align-items: center !important; }
    body footer .footer-bottom { flex-direction: column !important; text-align: center !important; }
}

/* ============================================================
   TYPOGRAPHIE — alignée sur "pension travail.html" -1pt
   ============================================================ */
body { -webkit-font-smoothing: antialiased !important; -moz-osx-font-smoothing: grayscale !important; line-height: 1.6 !important; }
html { font-size: 16px !important; }

/* Paragraphes de lecture */
body .section-text, body .section-text p,
body .programme-desc,
body .tarifs-intro {
    font-size: 1.02rem !important;
    line-height: 1.78 !important;
}
body .hero-body { font-size: 1.05rem !important; line-height: 1.75 !important; }
body .editorial-hero-dark p { font-size: 1.05rem !important; line-height: 1.78 !important; }

/* Nav header */
body header nav a { font-size: 0.82rem !important; letter-spacing: 0.8px !important; }

/* Breadcrumb */
body .breadcrumb { font-size: 0.72rem !important; }

/* H2 — alignée sur la réf pension-travail (-1pt) */
body .section-title,
body .section-center h2,
body .gallery-header-left h2,
body .avis-header h2,
body .situation-header h2,
body .tarifs-section-title {
    font-size: clamp(2.1rem, 3.4vw, 2.95rem) !important;
    line-height: 1.2 !important;
}
body .situation-header h3 { font-size: clamp(1.5rem, 2.4vw, 2.1rem) !important; }
body .programme-title { font-size: 2.1rem !important; line-height: 1.25 !important; }

/* H1 hero éditorial */
body .editorial-title {
    font-size: clamp(2.85rem, 4.85vw, 5.3rem) !important;
    line-height: 1.1 !important;
}

/* H3 / H4 */
body .tarifs-block-head h3 { font-size: 1.78rem !important; }
body .footer-contact h3 { font-size: 1.7rem !important; }
body .card-title { font-size: 1.5rem !important; }
body .tarif-extra-card h4 { font-size: 1.38rem !important; }
body .mini-feature h4 { font-size: 1.22rem !important; }

/* Footer texts */
body footer .footer-contact-list li,
body footer .footer-contact ul li,
body footer .footer-about p,
body footer .footer-text { font-size: 0.92rem !important; }
body footer .footer-bottom,
body footer .footer-bottom *,
body footer .footer-legal-link { font-size: 0.78rem !important; }

/* ============================================================
   FIX H1 VISIBLE — tous les hero avec image / fond sombre
   ============================================================ */
body .hero-content h1,
body .hero-immersif-premium h1,
body .editorial-hero-dark h1,
body .hero-section h1 {
    color: #FFFFFF !important;
    text-shadow: 0 4px 18px rgba(0,0,0,0.35) !important;
}
body .hero-content h1 span,
body .hero-immersif-premium h1 span,
body .editorial-hero-dark h1 span,
body .editorial-hero-dark h1 em {
    color: #FFFFFF !important;
}
body .editorial-hero-dark h1 > span,
body .editorial-hero-dark h1 em { color: #A41C2B !important; }

/* Overlay hero plus contrasté pour lisibilité */
body .hero-overlay {
    background: linear-gradient(to bottom, rgba(0,0,0,0.45), rgba(0,0,0,0.7)) !important;
}

/* ============================================================
   FOOTER — harmonisation des marges et logos partenaires
   ============================================================ */
body footer {
    background-color: var(--sand, #FDFBF9) !important;
    padding-top: 60px !important;
    border-top: 1px solid rgba(0,0,0,0.04);
}
body footer.nira-footer { padding-top: 40px !important; border-top: none !important; }

body footer .footer-grid {
    padding: 0 5% 50px !important;
    align-items: start !important;
}

body footer .footer-logo img,
body footer .footer-about img {
    height: 70px !important;
    width: auto !important;
    margin-bottom: 22px !important;
    display: block !important;
}

/* Logos partenaires : taille & alignement homogènes */
body footer .partner-logo,
body footer img[alt*="EquuRES"],
body footer img[alt*="Label"],
body footer img[alt*="Région"],
body footer img[alt*="européenne"] {
    height: 70px !important;
    width: auto !important;
    max-width: 200px !important;
    object-fit: contain !important;
    margin: 0 !important;
}

/* Liste contact uniformisée */
body footer .footer-contact-list,
body footer .footer-contact ul {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 14px !important;
}
body footer .footer-contact-list li,
body footer .footer-contact li {
    display: flex !important;
    align-items: flex-start !important;
    gap: 14px !important;
    color: var(--anthracite, #2D2D2D) !important;
    font-size: 0.95rem !important;
    line-height: 1.55 !important;
    margin: 0 !important;
}
body footer .footer-contact-list i,
body footer .footer-contact li i {
    color: var(--bordeaux, #A41C2B) !important;
    margin-top: 4px !important;
    flex-shrink: 0 !important;
}

/* Bandeau bas du footer — alignement et marge */
body footer .footer-bottom {
    border-top: 1px solid rgba(0,0,0,0.06) !important;
    padding: 22px 5% !important;
    margin-top: 20px !important;
    font-size: 0.8rem !important;
    color: #888 !important;
}

/* === FIX CRITIQUE : titre H1 visible sur fond sombre === */
body .editorial-hero-dark .editorial-title { color: #FFFFFF !important; }
body .editorial-hero-dark .editorial-title > span,
body .editorial-hero-dark .editorial-title em {
    color: #A41C2B !important;
}
body .editorial-hero-dark { color: #FFFFFF !important; }
body .editorial-hero-dark p { color: rgba(255,255,255,0.85) !important; }
body .editorial-hero-dark .breadcrumb { color: rgba(255,255,255,0.55) !important; }
body .editorial-hero-dark .breadcrumb a { color: rgba(255,255,255,0.7) !important; }
body .editorial-hero-dark .breadcrumb a:hover { color: #FFFFFF !important; }
body .editorial-hero-dark .breadcrumb span { color: #FFFFFF !important; }
</style>
        <?php
    }

    /**
     * Descriptions SEO par template (utilisées par JSON-LD + fallback Yoast).
     */
    private function nira_seo_descriptions() {
        return [
            'nira-accueil' => "Écurie familiale et chaleureuse à Bonneville-sur-Touques, à 500 m du Pôle International du Cheval de Deauville. Pension box ou paddock, travail du cheval, débourrage, balnéothérapie et gîtes équestres en Normandie.",
            'nira-infrastructure' => "Carrière, manège, paddocks, marcheur, balnéothérapie : découvrez les infrastructures soignées de l'Écurie de Nira en Normandie, à 3 km de la plage de Deauville.",
            'nira-debourrage' => "Débourrage du jeune cheval à l'Écurie de Nira (Deauville) : approche douce, progressive et personnalisée, dans le respect du rythme de chaque cheval.",
            'nira-balneo' => "Balnéothérapie équine près de Deauville : soin par l'eau salée pour la récupération, la rééducation et l'entretien des jambes de votre cheval.",
            'nira-pension-tarifs' => "Toutes nos formules et tarifs : pension box (paille ou copeaux), paddock, travail du cheval, débourrage, balnéothérapie. Prix clairs et transparents.",
            'nira-pension-box' => "Pension box & paddock à l'Écurie de Nira : sorties quotidiennes, alimentation soignée, accès aux infrastructures sportives à Deauville.",
            'nira-pension-travail' => "Pension travail à l'Écurie de Nira : accompagnement sur-mesure, valorisation et sorties en compétition pour votre cheval, en Normandie.",
            'nira-gites' => "Gîtes équestres à Bonneville-sur-Touques (14800), à 3 km de la plage de Deauville. Séjournez avec votre cheval dans un cadre paisible et soigné.",
            'nira-contact' => "Contactez Margaux Duchemin à l'Écurie de Nira (Bonneville-sur-Touques, 14800) : 06 74 57 28 19 — contact@ecuriedenira.fr.",
            'nira-mentions-legales' => "Mentions légales, politique de confidentialité (RGPD) et mentions de financement (Région Normandie & Union européenne) du site de l'Écurie de Nira.",
        ];
    }

    /**
     * Injecte JSON-LD (LocalBusiness/EquestrianFacility) + theme-color sur
     * les pages utilisant un template Nira. Yoast continue de gérer le reste
     * (title, description, canonical, OG, Twitter, sitemap).
     */
    public function nira_global_seo() {
        if ( ! is_page() ) return;
        $page_id = get_queried_object_id();
        $chosen  = get_page_template_slug( $page_id );
        $nira_templates = [
            'nira-gites', 'nira-accueil', 'nira-infrastructure', 'nira-debourrage',
            'nira-balneo', 'nira-pension-tarifs', 'nira-pension-box',
            'nira-pension-travail', 'nira-contact', 'nira-mentions-legales',
        ];
        if ( ! in_array( $chosen, $nira_templates, true ) ) return;

        $descs = $this->nira_seo_descriptions();
        $description = $descs[ $chosen ] ?? '';
        $home  = home_url( '/' );
        $logo  = NIRA_BOOKING_URL . 'assets/img/logo-OK-VECTO.png';
        $hero  = NIRA_BOOKING_URL . 'assets/img/IMG_6526-scaled.jpeg';

        // Schema EquestrianFacility — uniquement sur l'accueil (page focale du business)
        if ( $chosen === 'nira-accueil' ) {
            $schema = [
                '@context'    => 'https://schema.org',
                '@type'       => [ 'LocalBusiness', 'EquestrianFacility' ],
                '@id'         => $home . '#business',
                'name'        => 'Écurie de Nira',
                'alternateName' => 'Les Écuries de Nira',
                'description' => $description,
                'url'         => $home,
                'telephone'   => '+33674572819',
                'email'       => 'contact@ecuriedenira.fr',
                'image'       => $hero,
                'logo'        => $logo,
                'priceRange'  => '€€',
                'address'     => [
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => '609 route de Deauville',
                    'addressLocality' => 'Bonneville-sur-Touques',
                    'postalCode'      => '14800',
                    'addressRegion'   => 'Normandie',
                    'addressCountry'  => 'FR',
                ],
                'geo' => [
                    '@type'     => 'GeoCoordinates',
                    'latitude'  => 49.3620,
                    'longitude' => 0.1065,
                ],
                'areaServed' => [
                    [ '@type' => 'City',                'name' => 'Deauville' ],
                    [ '@type' => 'City',                'name' => 'Trouville-sur-Mer' ],
                    [ '@type' => 'City',                'name' => 'Bonneville-sur-Touques' ],
                    [ '@type' => 'AdministrativeArea',  'name' => 'Calvados' ],
                    [ '@type' => 'AdministrativeArea',  'name' => 'Normandie' ],
                ],
                'makesOffer' => [
                    [ '@type' => 'Offer', 'name' => 'Pension box & paddock',  'url' => $home . 'pension-box-paddock' ],
                    [ '@type' => 'Offer', 'name' => 'Travail & valorisation', 'url' => $home . 'pension-et-tarifs' ],
                    [ '@type' => 'Offer', 'name' => 'Débourrage',             'url' => $home . 'debourrage' ],
                    [ '@type' => 'Offer', 'name' => 'Balnéothérapie',         'url' => $home . 'balneotherapie' ],
                    [ '@type' => 'Offer', 'name' => 'Gîtes équestres',        'url' => $home . 'gites' ],
                ],
            ];
            echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
        }

        // Theme color (Safari, Chrome mobile, PWA)
        echo '<meta name="theme-color" content="#A41C2B">' . "\n";
        echo '<meta name="format-detection" content="telephone=no">' . "\n";

        // Préchargement des fonts critiques (gain LCP)
        echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">' . "\n";
    }

    /**
     * Si Yoast n'a pas de meta description renseignée pour la page, on lui
     * fournit une description par défaut basée sur le template Nira.
     */
    public function nira_metadesc_fallback( $metadesc ) {
        if ( ! empty( $metadesc ) ) return $metadesc;
        if ( ! is_page() ) return $metadesc;
        $chosen = get_page_template_slug( get_queried_object_id() );
        $descs = $this->nira_seo_descriptions();
        return $descs[ $chosen ] ?? $metadesc;
    }

    /**
     * Séparateur de title plus élégant.
     */
    public function nira_title_separator( $sep ) {
        return '·';
    }

    public function register_page_template( $templates ) {
        $templates['nira-gites']           = __( 'Gîtes & Séjours (Nira)',    'nira-booking' );
        $templates['nira-accueil']         = __( 'Accueil (Nira)',            'nira-booking' );
        $templates['nira-infrastructure']  = __( 'Infrastructures (Nira)',    'nira-booking' );
        $templates['nira-debourrage']      = __( 'Débourrage (Nira)',         'nira-booking' );
        $templates['nira-balneo']          = __( 'Balnéothérapie (Nira)',     'nira-booking' );
        $templates['nira-pension-tarifs']  = __( 'Pension et tarifs (Nira)',  'nira-booking' );
        $templates['nira-pension-box']     = __( 'Pension Box/Paddock (Nira)','nira-booking' );
        $templates['nira-pension-travail'] = __( 'Pension Travail (Nira)',    'nira-booking' );
        $templates['nira-contact']         = __( 'Contact (Nira)',            'nira-booking' );
        $templates['nira-mentions-legales']= __( 'Mentions légales (Nira)',   'nira-booking' );
        return $templates;
    }

    public function load_page_template( $template ) {
        if ( ! is_page() ) return $template;
        $chosen = get_page_template_slug( get_queried_object_id() );
        $map = [
            'nira-gites'           => 'page-gites.php',
            'nira-accueil'         => 'page-accueil.php',
            'nira-infrastructure'  => 'page-infrastructure.php',
            'nira-debourrage'      => 'page-debourrage.php',
            'nira-balneo'          => 'page-balneo.php',
            'nira-pension-tarifs'  => 'page-pension-tarifs.php',
            'nira-pension-box'     => 'page-pension-box-paddock.php',
            'nira-pension-travail' => 'page-pension-travail.php',
            'nira-contact'         => 'page-contact.php',
            'nira-mentions-legales'=> 'page-mentions-legales.php',
        ];
        if ( isset( $map[ $chosen ] ) ) {
            $file = NIRA_BOOKING_PATH . $map[ $chosen ];
            if ( file_exists( $file ) ) return $file;
        }
        return $template;
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'nira-booking', false, dirname( NIRA_BOOKING_BASENAME ) . '/languages' );
    }

    public function boot() {
        Nira_Properties::instance();
        Nira_Ajax::instance();
        Nira_Shortcode::instance();
        Nira_Admin::instance();
        Nira_Rest::instance();
        Nira_Ical::instance();
        Nira_Stripe::instance();
        Nira_Page_Images::instance();
        Nira_Contact::instance();
        Nira_Public_Actions::instance();

        if ( version_compare( (string) get_option( 'nira_db_version' ), NIRA_BOOKING_VERSION, '<' ) ) {
            Nira_DB::install();
        }
    }

    public function enqueue_frontend() {
        wp_register_style(
            'nira-booking',
            NIRA_BOOKING_URL . 'assets/css/frontend.css',
            [],
            NIRA_BOOKING_VERSION
        );
        wp_register_script(
            'nira-booking',
            NIRA_BOOKING_URL . 'assets/js/frontend.js',
            [],
            NIRA_BOOKING_VERSION,
            true
        );
        wp_localize_script( 'nira-booking', 'NiraBooking', [
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'restUrl'    => esc_url_raw( rest_url( 'nira/v1/' ) ),
            'nonce'      => wp_create_nonce( 'nira_booking' ),
            'stripeKey'  => Nira_Settings::get( 'stripe_pk', '' ),
            'currency'   => Nira_Settings::get( 'currency', 'EUR' ),
            'chargeMode' => Nira_Settings::get( 'charge_mode', 'deposit' ),
            'checkIn'    => Nira_Settings::get( 'checkin_time', '16:00' ),
            'checkOut'   => Nira_Settings::get( 'checkout_time', '11:00' ),
            'locale'     => get_locale(),
            'i18n'       => self::i18n_strings(),
        ] );
    }

    public function enqueue_admin( $hook ) {
        if ( false === strpos( (string) $hook, 'nira-' ) ) {
            return;
        }
        wp_enqueue_style(
            'nira-booking-admin',
            NIRA_BOOKING_URL . 'assets/css/admin.css',
            [],
            NIRA_BOOKING_VERSION
        );
        wp_enqueue_script(
            'nira-booking-admin',
            NIRA_BOOKING_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            NIRA_BOOKING_VERSION,
            true
        );
        wp_localize_script( 'nira-booking-admin', 'NiraAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'restUrl' => esc_url_raw( rest_url( 'nira/v1/' ) ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ] );
    }

    public static function i18n_strings() {
        return [
            'selectCheckIn'  => __( "Sélectionnez une date d'arrivée", 'nira-booking' ),
            'selectCheckOut' => __( 'Sélectionnez une date de départ', 'nira-booking' ),
            'night'          => __( 'nuit', 'nira-booking' ),
            'nights'         => __( 'nuits', 'nira-booking' ),
            'total'          => __( 'Total', 'nira-booking' ),
            'reserve'        => __( 'Réserver', 'nira-booking' ),
            'unavailable'    => __( 'Indisponible', 'nira-booking' ),
            'minStay'        => __( 'Séjour minimum de %d nuits', 'nira-booking' ),
            'loading'        => __( 'Chargement…', 'nira-booking' ),
            'closed'         => __( 'Fermé', 'nira-booking' ),
            'paymentError'   => __( 'Erreur de paiement, merci de réessayer.', 'nira-booking' ),
            'months'         => [
                __( 'Janvier', 'nira-booking' ), __( 'Février', 'nira-booking' ), __( 'Mars', 'nira-booking' ),
                __( 'Avril', 'nira-booking' ), __( 'Mai', 'nira-booking' ), __( 'Juin', 'nira-booking' ),
                __( 'Juillet', 'nira-booking' ), __( 'Août', 'nira-booking' ), __( 'Septembre', 'nira-booking' ),
                __( 'Octobre', 'nira-booking' ), __( 'Novembre', 'nira-booking' ), __( 'Décembre', 'nira-booking' ),
            ],
            'monthsShort'    => [ 'Jan.', 'Févr.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.' ],
            'weekdays'       => [ 'L', 'M', 'M', 'J', 'V', 'S', 'D' ],
        ];
    }

    public function on_deactivate() {
        wp_clear_scheduled_hook( 'nira_ical_sync' );
        wp_clear_scheduled_hook( 'nira_daily_cleanup' );
    }
}

Nira_Booking_Plugin::instance();

=== Nira Booking ===
Contributors:      noksconsulting
Author:            NOK'S Consulting
Tags:              booking, reservation, airbnb, stripe, gite, equestre
Requires at least: 6.0
Tested up to:      6.6
Requires PHP:      7.4
Stable tag:        2.0.43
License:           GPLv2 or later

Système de réservation complet pour les gîtes des Écuries de Nira, avec
calendrier façon Airbnb, paiement Stripe, synchronisation iCal bidirectionnelle
et tout paramétrable depuis l'admin WordPress.

== Description ==

Plugin WordPress dédié aux Écuries de Nira : gestion d'hébergements, tarifs
saisonniers et hebdomadaires, disponibilités (blocage manuel + sync Airbnb
iCal), prise de réservation en direct avec paiement Stripe (acompte ou
intégral), emails transactionnels et tableau de bord administrateur.

**Fonctionnalités**

* Widget de réservation façon Airbnb (shortcode `[nira_booking slug="..."]`)
  avec plage de dates sur calendrier 2 mois, stepper voyageurs, détail de
  prix en direct (base + ménage + taxes + acompte).
* Gestion des hébergements : capacité, chambres, salles de bain, heures
  d'arrivée/départ, séjour min/max, prix de base, frais de ménage, acompte %,
  politique d'annulation, équipements, URL Airbnb.
* Règles tarifaires : saisons (plage de dates) et jours de semaine, avec
  système de priorité pour les conflits.
* Synchronisation iCal bidirectionnelle : import des blocages Airbnb (plusieurs
  flux par hébergement), export de vos réservations directes vers Airbnb.
* Paiement Stripe (Payment Intents + webhooks) — acompte uniquement ou
  paiement intégral.
* "Holds" de réservation avec expiration automatique si non payé.
* Emails : confirmation client, notification admin, annulation.
* Réglages globaux : devise, TVA, taxe de séjour, identité, délais, politiques.

== Installation ==

1. Copier le dossier `nira-booking/` dans `wp-content/plugins/`.
2. Activer depuis *Extensions* de WordPress. Les tables et les réglages par
   défaut sont créés automatiquement.
3. Ouvrir le menu **Nira Booking** pour configurer :
   * **Réglages** → devise, Stripe (pk/sk/webhook secret), horaires, emails.
   * **Hébergements** → prix, capacité, équipements, photos.
   * **Tarifs** → règles saisonnières (été, Noël…) ou par jour.
   * **Synchronisation** → URL iCal depuis Airbnb (Calendrier → Paramètres →
     Exporter) pour chaque hébergement, puis URL d'export Nira à coller dans
     Airbnb pour bloquer automatiquement les dates.
4. Configurer le webhook Stripe :
   `https://votresite.fr/wp-json/nira/v1/stripe-webhook`
   Événements : `payment_intent.succeeded` et `payment_intent.payment_failed`.
5. Sur n'importe quelle page : `[nira_booking slug="appartement"]` ou
   `[nira_booking slug="duplex"]`.

== Intégration dans la page Airbnb existante ==

Le fichier `pge airbnb.html` contient déjà deux appels shortcode, un par
hébergement, à la place des anciennes cartes de réservation. Une fois la page
convertie en page/template WordPress, les widgets s'afficheront
automatiquement à l'endroit du shortcode.

== Arborescence ==

```
nira-booking/
├── nira-booking.php                 — bootstrap plugin
├── includes/
│   ├── class-nira-db.php            — migrations + cron
│   ├── class-nira-settings.php      — réglages globaux
│   ├── class-nira-properties.php    — CRUD hébergements
│   ├── class-nira-pricing.php       — règles tarifaires + quote
│   ├── class-nira-booking.php       — cycle de vie réservation
│   ├── class-nira-availability.php  — calendrier + vérifs
│   ├── class-nira-stripe.php        — Payment Intents + webhook
│   ├── class-nira-ical.php          — import/export iCal
│   ├── class-nira-email.php         — mails transactionnels
│   ├── class-nira-ajax.php          — endpoints publics
│   ├── class-nira-rest.php          — endpoints REST (calendar/bookings)
│   ├── class-nira-shortcode.php     — rendu widget
│   └── class-nira-admin.php         — écrans WP-Admin
├── templates/admin/*.php            — vues admin server-rendered
├── assets/css/{frontend,admin}.css
└── assets/js/{frontend,admin}.js
```

== Tâches planifiées (cron) ==

* `nira_ical_sync` (horaire) — tire les flux iCal entrants.
* `nira_daily_cleanup` (quotidien) — libère les réservations non payées
  expirées (délai configurable dans les réglages).

== Sécurité ==

* Nonces (`nira_booking`) sur tous les endpoints AJAX publics.
* `check_admin_referer` sur toutes les actions admin.
* Secrets Stripe masqués à la saisie (`••••••••` conserve la valeur existante).
* Webhook Stripe signé (vérif via `whsec_…`).

== Changelog ==

= 2.0.43 =
* Email admin dédié à chaque réservation payée : coordonnées du client, dates, montant payé, solde restant, message du client et lien direct vers la réservation dans l'admin — au lieu d'une copie de l'email client (qui contenait ses liens d'annulation et de paiement du solde).

= 2.0.42 =
* Mode test dans Réglages : passe un hébergement à 1 €/nuit (ménage 0 €, paiement intégral, 1 nuit min) en sauvegardant les vrais prix (prix de base, acompte, remises, règles tarifaires) pour restauration automatique en un clic. Bannière d'avertissement dans tout l'admin tant que le mode est actif.

= 2.0.41 =
* Fiabilité paiement : confirmation de la réservation directement après le paiement (endpoint `nira_confirm_payment` qui revérifie le PaymentIntent auprès de Stripe) — le webhook n'est plus un point de défaillance unique.
* Correctif critique : les holds expirés ou remplacés sont annulés au lieu d'être supprimés — un paiement arrivé en retard retrouve sa réservation (fini les réservations payées qui disparaissent de l'admin).
* Sécurité : signature du webhook Stripe désormais obligatoire (rejet si secret absent ou en-tête manquant), tolérance anti-rejeu de 5 minutes, traitement idempotent des webhooks livrés en double.
* Affichage prix : le récapitulatif du paiement affichait « NaN » et omettait les frais de ménage ; détail désormais complet (nuits, remise, ménage, taxe de séjour, TVA) et cohérent avec le total.
* iCal : chaque flux ne supprime plus que ses propres imports (fini l'effacement croisé entre Airbnb et Booking), fetch sécurisé anti-SSRF, plus de référence interne exposée dans l'export public.
* Divers : anti-abus sur la création de hold (5/15 min par IP), correction du fuseau horaire d'expiration des holds, validation des statuts/dates/chevauchements à la modification d'une réservation dans l'admin.

= 2.0.0 =
* Refonte complète : admin server-rendered, widget frontend façon Airbnb,
  Stripe intégré, iCal bidirectionnel, tout paramétrable depuis WP-Admin.

= 1.0.0 =
* Version initiale.

# Écurie de Nira — notes pour Claude Code

## Contenu du dépôt

Plugin WordPress **Nira Booking** dans `nira-booking/` : système de réservation
pour les gîtes des Écuries de Nira (calendrier type Airbnb, paiement Stripe,
synchronisation iCal bidirectionnelle, tarifs saisonniers, emails, admin WP).

## Structure

- `nira-booking/nira-booking.php` — point d'entrée du plugin (bootstrap, hooks d'activation).
- `nira-booking/includes/` — classes PHP (`class-nira-*.php`) : DB, réservations,
  disponibilités, tarifs, Stripe, iCal, REST, AJAX, emails, admin, shortcode.
- `nira-booking/templates/admin/` — écrans d'administration WordPress.
- `nira-booking/page-*.php` — templates de pages du site (accueil, gîtes, pensions, contact…).
- `nira-booking/assets/` — CSS et JS front + admin.
- `nira-booking/readme.txt` — readme WordPress officiel (version, changelog, fonctionnalités).

## Conventions

- Langue du projet : français (textes, commits, commentaires).
- Text domain i18n : `nira-booking`.
- PHP ≥ 7.4, WordPress ≥ 6.0.
- Toujours utiliser `$wpdb->prepare()` pour les requêtes SQL, échapper les sorties
  (`esc_html`, `esc_attr`, `esc_url`) et vérifier nonces + capabilities sur toute
  action admin/AJAX.
- Les clés Stripe et secrets sont stockés dans les réglages WordPress, jamais dans le code.
- À chaque changement de version : mettre à jour `Stable tag` et le changelog dans
  `readme.txt` ainsi que l'en-tête de `nira-booking.php`.

## Tests

Pas de suite de tests automatisée pour l'instant. Vérifier au minimum la syntaxe
PHP après modification : `php -l <fichier>` sur chaque fichier touché.

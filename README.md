# JeyTech GPSR Guard for WooCommerce — dépôt de développement

Affiche les informations GPSR (fabricant, responsable UE, avertissements) sur les fiches produit : saisie unique par marque (taxonomie core), héritage produit, section « Sécurité du produit », audit des produits sans données. Ce fichier n’est pas livré dans le ZIP (voir `.distignore`).

## Prérequis

Node.js uniquement : WordPress, WooCommerce et PHP tournent dans [WordPress Playground](https://developer.wordpress.org/playground/) (`@wp-playground/cli`). Premier lancement : `npm install`.

## Commandes

| Commande | Rôle |
|---|---|
| `npm run dev` | Site de démo en anglais sur http://127.0.0.1:9402 (connecté en admin, données de démo, page du plugin ouverte) |
| `npm run dev:fr` | Même chose en français, port 9403 |
| `npm test` | Scénario automatisé (Definition of Done) sur un site neuf, HPOS, PHP 8.3 |
| `npm run test:legacy` | Même scénario, stockage classique des commandes, PHP 7.4 |
| `npm run i18n` | Régénère le `.pot`, compile `.mo` et `.l10n.php` depuis les `.po` (le dossier `languages/` reste local) |
| `npm run check` | Construit le ZIP puis lance Plugin Check (vérifications statiques) sur son contenu |
| `npm run build` | `dist/jeytech-gpsr-guard.zip` |

Rapports : `dev/.test-output-<hpos|posts>.txt`, `dev/.plugin-check.txt`.

## Structure

- `jeytech-gpsr-guard.php` — en-tête, constantes, autoload, compatibilité.
- `src/Core/` — partie générique du boilerplate JeyTech (autoloader, prérequis, déclarations HPOS/Blocks).
- `src/Data.php` — champs, résolution marque → produit, sanitization.
- `src/BrandFields.php`, `src/ProductFields.php` — écrans de saisie.
- `src/Frontend/Safety.php` — onglet classique + section pour thèmes blocs.
- `src/Admin/SettingsPage.php`, `src/Admin/AuditPage.php` — réglages et audit.
- `dev/` — blueprints Playground, jeu de démo, scénario de test, sources des visuels. `.wordpress-org/` — icône, bannière et captures pour le SVN (dossier `assets`).

Points d’extension prévus pour le Pro : filtres `jeytech_gpsr_data` (données résolues), `jeytech_gpsr_should_display` (exclusions) et `jeytech_gpsr_tab_title`.

## Limites connues de Playground

- Sous PHP 7.4, Playground plante quand WooCommerce met en forme ses emails : les tests évitent ce cas (pas d’impact sur un vrai serveur).
- Plugin Check ne peut lancer que ses vérifications statiques (la partie « exécution » a besoin d’une base secondaire que SQLite refuse).
- Base SQLite : un dernier test sur MySQL (Docker + `wp-env`) reste possible avant soumission.

## Publication WordPress.org

Nom retenu : « JeyTech GPSR Guard for WooCommerce » (le préfixe distingue de « Rampedigitale GPSR Guard », déjà présent). Slug demandé : `jeytech-gpsr-guard`.

# JeyTech Safety Data by Brand for WooCommerce — dépôt de développement

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
| `npm run build` | `dist/jeytech-safety-data-by-brand.zip` |

Rapports : `dev/.test-output-<hpos|posts>.txt`, `dev/.plugin-check.txt`.

## Structure

- `jeytech-safety-data-by-brand.php` — en-tête, constantes, autoload, compatibilité.
- `src/Core/` — partie générique du boilerplate JeyTech (autoloader, prérequis, déclarations HPOS/Blocks).
- `src/Data.php` — champs, résolution marque → produit, sanitization.
- `src/BrandFields.php`, `src/ProductFields.php` — écrans de saisie.
- `src/Frontend/Safety.php` — onglet classique + section pour thèmes blocs.
- `src/Admin/SettingsPage.php`, `src/Admin/AuditPage.php` — réglages et audit.
- `dev/` — blueprints Playground, jeu de démo, scénario de test, sources des visuels. `.wordpress-org/` — icône, bannière et captures pour le SVN (dossier `assets`).

Points d’extension prévus pour le Pro : filtres `jeytech_sdbb_data` (données résolues), `jeytech_sdbb_should_display` (exclusions) et `jeytech_sdbb_tab_title`.

## Limites connues de Playground

- Sous PHP 7.4, Playground plante quand WooCommerce met en forme ses emails : les tests évitent ce cas (pas d’impact sur un vrai serveur).
- Plugin Check ne peut lancer que ses vérifications statiques (la partie « exécution » a besoin d’une base secondaire que SQLite refuse).
- Base SQLite : un dernier test sur MySQL (Docker + `wp-env`) reste possible avant soumission.

## Publication WordPress.org

Contrôle du 27/09/2026 : l’ancien nom « JeyTech GPSR Guard for WooCommerce » reprend « GPSR Guard », déjà utilisé par [Rampedigitale GPSR Guard](https://wordpress.org/plugins/rampedigitale-gpsr-guard/) et par le logiciel [GPSR-Guard](https://gpsr-software.de/gpsr-software). Le préfixe JeyTech ne suffit pas à rendre cette base originale : nom abandonné avant soumission.

Nom adopté : **JeyTech Safety Data by Brand for WooCommerce**, slug `jeytech-safety-data-by-brand`. Les recherches publiques du nom et de « Safety Data by Brand » ne trouvent aucune extension ; l’API du slug renvoie « Plugin not found ». Le slug court a ensuite été attribué dans le dossier de soumission WordPress.org ; l’approbation finale reste à obtenir.

Résultats et sources : [audit du nom](dev/name-audit-2026-09-27.json). Renommage appliqué au code, au domaine de traduction, aux outils, à la bannière et aux captures EN/FR. Le dépôt GitHub conserve son URL historique. ZIP 1.0.0 soumis le 27/09/2026 via le compte `jeytech` : **Awaiting Review**, analyse automatique **Pass**. Le slug automatique `jeytech-safety-data-by-brand-for-woocommerce` a été corrigé immédiatement en `jeytech-safety-data-by-brand`, aligné sur le domaine de traduction. SHA-256 du premier ZIP envoyé : `27c25cf74b6857727d1dc899560caf0a0a64dea240357c4e19b0cbf20416397e`.

Validation : 18 vérifications HPOS/PHP 8.3 + 18 en stockage classique/PHP 7.4 ; Plugin Check statique : 0 erreur, 0 avertissement. Les 15 fichiers du ZIP sont identiques aux sources ; traductions et fichiers de développement exclus. Visuels mis à jour avec ImageGen intégré ; prompt versionné dans `dev/assets-src/banner-safety-data-by-brand-prompt.md`.

Mise à jour UI du 27/09/2026 : panneau fabricant des formulaires ajout/édition de marque aux couleurs JeyTech, CSS limité à ce panneau et chargé uniquement sur la taxonomie `product_brand`. Enregistrement et absence de débordement mobile contrôlés en EN/FR. Captures du site : panneau fabricant + audit ; la vue publique suit le thème de la boutique. ZIP corrigé ajouté au dossier de revue existant, toujours **Awaiting Review**. SHA-256 actuel : `9f76737c6034e4be7134843c38be74ac5e7e0513ac008110148445b4ad1c8d9d`. Résultats : [audit UI](dev/ui-audit-2026-09-27.json).

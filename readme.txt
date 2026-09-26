=== JeyTech GPSR Guard for WooCommerce ===
Contributors: jeytech
Tags: woocommerce, gpsr, product safety, manufacturer, brands
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add GPSR manufacturer and safety information to every product — set it once per brand, audit what's missing.

== Description ==

Since 13 December 2024, EU Regulation 2023/988 (GPSR) requires online offers to show the manufacturer's name and contact details, the EU responsible person when the manufacturer is outside the EU, and the product safety warnings. WooCommerce has no fields for any of this.

GPSR Guard adds them where they belong:

* **Set once per brand** — manufacturer, address, email and EU responsible person live on the brand (WooCommerce core taxonomy). Every product of the brand inherits them.
* **Override per product** — any field can be replaced on a single product. Variations use their parent's data.
* **Safety warnings per product** — care instructions shown with the manufacturer block.
* **"Product Safety" section on the page** — a product tab on classic themes, an appended section on block themes. Nothing shows when a product has no data.
* **Audit list** — the admin screen lists the products still missing GPSR data.

GPSR Guard displays what you enter. It is not legal advice and does not guarantee compliance.

Compatible with High-Performance Order Storage (HPOS) and the Cart & Checkout blocks. Requires WooCommerce 9.6 or later for the core Brands taxonomy.

== Installation ==

1. Install and activate the plugin (WooCommerce 9.6 or later must be active).
2. Go to **Products → Brands** and fill in the manufacturer data of each brand.
3. Optionally override fields or add warnings on a product (GPSR tab).
4. Check **WooCommerce → GPSR audit** for products still missing data.

== Frequently Asked Questions ==

= Does it make my shop compliant? =

No tool can. The plugin displays the data you enter in the right place; you remain responsible for its accuracy. When in doubt, ask a professional.

= Where do I enter the manufacturer data? =

Once per brand under Products → Brands. Products inherit their brand's data; leave a product field empty to inherit it, or fill it to override.

= What about variable products? =

GPSR fields live on the parent product and its brand. Variations always use their parent's data.

= Does it work with block themes? =

Yes. Classic themes show a "Product Safety" product tab; block themes get the same section appended to the product content, since the Single Product block renders no classic tabs.

= What if a product has no brand? =

Fill the four fields directly on the product. The audit lists products that still miss data either way.

== Screenshots ==

1. Manufacturer fields on the brand screen, inherited by every product.
2. The Product Safety section on the product page.
3. The audit list of products missing GPSR data.

== Changelog ==

= 1.0.0 =
* First release: brand-level manufacturer data with per-product overrides, safety warnings, product page section for classic and block themes, audit list.

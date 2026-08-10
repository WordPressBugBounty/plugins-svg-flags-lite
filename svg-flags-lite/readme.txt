=== SVG Flags – Country Flag Blocks and Galleries ===
Contributors: dgwyer, wpgoplugins, gwycon
Tags: svg, flag, country, scalable, world
Requires at least: 6.3
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.9.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add responsive SVG country flags, accessible flag images, and multi-country galleries with blocks or shortcodes.

== Description ==

SVG Flags of the world allows you to add high quality beautiful flags to your WordPress site in seconds. Display one or more flags at any scale without loss of quality and with a fixed aspect ratio so they always look great! Supported aspect ratios are 4:3 (default) and 1:1 (square).

Create SVG flags visually in the block editor and preview them in real time. Choose from 271 current country, territory, regional, and organisation flags using a searchable selector, then control size, aspect ratio, captions, and layout without leaving the editor.

The plugin includes three dynamic blocks:

* **SVG Flag** for a lightweight CSS-rendered flag.
* **SVG Flag Image** for an accessible image with country-name alternative text.
* **SVG Flag Grid** for responsive multi-country galleries with up to eight columns, adjustable spacing, captions, and square or 4:3 flags.

Shortcodes are available for classic-editor and template use. Add one flag with <code>[svg-flag flag="gb"]</code>, an accessible image with <code>[svg-flag-image flag="fr"]</code>, or a gallery with <code>[svg-flag-grid flags="gb,us,ca,fr" columns="4" caption="true"]</code>.

= Need more presentation control? =

SVG Flags Pro adds flag headings, custom captions and tooltips, IDs and CSS classes, plus border, spacing, and presentation controls. Open the SVG Flags page in your WordPress dashboard to compare the available options and pricing.

We hope you find this plugin useful. If you have a moment please consider <a href="https://wordpress.org/support/view/plugin-reviews/svg-flags-lite"><strong>rating</strong></a> it to show your support. It's very much appreciated and helps spread the word.

Also, why not take a look at our <a href="https://www.wpgoplugins.com" target="_blank">other plugins</a>. We're continually developing great solutions for WordPress.

== Installation ==

1. Via the WordPress admin go to Plugins => Add New.
2. Enter 'SVG Flags' (without quotes) in the text box and click the 'Search Plugins' button.
3. In the list of relevant Plugins click the 'Install' link for SVG Flags on the right hand side of the page.
4. Click the 'Install Now' button on the popup page.
5. Click 'Activate Plugin' to finish installation.
6. You'll be redirected to the plugin welcome page upon activation which gives basic usage instructions and other information to get you started.

== Frequently Asked Questions ==

= Where can I see all the available SVG flag blocks, shortcodes, and attributes? =

Visit the <a target="_blank" href="https://wpgoplugins.com/document/svg-flags-documentation/">plugin documentation page</a> to see available SVG flags blocks, shortcodes and attributes, plus related options.

= Where can I see SVG Flags in action? =

Visit the <a target="_blank" href="https://wpgoplugins.com/plugins/svg-flags/">SVG Flags product page</a> for current examples, feature details, and links to the full documentation.

= Is there a way to remember all shortcode attributes? =

If you use the SVG flag editor blocks then you won't need to remember any shortcodes at all as you can add flags to your page content 100% visually. No need to remember which shortcodes are available. You will also be able to see a live preview of the flag in the editor when using a block, which you can't do with shortcodes.

= Where can I get the country code for each flag? =

The plugin uses Alpha-2 codes for countries and territories, plus the additional regional and organisation codes included by the bundled flag-icons library. You can look up standard country codes <a href="https://www.iban.com/country-codes" target="_blank">here</a>.

== Screenshots ==

1. SVG flags rendering in the Gutenberg block-based editor.
2. SVG Aspect Ratio.
3. Headings.
4. Div, Span, and Paragraph elements.
5. Flag Sizes.
6. Tables.

== Changelog ==
= 0.9.7, AUG 10, 2026 =

* Restored compatibility with current WordPress and PHP releases.
* Replaced the obsolete Node Sass build with a maintained Dart Sass and Webpack toolchain.
* Improved the editor country selector and fixed admin asset loading.
* Updated all blocks to Block API v3 and opted into current WordPress control sizing.
* Hardened flag, shortcode, and release configuration handling.
* Added a responsive multi-country Flag Grid block and shortcode.
* Added accessible image alternative text and lazy loading.
* Updated the Freemius SDK from 2.4.3 to 2.13.4.
* Updated the bundled flag library from flag-icon-css 3.4.5 to flag-icons 7.5.0, with 271 current flags and legacy CSS class compatibility.
* Replaced the legacy shared plugin framework with native SVG Flags admin and plugin services.

= 0.9.6, MAR 21, 2022 =

* Updated Freemius SDK to v2.4.3.

= 0.9.5, JAN 29, 2021 =

* Updated 3rd party libraries.
* Minor bug fixes and UI updates.

= 0.9.3, SEP 7, 2020 =

* New: Added a new [svg-flag-heading] shortcode.
* New: Added a block as an alternative to the [svg-flag-heading] shortcode.
* New: Added a block as an alternative to the [svg-flag-image] shortcode.
* Fixed: New features counter now working correctly.

= 0.9.2, SEP 7, 2020 =

* Patched bug in new features counter.

= 0.9.1, AUG 28, 2020 =

* Updated: Refactored code.

= 0.9.0, MAR 11, 2020 =

* New: Added new [svg-flag-image] shortcode.
* New: Attributes added to the [svg-flag] shortcode: size, size_unit.
* New: Attributes added to the [svg-flag] shortcode that were previously ONLY available in pro: caption, random, inline.
* New: Added new editor block which is a direct replacement to the [svg-flag] shortcode.
* Updated: Deployment script.

= 0.8, DEC 02, 2020 =

* Updated settings.

= 0.7, DEC 30, 2019 =

* Updated settings page.

= 0.6, DEC 24, 2019 =

* Maintenance release.

= 0.5, DEC 23, 2019 =

* Fixed 'square' shortcode attribute.
* Most of the code refactored.
* Compatible with WordPress 5.3.

= 0.4, MAY 21, 2019 =

* Minor bug fix.

= 0.3, MAY 21, 2019 =

* Added new <code>[svg-flags]</code> shortcode as a recent update to the Gutenberg block editor seems to strip out using the SVG flag HTML directly. Use the new shortcode to guarantee the flag will always display in the block editor and on the front end.

= 0.2, APRIL 26, 2019 =

* Flag SVG's updated to latest version (3.3).
* SVG flags now work in the WordPress (5.0+) block editor.

= 0.1, OCTOBER 25, 2017 =

* Initial plugin release!


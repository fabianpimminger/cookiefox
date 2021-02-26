=== CookieFox - Cookie Notice ===
Contributors: fabianpimminger
Donate link: http://paypal.me/fabianpimminger
Tags: privacy, cookie, cookie notice, cookie banner, cookie consent, GDPR, CCPA
Requires at least: 5.0
Tested up to: 5.6.2
Stable tag: 1.1.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
CookieFox is a performant and accessible cookie notice and consent solution for WordPress.

== Description ==

CookieFox helps you to make your WordPress site compliant with privacy laws such as GDPR by providing an easy-to-use and customizable cookie notice. It is optimized for performance and to have a minimal impact on loading times.

== Key Features ==

- The cookie notice can either be displayed as a modal or a banner on the bottom of the viewport.

- Basic design customizations (fonts, colors, …) are available in the admin settings. Advanced customizations are possible through the use of CSS variables or by providing a custom stylesheet.

- All texts and buttons can be customized to fit your requirements.

- Configured scripts and services can be executed when a user accepts or rejects the use of cookies.

- Multi-Language Support: Use WPML or Polylang to easily translate your privacy notice.

- Block Auto-Embedding: Optionally, you can prevent external content from being automatically embedded. The content will be embedded when accepting the privacy notice.

== Frequently Asked Questions ==

= Does CookieFox block cookies? =
No, CookieFox does not block cookies. Scripts that would set a cookie can be defined in the admin settings to only load when a user has accepted the use of cookies. 

= Why is the cookie notice not being displayed? =
Please check in the admin settings if the toggle for "Enable Cookie Notice" is switched to "on". Also, check your website with browser development tools regarding errors. If you find an incompatibility or bug, please use the plugins support forum.

= Is my site compliant after installing and activating CookieFox? =
Please note that you are responsible for complying with your local and international laws. CookieFox can help you to be compliant by providing a cookie notice and the possibility for users to opt-in to defined scripts and services, but installation and activation alone (without configuration) are probably not enough to be compliant.

== Screenshots ==
1. Cookie notice displayed as a banner
2. Cookie notice displayed as a modal
3. Configuration of cookie texts
4. Configuration of opt-in and opt-out scripts
5. Design section in the admin settings
6. Consent cookie & Advanced performance settings 

== Changelog ==

= 1.1.1 =
* Auto-block embeds
* Renaming of some CSS variables for consistency. If you've customized your variables, please update them to reflect the changes.

= 1.0.6 =
* German translation
* Styling changes and small bug fixes.

= 1.0.5 =
* WPML support
* Polylang support
* Design section is no hidden when styles are not included

= 1.0.4 =
* Fixed an issue where it wasn't possible to dismiss the modal notice.
* Fixed an issue where it wasn't possible to show the modal on the privacy page.
* Privacy notice can now be opened via Javascript cookiefox.api.show().
* Introduction of a shortcode to display a link/button to open the privacy notice: [cookiefox_show_notice].
* Smaller typos fixes, styling changes, and bug fixes.

= 1.0.3 =
* Styling changes and small bug fixes.

= 1.0.2 =
* Option to disable the privacy notice on the privacy policy page.
* Styling changes and small bug fixes.

= 1.0.1 =
* Styling changes and small bug fixes.

= 1.0.0 =
* First public release of CookieFox.

== Upgrade Notice ==
This plugin has been tested with WordPress 5.6.1

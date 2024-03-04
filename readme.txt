=== CookieFox - Cookie Notice ===
Contributors: fabianpimminger
Donate link: http://paypal.me/fabianpimminger
Tags: privacy, cookie, cookie notice, cookie banner, cookie consent, GDPR, CCPA
Requires at least: 5.0
Tested up to: 6.4.3
Stable tag: 2.0.10
Requires PHP: 7.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
CookieFox is a performant and accessible cookie notice and consent solution for WordPress.

== Description ==

CookieFox helps you to make your WordPress site compliant with privacy laws such as GDPR by providing an easy-to-use and customizable cookie notice. It is optimized for performance and to have a minimal impact on loading times.

== Key Features ==

- The cookie notice can either be displayed as a modal or a banner on the bottom of the viewport.

- Two consent types available: Simple consent offers a general accept/deny option. Category consent offers separate consent by cookie categories.

- Basic design customizations (fonts, colors, …) are available in the admin settings. Advanced customizations are possible through the use of CSS variables or by providing a custom stylesheet.

- All texts and buttons can be customized to fit your requirements.

- Configured scripts and services can be executed when a user accepts or rejects the use of cookies.

- Multi-Language Support: Use WPML or Polylang to easily translate your privacy notice.

- Block Auto-Embedding: Optionally, you can prevent external content from being automatically embedded. The content will be embedded when accepting the privacy notice.

== Performance ==

- Javascript: There's just one 11kb (gzip) Javascript file. No dependencies. No jQuery.
- CSS: You can either use an external CSS file (2.5kb, gzip), inline all styles, or provide a custom stylesheet. 
- HTML: CookieFox just injects only a single \<div\> tag in the `wp_footer` hook. It is used as a root tag for initiating the script.

== Documentation, API & How-Tos ==

Documentation and tutorials/samples are available in the [Wiki](https://github.com/fabianpimminger/cookiefox/wiki).


== Frequently Asked Questions ==

= Does CookieFox block cookies? =
No, CookieFox does not block cookies. Scripts that would set a cookie can be defined in the admin settings to only load when a user has accepted the use of cookies. 

= Why is the cookie notice not being displayed? =
Please check in the admin settings if the toggle for "Enable Cookie Notice" is switched to "on". Also, check your website with browser development tools regarding errors. If you find an incompatibility or bug, please use the plugins support forum.

= Is my site compliant after installing and activating CookieFox? =
Please note that you are responsible for complying with your local and international laws. CookieFox can help you to be compliant by providing a cookie notice and the possibility for users to opt-in to defined scripts and services, but installation and activation alone (without configuration) are probably not enough to be compliant.

== Screenshots ==
1. Cookie notice displayed as a modal
2. Cookie notice displayed as a banner
3. Configuration of cookies
4. Configuration of cookie texts
5. Configuration of opt-in and opt-out scripts
6. Design section in the admin settings
7. Consent cookie & Advanced performance settings 

== Changelog ==

= 2.0.10 =
* Fixed an error that occurred when a site doesn't use permalink rewrites.

= 2.0.9 =
* Fixed a compatibility issue with Rank Math SEO

= 2.0.8 =
* Made Cookie Information string translatable
* Privacy policy URL can be HTML (possibility to include links)
* Added duration field to cookie details

= 2.0.7 =
* Paragraphs and line breaks are now displayed correctly in cookie descriptions

= 2.0.6 =
* Fixed an error with auto-embedding and TikTok embeds
* Added API to set the consent status
* Paragraphs and line breaks are now displayed correctly in category descriptions

= 2.0.5 =
* Added setting for delaying the cookie notice
* Added API to get the current consent status
* Added API to subscribe to consent change events
* Added API to subscribe to the init event

= 2.0.4 =
* Added API for handling consent changes for dynamically inserted content/embeds.

= 2.0.3 =
* Enable Decline-Button in Category Consent Mode.

= 2.0.2 =
* Consent cookie now uses sameSite=strict
* Fixed an error in language detection

= 2.0.1 =
* Category based cookie consent
* Fixed accessibility issues concerning tab navigation

= 1.3.1 =
* Fixed an issue that displayed a wrong primary text color

= 1.3.0 =
* More sensible default colors regarding color contrast
* Option for decline action to be a button (now the default)
* Fixed an issue where wrong language was loaded for default settings

= 1.2.1 =
* Added feature to mask script to circumvent server security measures when sending script tags in POST requests.

= 1.2.0 =
* Added feature to remove cookies after opt-out
* Always-on scripts are now executed before other scripts
* Scripts will be injected without div container tag
* Fixed an issue with embed blocking on sites with classic editor

= 1.1.3 =
* Fixed an issue that displayed "undefined" at bottom of the site

= 1.1.2 =
* Added always-on scripts
* Added cookiefox_consent filter for custom content blocking
* Fixed an issue with auto-blocking embeds
* Fixed german translations 

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
This plugin has been tested with WordPress 5.9.3

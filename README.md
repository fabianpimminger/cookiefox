# CookieFox
 
CookieFox is a performant and accessible cookie notice and consent solution for WordPress. CookieFox helps you to make your WordPress site compliant with privacy laws such as GDPR by providing an easy to use and customizable cookie notice. It is optimized for performance and to have a minimal impact on loading times.

## Installation

Install CookieFox directly in your Wordpress Dashboard or download the plugin in the [Plugin Directory](https://wordpress.org/plugins/cookiefox/).

## Performance

* **Javascript**: There's just one 4kb (gzip) Javascript file. No dependencies. No jQuery.
* **CSS**: You can either use an external CSS file (1.6kb, gzip), inline all styles or provide your own stylesheet. 
* **HTML**: CookieFox just injects only a single \<div\> tag in the `wp_footer` hook. It is used as a root tag for initiating the script. 

## Key Features

* Minimal impact on site loading times. 
* The cookie notice can either be displayed as a modal or a banner on the bottom of the viewport.
* Basic design customizations (fonts, colors, …) are available in the admin settings. Advanced customizations are possible through the use of CSS variables or by providing your own stylesheet.
* All texts and buttons can be customized to fit your requirements.
* Configured scripts and services can be executed when a user accepts or rejects the use of cookies.
* Multi-Language Support: Use WPML or Polylang to easily translate your privacy notice.
* Block Auto-Embedding: Optionally, you can prevent external content from being automatically embedded. The content will be embedded when accepting the privacy notice.

## Frequently Asked Questions

### Does CookieFox block cookies?
No, CookieFox does not block cookies. Scripts that would set a cookie can be defined in the admin settings to only load when an user has accepted the use of cookies. 

### Why is the cookie notice not being displayed?
Please check in the admin settings if the toggle for "Enable Cookie Notice" is switched to "on". Also check your website with browser development tools regarding errors. If you find an incompatibility or bug, please use the plugins support forum.

### Is my site compliant after installing and activating CookieFox?
Please note that you are responsible for complying with your local and international laws. CookieFox can help you to be compliant by providing a cookie notice and the possibility for users to opt-in to defined scripts and services, but installation and activation alone (without configuration) are probably not enough to be compliant.
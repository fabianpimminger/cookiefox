<?php
/**
 * Plugin Name: CookieFox
 * Description: A performant and accessible cookie and consent solution for WordPress.
 * Version: 2.0.10
 * Requires at least: 5.0
 * Tested up to: 6.4.3
 * Requires PHP: 7.3
 * Author: Fabian Pimminger
 * Author URI: https://fabianpimminger.com/work/
 * License: GPL2
 * Text Domain: cookiefox
 *
 * @package CookieFox
 */

defined('ABSPATH') || exit;

if (! defined('COOKIEFOX_PLUGIN_FILE')) {
	define('COOKIEFOX_PLUGIN_FILE', __FILE__);
}

if (! class_exists('\CookieFox\Main')) {
	include_once dirname(__FILE__) . '/includes/class-main.php';
}

<?php
/**
 * CookieFox Setup
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Main {
	protected static $_instance = null;

	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		$this->constants();
		$this->autoload();
		$this->includes();
		register_activation_hook(COOKIEFOX_PLUGIN_FILE, array($this, 'activation_hook'));
		add_action('init', array($this, 'load_textdomain'));
	}

	private function constants() {
		define('COOKIEFOX_VERSION', '1.0.0');
		define('COOKIEFOX_ABSPATH', dirname(COOKIEFOX_PLUGIN_FILE) . '/');
		if (! defined('COOKIEFOX_COMPOSER_ABSPATH')) {
			define('COOKIEFOX_COMPOSER_ABSPATH', dirname(COOKIEFOX_PLUGIN_FILE) . '/vendor/');
		}
	}
	
	private function autoload() {
		include_once COOKIEFOX_COMPOSER_ABSPATH . 'autoload.php';
	}

	private function includes() {
		include_once COOKIEFOX_ABSPATH . 'includes/class-helper.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-admin.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-cmb2.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-post_type.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-settings.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-frontend.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-sample_content.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-shortcode.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-embeds.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-internationalization.php';
		include_once COOKIEFOX_ABSPATH . 'includes/class-rest_api.php';
		include_once COOKIEFOX_ABSPATH . 'includes/cmb2/class-cmb2-toggle.php';
		include_once COOKIEFOX_ABSPATH . 'includes/cmb2/class-cmb2-button.php';
	}

	public static function plugin_url() {
		return untrailingslashit(plugins_url('/', COOKIEFOX_PLUGIN_FILE));
	}

	public function activation_hook() {
		$this->load_textdomain();
		Settings::register_defaults();
	}
	
	public function load_textdomain() {
	  load_plugin_textdomain( 'cookiefox', false, dirname( plugin_basename( COOKIEFOX_PLUGIN_FILE ) ) . '/languages' ); 
	}
}
Main::instance();

<?php
/**
 * Integrations.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Integrations {
	public function __construct() {
		include_once COOKIEFOX_ABSPATH . 'includes/integrations/class-integration.php';

		foreach ($this->active_integration_files() as $file) {
			$path = COOKIEFOX_ABSPATH . 'includes/integrations/' . $file;
			if (is_readable($path)) {
				include_once $path;
			}
		}

		add_action('wp', array($this, 'do_integrations'));
	}

	public function do_integrations() {
		if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || is_feed()) {
			return;
		}

		if (Helper::get_option('cookie_notice_enabled', false) !== 'on') {
			return;
		}

		try {
			if (Helper::get_option('consent_type') === 'category') {
				$this->do_integrations_category();
			} else {
				$this->do_integrations_simple();
			}
		} catch (\Throwable $e) {
			return;
		}
	}

	public static function consent_script($slug) {
		if (!is_string($slug) || $slug === '') {
			return '';
		}

		try {
			$script = apply_filters("cookiefox_consent_{$slug}", '');
		} catch (\Throwable $e) {
			return '';
		}

		return is_string($script) ? $script : '';
	}

	private function do_integrations_simple() {
		$integrations = Helper::get_option('integrations', array());
		if (!is_array($integrations)) {
			return;
		}

		foreach ($integrations as $slug) {
			$this->run_integration($slug);
		}
	}

	private function do_integrations_category() {
		if (!post_type_exists('cookiefox_cookie')) {
			return;
		}

		$cookies = get_posts(array(
			'post_type' => 'cookiefox_cookie',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_key' => 'type',
			'meta_value' => 'integration',
		));

		if (empty($cookies) || is_wp_error($cookies)) {
			return;
		}

		foreach ($cookies as $cookie_id) {
			$slug = get_post_meta($cookie_id, 'integration', true);
			$this->run_integration($slug);
		}
	}

	private function run_integration($slug) {
		if (!is_string($slug) || $slug === '') {
			return;
		}

		try {
			do_action("cookiefox_integration_{$slug}", $slug);
		} catch (\Throwable $e) {
			return;
		}
	}

	private function active_integration_files() {
		$plugin_files = array(
			'ga-google-analytics' => 'class-ga-google-analytics.php',
			'google-analytics-for-wordpress' => 'class-monsterinsights.php',
			'google-analytics-premium' => 'class-monsterinsights.php',
			'google-analytics-dashboard-for-wp' => 'class-exactmetrics.php',
			'exactmetrics-premium' => 'class-exactmetrics.php',
			'google-site-kit' => 'class-google-site-kit.php',
			'jetpack' => 'class-jetpack.php',
			'matomo' => 'class-matomo.php',
			'wp-piwik' => 'class-wp-piwik.php',
		);

		$files = array();
		foreach ($plugin_files as $plugin => $file) {
			if ($this->plugin_is_active($plugin)) {
				$files[$file] = true;
			}
		}

		return array_keys($files);
	}

	private function plugin_is_active($folder) {
		$active = (array) get_option('active_plugins', array());
		if (is_multisite()) {
			$network = get_site_option('active_sitewide_plugins', array());
			if (is_array($network)) {
				$active = array_merge($active, array_keys($network));
			}
		}

		foreach ($active as $file) {
			if (!is_string($file)) {
				continue;
			}
			$name = (false === strpos($file, '/')) ? basename($file, '.php') : dirname($file);
			if ($name === $folder) {
				return true;
			}
		}

		return false;
	}
}
new Integrations();

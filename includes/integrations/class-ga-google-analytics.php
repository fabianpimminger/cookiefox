<?php
/**
 * Integration: GA Google Analytics (Jeff Starr).
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

defined('ABSPATH') || exit;

class GA_Google_Analytics extends Integration {
	private $slug = 'ga-google-analytics';
	private $done = false;

	public function __construct() {
		add_filter('cookiefox_integrations', array($this, 'add_integration'));
		add_filter("cookiefox_consent_{$this->slug}", array($this, 'consent'));
		add_action("cookiefox_integration_{$this->slug}", array($this, 'do_integration'));
	}

	public function add_integration($integrations) {
		if (!is_array($integrations)) {
			$integrations = array();
		}
		$integrations[$this->slug] = 'GA Google Analytics';
		return $integrations;
	}

	public function consent() {
		return '<script>if(window.cookiefox&&window.cookiefox.api&&window.cookiefox.api.activateIntegration){window.cookiefox.api.activateIntegration(' . wp_json_encode($this->slug) . ');}</script>';
	}

	public function do_integration() {
		if ($this->done) {
			return;
		}

		$hooks = array('wp_head', 'wp_footer', 'admin_head', 'admin_footer');
		foreach ($hooks as $hook) {
			if (has_action($hook, 'ga_google_analytics_tracking_code')) {
				remove_action($hook, 'ga_google_analytics_tracking_code');
				add_action($hook, array($this, 'render_blocked'));
			}
		}

		$this->done = true;
	}

	public function render_blocked() {
		if (!function_exists('ga_google_analytics_tracking_code')) {
			return;
		}

		ob_start();
		ga_google_analytics_tracking_code();
		echo $this->block_scripts(ob_get_clean());
	}

	private function block_scripts($html) {
		if (!is_string($html) || $html === '') {
			return $html;
		}

		$html = preg_replace('/\s+type=(["\']).*?\1/i', '', $html);
		$attr = ' type="text/plain" data-cookiefox-integration="' . esc_attr($this->slug) . '"';

		return preg_replace('/<script\b/i', '<script' . $attr, $html);
	}
}
new GA_Google_Analytics();

<?php
/**
 * Integration: Matomo Analytics.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

defined('ABSPATH') || exit;

class Matomo extends Integration {
	private $slug = 'matomo';
	private $done = false;
	private $tracker = null;

	public function __construct() {
		add_filter('cookiefox_integrations', array($this, 'add_integration'));
		add_filter("cookiefox_consent_{$this->slug}", array($this, 'consent'));
		add_action("cookiefox_integration_{$this->slug}", array($this, 'do_integration'));
	}

	public function add_integration($integrations) {
		if (!is_array($integrations)) {
			$integrations = array();
		}
		$integrations[$this->slug] = 'Matomo Analytics';
		return $integrations;
	}

	public function consent() {
		return '<script>if(window.cookiefox&&window.cookiefox.api&&window.cookiefox.api.activateIntegration){window.cookiefox.api.activateIntegration(' . wp_json_encode($this->slug) . ');}window._paq=window._paq||[];window._paq.push(["setConsentGiven"]);window._paq.push(["setCookieConsentGiven"]);</script>';
	}

	public function do_integration() {
		if ($this->done) {
			return;
		}

		$tracker = $this->tracker();
		if (!$tracker) {
			return;
		}

		$this->tracker = $tracker;
		foreach (array('wp_head', 'wp_footer') as $hook) {
			$priority = has_action($hook, array($tracker, 'add_javascript_code'));
			if ($priority !== false) {
				remove_action($hook, array($tracker, 'add_javascript_code'), $priority);
				add_action($hook, array($this, 'render_blocked'), $priority);
			}

			$noscript = has_action($hook, array($tracker, 'add_noscript_code'));
			if ($noscript !== false) {
				remove_action($hook, array($tracker, 'add_noscript_code'), $noscript);
			}
		}

		$this->done = true;
	}

	public function render_blocked() {
		if (!$this->tracker) {
			return;
		}

		ob_start();
		$this->tracker->add_javascript_code();
		echo $this->block_scripts(ob_get_clean());
	}

	private function block_scripts($html) {
		if (!is_string($html) || $html === '') {
			return $html;
		}

		$html = preg_replace('/\s+type=(["\']).*?\1/i', '', $html);
		$attr = ' type="text/plain" data-cookiefox-integration="' . esc_attr($this->slug) . '"';
		$blocked = preg_replace('/<script\b/i', '<script' . $attr, $html);

		return is_string($blocked) ? $blocked : $html;
	}

	private function tracker() {
		if (class_exists('\WpMatomo') && method_exists('\WpMatomo', 'get_active_feature') && class_exists('\WpMatomo\TrackingCode')) {
			try {
				$feature = \WpMatomo::get_active_feature(\WpMatomo\TrackingCode::class);
				if ($feature instanceof \WpMatomo\TrackingCode) {
					return $feature;
				}
			} catch (\Throwable $e) {
				// Fall through to hook lookup.
			}
		}

		return $this->tracker_from_hooks();
	}

	private function tracker_from_hooks() {
		if (!class_exists('\WpMatomo\TrackingCode')) {
			return null;
		}

		foreach (array('wp_head', 'wp_footer') as $hook) {
			if (empty($GLOBALS['wp_filter'][$hook]) || !is_object($GLOBALS['wp_filter'][$hook])) {
				continue;
			}

			$hook_obj = $GLOBALS['wp_filter'][$hook];
			if (empty($hook_obj->callbacks) || !is_array($hook_obj->callbacks)) {
				continue;
			}

			foreach ($hook_obj->callbacks as $callbacks) {
				if (!is_array($callbacks)) {
					continue;
				}

				foreach ($callbacks as $cb) {
					if (!is_array($cb) || !isset($cb['function'])) {
						continue;
					}

					$fn = $cb['function'];
					if (is_array($fn) && isset($fn[0], $fn[1]) && is_object($fn[0]) && $fn[1] === 'add_javascript_code' && $fn[0] instanceof \WpMatomo\TrackingCode) {
						return $fn[0];
					}
				}
			}
		}

		return null;
	}
}
new Matomo();

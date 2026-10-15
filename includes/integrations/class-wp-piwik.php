<?php
/**
 * Integration: Connect Matomo.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

defined('ABSPATH') || exit;

class Connect_Matomo extends Integration {
	private $slug = 'wp-piwik';
	private $done = false;
	private $plugin = null;

	public function __construct() {
		add_filter('cookiefox_integrations', array($this, 'add_integration'));
		add_filter("cookiefox_consent_{$this->slug}", array($this, 'consent'));
		add_action("cookiefox_integration_{$this->slug}", array($this, 'do_integration'));
	}

	public function add_integration($integrations) {
		if (!is_array($integrations)) {
			$integrations = array();
		}
		$integrations[$this->slug] = 'Connect Matomo';
		return $integrations;
	}

	public function consent() {
		return '<script>if(window.cookiefox&&window.cookiefox.api&&window.cookiefox.api.activateIntegration){window.cookiefox.api.activateIntegration(' . wp_json_encode($this->slug) . ');}window._paq=window._paq||[];window._paq.push(["setConsentGiven"]);window._paq.push(["setCookieConsentGiven"]);</script>';
	}

	public function do_integration() {
		if ($this->done) {
			return;
		}

		$plugin = $this->plugin();
		if (!$plugin) {
			return;
		}

		$this->plugin = $plugin;
		foreach (array('wp_head', 'wp_footer') as $hook) {
			$priority = has_action($hook, array($plugin, 'add_javascript_code'));
			if ($priority !== false) {
				remove_action($hook, array($plugin, 'add_javascript_code'), $priority);
				add_action($hook, array($this, 'render_blocked'), $priority);
			}

			$noscript = has_action($hook, array($plugin, 'add_noscript_code'));
			if ($noscript !== false) {
				remove_action($hook, array($plugin, 'add_noscript_code'), $noscript);
			}
		}

		$this->done = true;
	}

	public function render_blocked() {
		if (!$this->plugin) {
			return;
		}

		ob_start();
		$this->plugin->add_javascript_code();
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

	private function plugin() {
		if (isset($GLOBALS['wp-piwik']) && is_object($GLOBALS['wp-piwik']) && class_exists('\WP_Piwik') && $GLOBALS['wp-piwik'] instanceof \WP_Piwik) {
			return $GLOBALS['wp-piwik'];
		}

		return $this->plugin_from_hooks();
	}

	private function plugin_from_hooks() {
		if (!class_exists('\WP_Piwik')) {
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
					if (is_array($fn) && isset($fn[0], $fn[1]) && is_object($fn[0]) && $fn[1] === 'add_javascript_code' && $fn[0] instanceof \WP_Piwik) {
						return $fn[0];
					}
				}
			}
		}

		return null;
	}
}
new Connect_Matomo();

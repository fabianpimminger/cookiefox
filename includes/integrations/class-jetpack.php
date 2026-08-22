<?php
/**
 * Integration: Jetpack Stats.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

defined('ABSPATH') || exit;

class Jetpack extends Integration {
	private $slug = 'jetpack';
	private $done_stats = false;

	public function __construct() {
		add_filter('cookiefox_integrations', array($this, 'add_integration'));
		add_filter("cookiefox_consent_{$this->slug}--stats", array($this, 'consent_stats'));
		add_action("cookiefox_integration_{$this->slug}--stats", array($this, 'do_integration_stats'));
	}

	public function add_integration($integrations) {
		if (!is_array($integrations)) {
			$integrations = array();
		}

		if (!$this->stats_module_active()) {
			return $integrations;
		}

		$integrations["{$this->slug}--stats"] = 'Jetpack - Stats';
		return $integrations;
	}

	public function consent_stats() {
		$slug = $this->slug . '--stats';
		return '<script>if(window.cookiefox&&window.cookiefox.api&&window.cookiefox.api.activateIntegration){window.cookiefox.api.activateIntegration(' . wp_json_encode($slug) . ');}</script>';
	}

	public function do_integration_stats() {
		if ($this->done_stats) {
			return;
		}

		add_filter('script_loader_tag', array($this, 'script_loader_tag'), 10, 2);
		add_filter('wp_inline_script_attributes', array($this, 'inline_script_attributes'), 10, 2);
		$this->done_stats = true;
	}

	public function script_loader_tag($tag, $handle) {
		if (!is_string($tag)) {
			return $tag;
		}

		if (strpos($tag, 'data-cookiefox-integration') !== false) {
			return $tag;
		}

		$is_handle = ($handle === 'jetpack-stats');
		$is_src = (strpos($tag, 'stats.wp.com/e-') !== false);

		if (!$is_handle && !$is_src) {
			return $tag;
		}

		if (!$is_src && strpos($tag, 'src=') === false) {
			return $tag;
		}

		$tag = preg_replace('/\s+type=(["\']).*?\1/i', '', $tag);
		return preg_replace('/<script\b/i', '<script type="text/plain" data-cookiefox-integration="' . esc_attr($this->slug . '--stats') . '"', $tag, 1);
	}

	public function inline_script_attributes($attributes, $data = '') {
		if (!is_array($attributes)) {
			return $attributes;
		}

		$id = isset($attributes['id']) ? $attributes['id'] : '';
		if ($id !== 'jetpack-stats-js-before' && $id !== 'jetpack-stats-js-after') {
			return $attributes;
		}

		$attributes['type'] = 'text/plain';
		$attributes['data-cookiefox-integration'] = $this->slug . '--stats';
		return $attributes;
	}

	private function stats_module_active() {
		try {
			if (class_exists('\Jetpack') && method_exists('\Jetpack', 'is_module_active')) {
				return (bool) \Jetpack::is_module_active('stats');
			}

			if (class_exists('\Automattic\Jetpack\Modules')) {
				return (bool) (new \Automattic\Jetpack\Modules())->is_active('stats');
			}
		} catch (\Throwable $e) {
			return false;
		}

		return false;
	}
}
new Jetpack();

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

		add_filter('script_loader_tag', array($this, 'script_loader_tag'), 10, 3);
		add_filter('wp_script_attributes', array($this, 'script_attributes'));
		add_filter('wp_inline_script_attributes', array($this, 'inline_script_attributes'), 10, 2);
		$this->done_stats = true;
	}

	public function script_attributes($attributes) {
		if (!is_array($attributes) || !$this->is_stats_src_attributes($attributes)) {
			return $attributes;
		}

		$attributes['type'] = 'text/plain';
		$attributes['data-cookiefox-integration'] = $this->slug . '--stats';
		return $attributes;
	}

	public function script_loader_tag($tag, $handle, $src = '') {
		if (!is_string($tag)) {
			return $tag;
		}

		$is_handle = ($handle === 'jetpack-stats');
		$is_src = (is_string($src) && strpos($src, 'stats.wp.com') !== false) || strpos($tag, 'stats.wp.com') !== false;

		if (!$is_handle && !$is_src) {
			return $tag;
		}

		return preg_replace_callback('/<script\b([^>]*)>/i', array($this, 'block_script_open_tag'), $tag);
	}

	public function block_script_open_tag($matches) {
		$attrs = $matches[1];

		if (strpos($attrs, 'data-cookiefox-integration') !== false) {
			return $matches[0];
		}

		$has_stats_src = (strpos($attrs, 'stats.wp.com') !== false);
		$has_stats_id = (bool) preg_match('/\bid=(["\'])jetpack-stats-js\1/', $attrs);

		if (!$has_stats_src && !$has_stats_id) {
			return $matches[0];
		}

		$attrs = preg_replace('/\s+type=(["\']).*?\1/i', '', $attrs);
		return '<script type="text/plain" data-cookiefox-integration="' . esc_attr($this->slug . '--stats') . '"' . $attrs . '>';
	}

	private function is_stats_src_attributes($attributes) {
		$id = isset($attributes['id']) ? $attributes['id'] : '';
		$src = isset($attributes['src']) ? $attributes['src'] : '';

		if ($id === 'jetpack-stats-js') {
			return true;
		}

		return is_string($src) && strpos($src, 'stats.wp.com') !== false;
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

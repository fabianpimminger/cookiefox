<?php
/**
 * Integration: MonsterInsights.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

defined('ABSPATH') || exit;

class Monsterinsights extends Integration {
	private $slug = 'monsterinsights';
	private $script_handle = 'monsterinsights-frontend-script';
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
		$integrations[$this->slug] = 'MonsterInsights';
		return $integrations;
	}

	public function consent() {
		return '<script>if(window.cookiefox&&window.cookiefox.api&&window.cookiefox.api.activateIntegration){window.cookiefox.api.activateIntegration(' . wp_json_encode($this->slug) . ');}</script>';
	}

	public function do_integration() {
		if ($this->done) {
			return;
		}

		add_filter('monsterinsights_tracking_analytics_script_attributes', array($this, 'script_atts'));
		add_filter('script_loader_tag', array($this, 'script_loader_tag'), 10, 3);
		add_filter('wp_script_attributes', array($this, 'enqueue_script_attributes'));
		$this->done = true;
	}

	public function enqueue_script_attributes($attributes) {
		if (!is_array($attributes)) {
			return $attributes;
		}

		$id = isset($attributes['id']) ? $attributes['id'] : '';
		if ($id !== $this->script_handle . '-js') {
			return $attributes;
		}

		$attributes['type'] = 'text/plain';
		$attributes['data-cookiefox-integration'] = $this->slug;
		return $attributes;
	}

	public function script_loader_tag($tag, $handle, $src = '') {
		if (!is_string($tag) || $handle !== $this->script_handle) {
			return $tag;
		}

		return preg_replace_callback('/<script\b([^>]*)>/i', array($this, 'block_script_open_tag'), $tag);
	}

	public function block_script_open_tag($matches) {
		$attrs = $matches[1];
		if (strpos($attrs, 'data-cookiefox-integration') !== false) {
			return $matches[0];
		}

		$has_src = (strpos($attrs, 'frontend-gtag') !== false);
		$has_id = (bool) preg_match('/\bid=(["\'])' . preg_quote($this->script_handle . '-js', '/') . '\1/', $attrs);
		if (!$has_src && !$has_id) {
			return $matches[0];
		}

		$attrs = preg_replace('/\s+type=(["\']).*?\1/i', '', $attrs);
		return '<script type="text/plain" data-cookiefox-integration="' . esc_attr($this->slug) . '"' . $attrs . '>';
	}

	public function script_atts($atts) {
		if (!is_array($atts)) {
			$atts = array();
		}

		return array_merge($atts, array(
			'type' => 'text/plain',
			'data-cookiefox-integration' => $this->slug,
		));
	}
}
new Monsterinsights();

<?php
/**
 * Integration: ExactMetrics.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

defined('ABSPATH') || exit;

class Exactmetrics extends Integration {
	private $slug = 'exactmetrics';
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
		$integrations[$this->slug] = 'ExactMetrics';
		return $integrations;
	}

	public function consent() {
		return '<script>if(window.cookiefox&&window.cookiefox.api&&window.cookiefox.api.activateIntegration){window.cookiefox.api.activateIntegration(' . wp_json_encode($this->slug) . ');}</script>';
	}

	public function do_integration() {
		if ($this->done) {
			return;
		}

		add_filter('exactmetrics_tracking_analytics_script_attributes', array($this, 'script_atts'));
		$this->done = true;
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
new Exactmetrics();

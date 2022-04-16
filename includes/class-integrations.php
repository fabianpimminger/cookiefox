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
		add_filter('script_loader_tag', array($this, 'script_loader_tag'), 10, 2);
		add_filter('ga_google_analytics_script_atts', array($this, 'ga_google_analytics_script_atts'));
		add_filter('ga_google_analytics_script_atts_ext', array($this, 'ga_google_analytics_script_atts'));
		
	}
	
	public function init() {
	}
	
	public function script_loader_tag($tag, $handle) {
		return $tag;
	}
	
	public function ga_google_analytics_script_atts($atts) {
		$atts = $atts." type=text/plain data-cookiefox-consent=google-analytics";
		
		return $atts;
	}

}
new Integrations();

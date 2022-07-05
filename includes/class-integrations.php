<?php
/**
 * Integrations.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Integrations {
	
	private $plugins;
	
	public function __construct() {
		$plugin_integrations = array(
			"ga-google-analytics" => "class-ga-google-analytics.php",
			"google-site-kit" => "class-google-site-kit.php",
			//"jetpack" => "class-jetpack.php",
			"google-analytics-for-wordpress" => "class-monsterinsights.php",
			"google-analytics-dashboard-for-wp" => "class-exactmetrics.php",
		);
		
		$active_plugins = get_option('active_plugins');
		
		$active_plugins = array_map(array($this, "get_plugin_name"), $active_plugins);
		include_once COOKIEFOX_ABSPATH . 'includes/integrations/class-integration.php';
		
		foreach($plugin_integrations as $key => $value){
			if(in_array($key, $active_plugins)){
				include_once COOKIEFOX_ABSPATH . 'includes/integrations/'.$value;
			}
		}
		
		$this->do_integrations();
		
	}
	
	private function get_plugin_name( $basename ) {
		if ( false === strpos( $basename, '/' ) ) {
			$name = basename( $basename, '.php' );
		} else {
			$name = dirname( $basename );
		}
	
		return $name;
	}

	
	public function do_integrations() {
		if(is_admin()){
			return;
		}
		
		$consent_type = Helper::get_option("consent_type");		
		
		if($consent_type == "simple"){
			$this->do_integrations_simple();
			add_filter("cookiefox_frontend_prepare_data", array($this, "simple_consent_prepare_data"));
		} else {
			$this->do_integrations_category();
		}
		
	}
	
	private function do_integrations_simple() {
		$integrations = Helper::get_option("integrations");

		foreach($integrations as $integration){
			do_action("cookiefox_integration_{$integration}", $integration);
		}
	}
	
	private function do_integrations_category() {
		$query_vars = array(
			"post_type" => "cookiefox_cookie",
			"posts_per_page" => -1,
			"meta_query" => array(
				array(
					"key" => "type",
          'value' => 'integration',
				)
			),
		);
		
  	$cookies = get_posts($query_vars);
		
		foreach($cookies as $cookie){
			$integration = get_post_meta($cookie->ID, "integration", true);
			$slug = get_post_field('post_name', $cookie->ID);
			do_action("cookiefox_integration_{$integration}", $slug);
		}
		
	}
	
	public function simple_consent_prepare_data($data) {
		if(empty($data["integrations"])){
			return $data;
		}
		
		foreach($data["integrations"] as $integration){
			$data["scripts_consent"] .= apply_filters("cookiefox_consent_{$integration}", "");
		}
				
		return $data;
	}

}
new Integrations();
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

}
new Integrations();
<?php
/**
 * Rest_API.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Rest_API {
	public function __construct() {
		add_action( 'rest_api_init', array($this, 'init'));
	}
	
	public function init() {
	  register_rest_route( 'cookiefox/v1', '/cookies', array(
	    'methods' => 'GET',
	    'callback' => array($this, 'endpoint_cookies'),
			'permission_callback' => '__return_true'
	  ) );
	}
	
	public function rest_endpoint_setup() {
		do_action("cookiefox_internationalization_init");
	}

	public function endpoint_cookies(\WP_REST_Request $request) {		
		$this->rest_endpoint_setup();
		
		$response = array("categories" => []);
		
		$categories = get_terms(array(
	    'taxonomy' => 'cookiefox_category',
	    'hide_empty' => false,
      'orderby' => 'meta_value_num',
			'meta_key' => 'order',
		));
		
		foreach($categories as $i => $cat){
			$category = array(
				"name" => $cat->name,
				"slug" => $cat->slug,
				"description" => wp_kses_post(wpautop($cat->description)),
				"order" => $i,
				"cookies" => array(),
				"always_on" => false,
				"integrations" => array(),
			);
			
			$query_vars = array(
				"post_type" => "cookiefox_cookie",
				"tax_query" => array(
					array(
						"taxonomy" => "cookiefox_category",
            'field' => 'term_id',
            'terms' => $cat->term_id,
					)
				),
      	"orderby" => "menu_order",
				"order" => "asc",
			);
			
	  	$cookies = new \WP_Query($query_vars);
			
			while ( $cookies->have_posts() ) { 
				$cookies->the_post();
				
				
				$cookie = array(
					"name" => get_the_title(),
					"slug" => get_post_field( 'post_name', get_post()),
				);
				
				$meta_data = get_post_meta(get_the_ID());
				$include_meta_keys = array("vendor", "description", "privacy_policy", "scripts_consent", "scripts_no_consent", "scripts_always", "cookies", "duration");
				
				foreach($include_meta_keys as $meta_key){
					if(!empty($meta_data[$meta_key])){
						$meta_value = $meta_data[$meta_key][0];
						
						if($meta_value == "off"){
							$meta_value = false;
						}else if($meta_value == "on"){
							$meta_value = true;
						}
						
						if(in_array($meta_key, array("scripts_consent", "scripts_no_consent", "scripts_always"))){
							$meta_value = apply_filters( "cookiefox_prepare_scripts", $meta_value);
						}
						
						if($meta_key == "description"){
							$meta_value = wp_kses_post(wpautop($meta_value));
						}
						
						$cookie[$meta_key] = $meta_value;
					} else {
						$cookie[$meta_key] = null;
					}
				}
				
				$category["cookies"][] = $cookie;
			}
			$always_on = get_term_meta( $cat->term_id, "always_on", true );
			if(!empty($always_on) && $always_on == "on"){
				$category["always_on"] = true;
			}

			$unblock_embeds = get_term_meta( $cat->term_id, "unblock_embeds", true );
			if(!empty($unblock_embeds) && $unblock_embeds == "on"){
				$category["integrations"]["unblock_embeds"] = true;
			}
			
			$response["categories"][] = $category;
			
		}
	
		return $response;
	}
}
new Rest_API();

<?php
/**
 * Frontend.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Shortcode {
	public function __construct() {
		add_action("init", array($this, "init"));
	}
	
	public function init() {		
    add_shortcode( 'cookiefox_show_notice', array($this, 'show_notice') );		
	}
	
	function show_notice( $atts = [], $content = null, $tag = '' ) {

    $atts = array_change_key_case( (array) $atts, CASE_LOWER );
 
    $attributes = shortcode_atts(
        array(
            "type" => "link",
						"text" => __("Show Privacy Settings", "cookiefox"),
        ), $atts, $tag
    );
		
		$element = "";
		
		if($attributes["type"] == "link"){
			$element = '<a href="javascript:void(0)" onclick="cookiefox.api.show()">%s</a>';
		} else {
			$element = '<button onclick="cookiefox.api.show()">%s</button>';
		}
		
		$output = sprintf($element, esc_html($attributes["text"]));
		
    return $output;
	}
}
new Shortcode();

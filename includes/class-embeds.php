<?php
/**
 * Embed.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Embeds {
	public function __construct() {
		add_action("init", array($this, "init"));
	}
	
	public function init() {		
		if(is_feed() || is_admin()){
			return;
		}
		
		$option_enabled = Helper::get_option("cookie_notice_enabled", false);
		
		if($option_enabled !== "on"){
			return;
		}
		
		$block = Helper::get_option("block_embeds", false);
		if(!empty($block) && $block == "on"){
			add_filter('embed_oembed_html', array($this, 'oembed_html'), 10, 3);
			add_filter('video_embed_html', array($this, 'oembed_html'), 10, 3);
			add_filter('cookiefox_consent', array($this, 'filter_optin'), 10, 3);
		}
	}
	
	public function oembed_html($html, $url, $attr) {
		return $this->wrapper($html);
	}
	
	public function filter_optin($html) {
		return $this->wrapper($html);
	}
	
	private function wrapper($html){
		return '<div class="wp-block-cookiefox cookiefox cookiefox__embed is-blocked" data-embed="'.htmlspecialchars($html).'"><div class="cookiefox__embed-notice"><p>'.esc_html__('External content is hidden due to privacy reasons. It will be embedded after consent is given in the privacy settings.', 'cookiefox').'</p><div class="cookiefox__embed-footer"><button class="cookiefox__button cookiefox__button--primary is-button" onclick="window.cookiefox.api.show();">'.esc_html__('Privacy Settings', 'cookiefox').'</button></div></div></div>';
	}
}
new Embeds();

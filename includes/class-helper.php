<?php
/**
 * Helper.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Helper {
	
	private static function default_settings() {
		return array(
			'consent_type' => 'simple',
			'cookie_notice_enabled' => 'on',
			'cookie_notice_hide_on_privacy_page' => 'on',
			'notice_display' => 'banner',
			'notice_delay' => 0,
			'notice_title' => __('Privacy Settings', 'cookiefox'),
			'notice_text' => __('We use cookies to improve your experience on our site. To learn more, view our privacy policy.', 'cookiefox'),
			'notice_button_accept' => __('Accept', 'cookiefox'),
			'notice_button_save' => __('Save', 'cookiefox'),
			'notice_button_manage' => __('Manage cookies', 'cookiefox'),
			'notice_button_decline_type' => 'button',
			'notice_button_decline' => __('Decline', 'cookiefox'),
			'block_embeds' => 'off',
			'font' => 'theme',
			'button_style' => 'rounded',
			'color_background' => '#ffffff',
			'color_text_primary' => '#000000',
			'color_text_secondary' => '#767676',
			'color_text_tertiary' => '#d8d8d8',
			'color_button_primary' => '#3D854F',
			'color_button_secondary' => '#767676',
			'cookie_name' => 'cookiefox_consent',
			'cookie_expiration' => '90',
			'stylesheet' => 'external',
			'javascript' => 'modern',
		);
	}
	
	public static function get_option($key = '', $default = false) {
		if (function_exists('cmb2_get_option')) {
			return cmb2_get_option('cookiefox', $key, $default);
		}
	
		$opts = get_option('cookiefox', $default);
	
		$val = $default;
		
	
		if ('all' == $key) {
			$val = $opts;
		} elseif (is_array($opts) && array_key_exists($key, $opts) && false !== $opts[$key]) {
			$val = $opts[$key];
		} else {
			$default_settings = self::default_settings();
				
			if(array_key_exists($key, $default_settings)){
				$val = $default_settings[$key];
			}
		}
	
		return $val;
	}
	
	public static function merge_default_settings($settings){
		$settings = array_merge(self::default_settings(), $settings);
		
		return $settings;
	}
	
	public static function get_language() {
		if(function_exists('pll_current_language')){
			return pll_current_language();
		}
		
		$wpml_language = apply_filters( 'wpml_current_language', NULL );
		if(!empty($wpml_language)){
			return $wpml_language;
		}
		
		$locale = get_locale();
	    $locale_parts = explode( '_', $locale );
		if(count($locale_parts) > 1){
			return $locale_parts[0];
		} else {
			return $locale;
		}
	}
}

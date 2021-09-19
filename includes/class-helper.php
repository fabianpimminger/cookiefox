<?php
/**
 * Helper.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Helper {
	public static function get_option($key = '', $default = false) {
		if (function_exists('cmb2_get_option')) {
			return cmb2_get_option('cookiefox', $key, $default);
		}
	
		$opts = get_option('cookiefox', $default);
	
		$val = $default;
	
		if ('all' == $key) {
			$val = $opts;
		} elseif (is_array($opts) && array_key_exists($key, $opts) && false !== $opts[ $key ]) {
			$val = $opts[ $key ];
		}
	
		return $val;
	}
	
	public static function merge_default_settings($settings){
		$settings = array_merge(array(
			'cookie_notice_enabled' => 'on',
			'cookie_notice_hide_on_privacy_page' => 'on',
			'notice_display' => 'banner',
			'notice_title' => __('Privacy Settings', 'cookiefox'),
			'notice_text' => __('We use cookies to improve your experience on our site. To learn more, view our privacy policy.', 'cookiefox'),
			'notice_button_accept' => __('Accept', 'cookiefox'),
			'notice_button_decline_type' => 'text',
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
		), $settings);
		
		return $settings;
	}
}

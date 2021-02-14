<?php
/**
 * Internationalization.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Internationalization {
	public function __construct() {
		add_action("init", array($this, "init"));
		add_filter("cookiefox_settings_field_desc", array($this, "settings_field_desc"), 10, 3);
	}
	
	public function init() {
	}
	
	public function settings_field_desc($value, $field) {
		if ($this->is_wpml_active()) {
			$value = '<span class="dashicons dashicons-translation"></span> ';
			$value .= sprintf(
				// translators: %1$s is the opening a tag
				// translators: %2$s is the closing a tag
				esc_html__('Translate this field in %1$s String Translation %2$s', 'cookiefox'),
				'<a href="'.esc_url(admin_url("admin.php?page=wpml-string-translation%2Fmenu%2Fstring-translation.php&context=admin_texts_cookiefox&search=%5Bcookiefox%5D".$field)).'">',
				'</a>'
			);
		}
		
		return $value;
	}

	public function is_multilang_plugin_active() {
		if (class_exists("SitePress") || class_exists("Polylang")) {
			return true;
		}
		
		return false;
	}
	
	public function is_wpml_active() {
		if (class_exists("SitePress")) {
			return true;
		}
		
		return false;
	}
	
	public function is_polylang_active() {
		if (class_exists("Polylang")) {
			return true;
		}
		
		return false;
	}
}
new Internationalization();

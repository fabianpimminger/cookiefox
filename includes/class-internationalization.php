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
		add_filter("cookiefox_settings_title_desc", array($this, "settings_title_desc"), 10, 3);
		add_action("cookiefox_internationalization_init", array($this, "rest_init"));
	}
	
	public function init() {
	}
	
	public function rest_init(){
		if(defined("REST_REQUEST")){
			if($this->is_multilang_plugin_active()){
				if($this->is_polylang_active()){
			    global $polylang;
					
					$current_language = pll_default_language();
					
			    if (isset($_GET['lang'])) {
						$current_language = $_GET['lang'];
				    $default = pll_default_language();
				    $langs = pll_languages_list();
							
				    if (!in_array($current_language, $langs)) {
				        $cur_lang = $default;
				    }

			    }
								
			    $polylang->curlang = $polylang->model->get_language($current_language);			
				}
			}
		}
	}

	public function settings_title_desc($value, $field) {
		if($field != "title_notice"){
			return $value;
		}
		
		if ($this->is_wpml_active()) {
			$value .= '<br><br><span class="dashicons dashicons-translation"></span> ';
			$value .= sprintf(
				// translators: %1$s is the opening a tag
				// translators: %2$s is the closing a tag
				esc_html__('Translation of this section is available via WPML in %1$sString Translation%2$s.', 'cookiefox'),
				'<a href="'.esc_url(admin_url("admin.php?page=wpml-string-translation%2Fmenu%2Fstring-translation.php&context=admin_texts_cookiefox")).'">',
				'</a>'
			);
		} elseif ($this->is_polylang_active()) {
			$value .= '<br><br><span class="dashicons dashicons-translation"></span> ';
			$value .= sprintf(
				// translators: %1$s is the opening a tag
				// translators: %2$s is the closing a tag
				esc_html__('Translation of this section is available via Polylang in %1$sStrings translations%2$s.', 'cookiefox'),
				'<a href="'.esc_url(admin_url("admin.php?page=mlang_strings&group=cookiefox")).'">',
				'</a>'
			);
		}
		
		return $value;
	}

	
	public function settings_field_desc($value, $field) {
		if ($this->is_wpml_active()) {
			$value = '<span class="dashicons dashicons-translation"></span> ';
			$value .= sprintf(
				// translators: %1$s is the opening a tag
				// translators: %2$s is the closing a tag
				esc_html__('Translate this field in %1$sString Translation%2$s.', 'cookiefox'),
				'<a href="'.esc_url(admin_url("admin.php?page=wpml-string-translation%2Fmenu%2Fstring-translation.php&context=admin_texts_cookiefox&search=%5Bcookiefox%5D".$field)).'">',
				'</a>'
			);
		} elseif ($this->is_polylang_active()) {
			$value = '<span class="dashicons dashicons-translation"></span> ';
			$value .= sprintf(
				// translators: %1$s is the opening a tag
				// translators: %2$s is the closing a tag
				esc_html__('Translate this field in %1$sStrings translations%2$s.', 'cookiefox'),
				'<a href="'.esc_url(admin_url("admin.php?page=mlang_strings&group=cookiefox&s=".$field)).'">',
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

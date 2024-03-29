<?php
/**
 * Frontend.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Settings {
	public function __construct() {
		add_action('cmb2_admin_init', array($this, 'metabox'));
	}

	public function metabox() {
		$main_options = new_cmb2_box(array(
			'id' => 'cookiefox_options_main',
			'title' => __('CookieFox Settings', 'cookiefox'),
			'object_types' => array( 'options-page' ),
			'option_key' => 'cookiefox',
			'icon_url' => 'dashicons-shield-alt',
			'menu_title' => __('Cookie Notice', 'cookiefox'),
			'capability' => 'manage_options',
			'parent_slug' => 'options-general.php',
		));
		
		$main_options->add_field(array(
			'name' => __('General Settings', 'cookiefox'),
			'type' => 'title',
			'id' => 'title_general'
		));

		$main_options->add_field(array(
			'name' => esc_html__('Enable Cookie Notice', 'cookiefox'),
			'id' => 'cookie_notice_enabled',
			'desc' => __('The privacy notice will be displayed when this option is enabled.', 'cookiefox'),
			'type' => 'toggle',
		));

		$main_options->add_field(array(
			'name' => esc_html__('Disable on Privacy Page', 'cookiefox'),
			'id' => 'cookie_notice_hide_on_privacy_page',
			'desc' => sprintf(
				// translators: %1$s is the opening a tag
				// translators: %2$s is the closing a tag
				esc_html__('Hides the privacy notice on the privacy page configured in Settings -> %1$sPrivacy%2$s.', 'cookiefox'),
				'<a href="'.esc_url(admin_url("options-privacy.php")).'">',
				'</a>'
			),
			'type' => 'toggle',
		));


		$main_options->add_field(array(
			'name' => __('Privacy Notice', 'cookiefox'),
			'desc' => apply_filters("cookiefox_settings_title_desc", __('The privacy notice can either be displayed as a modal or banner. Modals obscure the content while being displayed and require explicit user action while banners are fixed on the bottom of the viewport and users can browse the site without a prior interaction.', 'cookiefox'), 'title_notice'),
			'type' => 'title',
			'id' => 'title_notice'
		));

		$main_options->add_field(array(
			'name' => __('Display Privacy Notice as', 'cookiefox'),
			'id' => 'notice_display',
			'type' => 'select',
			'show_option_none' => false,
			'default' => 'banner',
			'options' => array(
				'banner' => __('Banner', 'cookiefox'),
				'modal' => __('Modal', 'cookiefox'),
			),
		));

		$main_options->add_field(array(
			'name' => __('Title', 'cookiefox'),
			'id' => 'notice_title',
			'type' => 'text',
			'desc' => apply_filters("cookiefox_settings_field_desc", false, 'notice_title')
		));
				
		$main_options->add_field(array(
			'name' => __('Text', 'cookiefox'),
			'id' => 'notice_text',
			'type' => 'textarea',
			'desc' => apply_filters("cookiefox_settings_field_desc", false, 'notice_text'),
		));

		$main_options->add_field(array(
			'name' => __('Accept Button Text', 'cookiefox'),
			'id' => 'notice_button_accept',
			'type' => 'text',
			'desc' => apply_filters("cookiefox_settings_field_desc", false, 'notice_button_accept'),
		));

		$main_options->add_field(array(
			'name' => esc_html__('Enable Decline Button', 'cookiefox'),
			'desc' => __('Disabling this option could violate the privacy laws in your country. Use with caution.', 'cookiefox'),
			'id' => 'notice_button_decline_enabled',
			'type' => 'toggle',
		));
		
		$main_options->add_field(array(
			'name' => __('Decline Button Text', 'cookiefox'),
			'id' => 'notice_button_decline',
			'type' => 'text',
			'desc' => apply_filters("cookiefox_settings_field_desc", false, 'notice_button_decline'),
			'attributes' => array(
				'data-conditional-id' => 'notice_button_decline_enabled',
				'data-conditional-value' => 'on',
			),
		));

		// $main_options->add_field(array(
		// 	'name' => esc_html__('Support this Plugin with a Link', 'cookiefox'),
		// 	'id' => 'cookie_notice_support',
		// 	'type' => 'toggle',
		// ));
		
		$main_options->add_field(array(
			'name' => __('Scripts & Cookies', 'cookiefox'),
			'desc' => __('You can set the scripts to be executed when the user accepts or declines the use of cookies. The scripts will be executed directly after consent is given and at each page view.', 'cookiefox'),
			'type' => 'title',
			'id' => 'title_scripts'
		));

		$main_options->add_field(array(
			'name' => __('Opt-In Scripts', 'cookiefox'),
			'desc' => __('These scripts will be executed when the user opts in to the use of cookies.', 'cookiefox'),
			'id' => 'scripts_consent',
			'type' => 'textarea_code'
		));

		$main_options->add_field(array(
			'name' => __('Opt-Out Scripts', 'cookiefox'),
			'desc' => __('These scripts will be executed when the user declines the use of cookies.', 'cookiefox'),
			'id' => 'scripts_no_consent',
			'type' => 'textarea_code'
		));

		$main_options->add_field(array(
			'name' => __('Always-On Scripts', 'cookiefox'),
			'desc' => __('These scripts will always be executed.', 'cookiefox'),
			'id' => 'scripts_always',
			'type' => 'textarea_code'
		));

		$main_options->add_field(array(
			'name' => __('Cookies', 'cookiefox'),
			'desc' => __('These cookies will be removed after opt-out. Seperate multiple cookies by a comma.', 'cookiefox'),
			'id' => 'cookies',
			'type' => 'text'
		));
		
		$main_options->add_field(array(
			'name' => __('Embedded Content', 'cookiefox'),
			'desc' => __('Embedded content from external sites such as Youtube-Videos and Tweets can violate your users\' privacy. CookieFox allows users to opt-in.', 'cookiefox'),
			'type' => 'title',
			'id' => 'title_embeds',
		));
		
		$main_options->add_field(array(
			'name' => esc_html__('Block Auto-Embeds', 'cookiefox'),
			'desc' => __('Enable this option to block external content from being automatically embedded. After accepting the privacy notice, all content will be embedded.', 'cookiefox'),
			'id' => 'block_embeds',
			'type' => 'toggle',
		));		

		$main_options->add_field(array(
			'name' => __('Design', 'cookiefox'),
			'desc' => __('Basic design customizations are available in this section. Advanced customization is available through CSS variables.', 'cookiefox'),
			'type' => 'title',
			'id' => 'title_design',
			'attributes' => array(
				'data-conditional-id' => 'stylesheet',
				'data-conditional-value' => wp_json_encode(array('external', 'inline')),
			),
		));

		$main_options->add_field(array(
			'name' => __('Font', 'cookiefox'),
			'desc' => __('Choose "Theme Font" to automatically use the font of your active theme.', 'cookiefox'),
			'id' => 'font',
			'type' => 'select',
			'show_option_none' => false,
			'default' => 'theme',
			'options' => array(
				'theme' => __('Theme Font', 'cookiefox'),
				'system' => __('Browser/System Font', 'cookiefox'),
				'arial' => 'Arial',
				'verdana' => 'Verdana',
				'georgia' => 'Georgia',
				'tahoma' => 'Tahoma'	
			),
			'attributes' => array(
				'data-conditional-id' => 'stylesheet',
				'data-conditional-value' => wp_json_encode(array('external', 'inline')),
			),
		));

		$main_options->add_field(array(
			'name' => __('Button Style', 'cookiefox'),
			'id' => 'button_style',
			'type' => 'select',
			'show_option_none' => false,
			'default' => 'rounded',
			'options' => array(
				'plain' => __('Plain', 'cookiefox'),
				'rounded' => __('Rounded Corners', 'cookiefox'),
				'round' => __('Round Corners', 'cookiefox'),
			),
		));
		
		$main_options->add_field(array(
			'name' => __('Background Color', 'cookiefox'),
			'id' => 'color_background',
			'type' => 'colorpicker',
			'default' => '#ffffff',
			'attributes' => array(
				'data-conditional-id' => 'stylesheet',
				'data-conditional-value' => wp_json_encode(array('external', 'inline')),
			),
		));

		$main_options->add_field(array(
			'name' => __('Primary Text Color', 'cookiefox'),
			'id' => 'color_text_primary',
			'type' => 'colorpicker',
			'default' => '#000000',
			'attributes' => array(
				'data-conditional-id' => 'stylesheet',
				'data-conditional-value' => wp_json_encode(array('external', 'inline')),
			),
		));

		$main_options->add_field(array(
			'name' => __('Secondary Text Color', 'cookiefox'),
			'id' => 'color_text_secondary',
			'type' => 'colorpicker',
			'default' => '#666',
			'attributes' => array(
				'data-conditional-id' => 'stylesheet',
				'data-conditional-value' => wp_json_encode(array('external', 'inline')),
			),
		));
		
		$main_options->add_field(array(
			'name' => __('Accent Color', 'cookiefox'),
			'id' => 'color_accent',
			'type' => 'colorpicker',
			'default' => '#60B665',
			'attributes' => array(
				'data-conditional-id' => 'stylesheet',
				'data-conditional-value' => wp_json_encode(array('external', 'inline')),
			),
		));
		
		$main_options->add_field(array(
			'name' => __('Consent Cookie Settings', 'cookiefox'),
			'desc' => __('CookieFox persists consent information in the browser using a Cookie.', 'cookiefox'),
			'type' => 'title',
			'id' => 'title_cookie'
		));

		$main_options->add_field(array(
			'name' => __('Cookie Name', 'cookiefox'),
			'desc' => __('The name of the consent cookie.', 'cookiefox'),
			'id' => 'cookie_name',
			'type' => 'text',
		));

		$main_options->add_field(array(
			'name' => __('Cookie Domain', 'cookiefox'),
			'desc' => __('The domain that the cookie is available to. Leave empty to use the default domain of your site.', 'cookiefox'),
			'id' => 'cookie_domain',
			'type' => 'text',
		));

		$main_options->add_field(array(
			'name' => __('Expiration Time in Days', 'cookiefox'),
			'desc' => __('The number of days the consent cookie is stored.', 'cookiefox'),
			'default' => '90',
			'id' => 'cookie_expiration',
			'type' => 'text_small'
		));
		
		$main_options->add_field(array(
			'name' => __('Advanced Settings & Performance', 'cookiefox'),
			'desc' => __('These settings can be used to customize some performance characteristics of CookieFox.', 'cookiefox'),
			'type' => 'title',
			'id' => 'title_performance'
		));

		$main_options->add_field(array(
			'name' => __('Stylesheet', 'cookiefox'),
			'desc' => __('CSS can be included via an external stylesheet, an inline style-tag or not be included at all.', 'cookiefox'),
			'id' => 'stylesheet',
			'type' => 'select',
			'show_option_none' => false,
			'default' => 'theme',
			'options' => array(
				'external' => __('External Stylesheet', 'cookiefox'),
				'inline' => __('Inline CSS', 'cookiefox'),
				'none' => __('Do not include styles', 'cookiefox'),
			),
		));
		
		$main_options->add_field(array(
			'name' => __('Javascript', 'cookiefox'),
			'desc' => __('The modern version has a smaller file size and is more performant than the legacy version. If you need to support legacy browsers such as Internet Explorer 11, please choose the legacy option.', 'cookiefox'),
			'id' => 'javascript',
			'type' => 'select',
			'show_option_none' => false,
			'default' => 'modern',
			'options' => array(
				'modern' => __('Modern Browsers', 'cookiefox'),
				'legacy' => __('Legacy (including IE11)', 'cookiefox'),
			),
		));

	}
	
	public static function register_defaults(){
		
		$settings = get_option("cookiefox", array());
		
		$settings = array_merge(array(
		  'cookie_notice_enabled' => 'on',
			'cookie_notice_hide_on_privacy_page' => 'on',
		  'notice_display' => 'banner',
		  'notice_title' => __('Privacy Settings', 'cookiefox'),
		  'notice_text' => __('We use cookies to improve your experience on our site. To learn more, view our privacy policy.', 'cookiefox'),
		  'notice_button_accept' => __('Accept', 'cookiefox'),
		  'notice_button_decline_enabled' => 'on',
		  'notice_button_decline' => __('Decline', 'cookiefox'),
		  'block_embeds' => 'off',
		  'font' => 'theme',
		  'button_style' => 'rounded',
		  'color_background' => '#ffffff',
		  'color_text_primary' => '#000000',
		  'color_text_secondary' => '#666666',
		  'color_text_tertiary' => '#d8d8d8',
		  'color_accent' => '#60B665',
		  'cookie_name' => 'cookiefox_consent',
		  'cookie_expiration' => '90',
		  'stylesheet' => 'external',
		  'javascript' => 'modern',
		), $settings);
		
		update_option("cookiefox", $settings, true);
	}
}
new Settings();

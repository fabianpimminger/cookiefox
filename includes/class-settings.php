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
		$consent_type = Helper::get_option("consent_type");		
		
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
			'name' => esc_html__('Consent Type', 'cookiefox'),
			'id' => 'consent_type',
			'desc' => __('Simple consent only provides a general accept/deny option. Category consent offers separate consent by cookie categories.', 'cookiefox'),
			'type' => 'select',
			'options' => array(
				'simple' => __('Simple', "cookiefox"),
				'category' => __('Category', "cookiefox")
			)
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
		
		if($consent_type == "category"){
			$sample_url = admin_url("options-general.php?page=cookiefox&action=cookiefox_sample_content");
			$sample_url = wp_nonce_url($sample_url, 'cookiefox-install-sample-content');
			
			$main_options->add_field(array(
				'name' => esc_html__('Sample Content', 'cookiefox'),
				'id' => 'sample_content',
				'desc' => __('Create sample content to get you started faster. Includes a sample cookie and four categories (Functional, Statistics, Marketing and External Media).', 'cookiefox'),
				'type' => 'button',
				'url' => $sample_url,
				'label' => __('Install Sample Content', 'cookiefox'),
			));
		}

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
			'name' => __('Save Button Text', 'cookiefox'),
			'id' => 'notice_button_save',
			'type' => 'text',
			'desc' => apply_filters("cookiefox_settings_field_desc", false, 'notice_button_save'),
			'attributes' => array(
				'data-conditional-id' => 'consent_type',
				'data-conditional-value' => wp_json_encode(array('category')),
			),
		));

		$main_options->add_field(array(
			'name' => __('Manage Cookies Button Text', 'cookiefox'),
			'id' => 'notice_button_manage',
			'type' => 'text',
			'desc' => apply_filters("cookiefox_settings_field_desc", false, 'notice_button_manage'),
			'attributes' => array(
				'data-conditional-id' => 'consent_type',
				'data-conditional-value' => wp_json_encode(array('category')),
			),
		));

		$main_options->add_field(array(
			'name' => esc_html__('Decline Button', 'cookiefox'),
			'desc' => __('Disabling this option could violate the privacy laws in your country. Use with caution.', 'cookiefox'),
			'id' => 'notice_button_decline_type',
			'type' => 'select',
			'default' => 'button',
			'options' => array(
				'none' => __('None', 'cookiefox'),
				'text' => __('Text', 'cookiefox'),
				'button' => __('Button', 'cookiefox'),
			),
		));
		
		$main_options->add_field(array(
			'name' => __('Decline Button Text', 'cookiefox'),
			'id' => 'notice_button_decline',
			'type' => 'text',
			'desc' => apply_filters("cookiefox_settings_field_desc", false, 'notice_button_decline'),
			'attributes' => array(
				'data-conditional-id' => 'notice_button_decline_type',
				'data-conditional-value' => wp_json_encode(array('text', 'button')),
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
			'id' => 'title_scripts',
		));

		$main_options->add_field(array(
			'name' => __('Opt-In Scripts', 'cookiefox'),
			'desc' => __('These scripts will be executed when the user opts in to the use of cookies.', 'cookiefox'),
			'id' => 'scripts_consent',
			'type' => 'textarea_code',
			'attributes' => array(
				'data-conditional-id' => 'consent_type',
				'data-conditional-value' => wp_json_encode(array('simple')),
			),
		));

		$main_options->add_field(array(
			'name' => __('Opt-Out Scripts', 'cookiefox'),
			'desc' => __('These scripts will be executed when the user declines the use of cookies.', 'cookiefox'),
			'id' => 'scripts_no_consent',
			'type' => 'textarea_code',
			'attributes' => array(
				'data-conditional-id' => 'consent_type',
				'data-conditional-value' => wp_json_encode(array('simple')),
			),
		));

		$main_options->add_field(array(
			'name' => __('Always-On Scripts', 'cookiefox'),
			'desc' => __('These scripts will always be executed.', 'cookiefox'),
			'id' => 'scripts_always',
			'type' => 'textarea_code',
		));

		$main_options->add_field(array(
			'name' => __('Cookies', 'cookiefox'),
			'desc' => __('These cookies will be removed after opt-out. Seperate multiple cookies by a comma.', 'cookiefox'),
			'id' => 'cookies',
			'type' => 'text',
			'attributes' => array(
				'data-conditional-id' => 'consent_type',
				'data-conditional-value' => wp_json_encode(array('simple')),
			),
		));
		
		$main_options->add_field(array(
			'name' => __('Embedded Content', 'cookiefox'),
			'desc' => __('Embedded content from external sites such as Youtube-Videos and Tweets can violate your users\' privacy. CookieFox allows users to opt-in.', 'cookiefox'),
			'type' => 'title',
			'id' => 'title_embeds',
		));
		
		$category_consent_string = "";

		if($consent_type == "category"){
			$category_consent_string = " <b>".__('When using category-based consent, you should activate "Unblock Embedded Content" at least once in the category settings.', 'cookiefox')."</b>";
		}
		
		$main_options->add_field(array(
			'name' => esc_html__('Block Auto-Embeds', 'cookiefox'),
			'desc' => __('Enable this option to block external content from being automatically embedded. After accepting the privacy notice, all content will be embedded.', 'cookiefox').$category_consent_string,
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
			'default' => '#767676',
			'attributes' => array(
				'data-conditional-id' => 'stylesheet',
				'data-conditional-value' => wp_json_encode(array('external', 'inline')),
			),
		));
		
		$main_options->add_field(array(
			'name' => __('Primary Button Color', 'cookiefox'),
			'id' => 'color_button_primary',
			'type' => 'colorpicker',
			'default' => '#3D854F',
			'attributes' => array(
				'data-conditional-id' => 'stylesheet',
				'data-conditional-value' => wp_json_encode(array('external', 'inline')),
			),
		));

		$main_options->add_field(array(
			'name' => __('Secondary Button Color', 'cookiefox'),
			'id' => 'color_button_secondary',
			'type' => 'colorpicker',
			'default' => '#767676',
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
			'type' => 'text_small',
			'attributes' => array(
				'type' => 'number',
				'pattern' => '\d*',
			),
			'sanitization_cb' => 'absint',
      'escape_cb'       => 'absint',
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

		$main_options->add_field(array(
			'name' => __('Cookie Notice Delay', 'cookiefox'),
			'desc' => __('Opening the cookie notice will be delayed by x seconds.', 'cookiefox'),
			'default' => '0',
			'id' => 'notice_delay',
			'type' => 'text_small',
			'attributes' => array(
				'type' => 'number',
				'pattern' => '\d*',
			),
			'sanitization_cb' => 'absint',
      'escape_cb'       => 'absint',
		));

	}
	
	public static function register_defaults(){
		
		$settings = get_option("cookiefox", array());
		
		$settings = Helper::merge_default_settings($settings);
					
		update_option("cookiefox", $settings, true);
	}
}
new Settings();

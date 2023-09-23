<?php
/**
 * Frontend.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Frontend {
	public function __construct() {
		add_action("wp", array($this, "init_frontend"));
		add_filter('cookiefox_prepare_scripts', array($this, "prepare_scripts"));
	}
	
	public function init_frontend() {
		
		if(!$this->privacy_notice_enabled()){
			return;
		}
				
		add_action('wp_enqueue_scripts', array($this, "enqueue_scripts"));
		add_action('wp_footer', array($this, "footer"));
		add_action('wp_head', array($this, "var_style"), 101);
		
	}

	public function enqueue_scripts() {
		$stylesheet = Helper::get_option("stylesheet", "external");
		
		if($stylesheet == "external"){
			wp_register_style("cookiefox", Main::plugin_url() . '/assets/frontend/css/main.css', array(), filemtime(dirname(COOKIEFOX_PLUGIN_FILE) . '/assets/frontend/css/main.css'));
			wp_enqueue_style("cookiefox");
		} elseif ($stylesheet == "inline"){
			add_action('wp_head', array($this, "inline_style"), 100);
		}

		$javascript = Helper::get_option("javascript", "modern");
		
		if($javascript == "modern"){
			wp_register_script("cookiefox", Main::plugin_url() . '/assets/frontend/js/main.js', array(), filemtime(dirname(COOKIEFOX_PLUGIN_FILE) . '/assets/frontend/js/main.js'), true);
		} elseif ($javascript == "legacy"){
			wp_register_script("cookiefox", Main::plugin_url() . '/assets/frontend/js/legacy.js', array(), filemtime(dirname(COOKIEFOX_PLUGIN_FILE) . '/assets/frontend/js/legacy.js'), true);
		}

		wp_enqueue_script("cookiefox");

	}
	
	public function inline_style() {
		if(file_exists(dirname(COOKIEFOX_PLUGIN_FILE) . '/assets/frontend/css/main.css')){
			echo "<style>";
			include dirname(COOKIEFOX_PLUGIN_FILE) . '/assets/frontend/css/main.css';
			echo "</style>";
		}
	}

	public function var_style() {
		$font = Helper::get_option("font", "inherit");

		if($font != "inherit"){
			switch ($font) {
			  case "system":
			    $font = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';
			    break;
			  case "arial":
			    $font = "Arial, sans-serif";
			    break;
				case "verdana":
					$font = "Verdana, sans-serif";
			    break;
				case "georgia":
					$font = "Georgia, serif";
			    break;
				case "tahoma":
					$font = "Tahoma, sans-serif";
			    break;
				default:
					$font = "inherit";
			    break;
			}			
		}		

		$button_style = Helper::get_option("button_style", "rounded");
		$border_radius = "5px";
		if($button_style != "rounded"){
			switch ($button_style) {
			  case "round":
			    $border_radius = '3em';
			    break;
			  case "plain":
			    $border_radius = "0px";
			    break;
				default:
					$border_radius = "5px";
			    break;
			}			
		}

		$color_background = Helper::get_option("color_background", "#ffffff");
		$color_text_primary = Helper::get_option("color_text_primary", "#000000");
		$color_text_secondary = Helper::get_option("color_text_secondary", "#767676");
		$color_button_primary = Helper::get_option("color_button_primary", "#3D854F");
		$color_button_secondary = Helper::get_option("color_button_secondary", "#767676");

		?>
		<style>
			.cookiefox{
				--cookiefox--font-family: <?php echo $font; ?>;
				--cookiefox--background: <?php echo esc_html($color_background); ?>;
				--cookiefox--color-text-primary: <?php echo esc_html($color_text_primary); ?>;
				--cookiefox--color-text-secondary: <?php echo esc_html($color_text_secondary); ?>;
				--cookiefox--color-button-primary: <?php echo esc_html($color_button_primary); ?>;
				--cookiefox--color-button-secondary: <?php echo esc_html($color_button_secondary); ?>;
				--cookiefox__button--border-radius: <?php echo esc_html($border_radius); ?>;
			}
		</style>
		<?php

	}
		
	public function footer() {
		
		$data = get_option("cookiefox", array());
		$data["privacy_type"] = "basic";
		$data["disabled_on_privacy_page"] = $this->is_disabled_on_privacy_page();
		$data = $this->prepare_data($data);
		?>
		<script>
			var cookiefox = {data: <?php echo wp_json_encode($data); ?>};
		</script>
		<div id="cookiefox" data-nosnippet></div>
		
		<?php
	}
	
	private function prepare_data($data) {
		
		if(defined('COOKIEFOX_UNMASK_SCRIPTS') && COOKIEFOX_UNMASK_SCRIPTS == true) {
			$keys = array("scripts_consent", "scripts_no_consent", "scripts_always");
			
			foreach($keys as $key){
				if(!empty($data[$key])){
					$data[$key] = $this->unmask_scripts($data[$key]);
				}
			}
		}
		
		$data = Helper::merge_default_settings($data);
		
		$data["api_base"] = get_rest_url();
		$data["lang"] = Helper::get_language();
		$data["notice_button_back"] = __("Back", "cookiefox");
		$data["notice_button_cookie_information"] = __("Cookie information", "cookiefox");
		$data["notice_text_name"] = __("Name", "cookiefox");
		$data["notice_text_vendor"] = __("Vendor", "cookiefox");
		$data["notice_text_purpose"] = __("Purpose", "cookiefox");
		$data["notice_text_privacy_policy"] = __("Privacy Policy", "cookiefox");
		$data["notice_text_cookies"] = __("Cookies", "cookiefox");
		$data["notice_text_duration"] = __("Duration", "cookiefox");
		$data = apply_filters( 'cookiefox_frontend_prepare_data', $data );
		
		return $data;
	}
	
	private function unmask_scripts($html) {
		return str_replace("%script%", "script", $html);
	}
	
	public function prepare_scripts($html) {
		if(defined('COOKIEFOX_UNMASK_SCRIPTS') && COOKIEFOX_UNMASK_SCRIPTS == true) {
			return $this->unmask_scripts($html);
		}
		
		return $html;
	}
	
	private function privacy_notice_enabled() {
		$enabled = true;
		
		$option_enabled = Helper::get_option("cookie_notice_enabled", false);
		
		if($option_enabled !== "on"){
			$enabled = false;
		}
				
		$enabled = apply_filters( 'cookiefox_privacy_notice_enabled', $enabled);
		
		return $enabled;

	}
	
	private function is_disabled_on_privacy_page(){
		
		if(!is_page()){
			return false;
		}
		
		$page_id = get_option( 'wp_page_for_privacy_policy', false);
		
		if(empty($page_id)){
			return false;
		}
		
		if($page_id == get_the_ID()){
			$option_enabled = Helper::get_option("cookie_notice_hide_on_privacy_page", false);
			
			if($option_enabled === "on"){
				return true;
			}
		}
		
		return false;
	}
}
new Frontend();

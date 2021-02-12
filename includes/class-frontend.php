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

		$color_background = Helper::get_option("color_background", "white");
		$color_text_primary = Helper::get_option("color_text_primary", "#000000");
		$color_text_secondary = Helper::get_option("color_text_secondary", "#666666");
		$color_accent = Helper::get_option("color_accent", "#60B665");
		

		?>
		<style>
			.cookiefox{
				--cookiefox-font-family: <?php echo $font; ?>;
				--cookiefox-color-background: <?php echo esc_html($color_background); ?>;
				--cookiefox-color-text-primary: <?php echo esc_html($color_text_primary); ?>;
				--cookiefox-color-text-secondary: <?php echo esc_html($color_text_secondary); ?>;
				--cookiefox-color-accent: <?php echo esc_html($color_accent); ?>;
			}
		</style>
		<?php

	}
		
	public function footer() {
		
		$data = get_option("cookiefox", array());
		$data["privay_type"] = "basic";
		
		?>
		<script>
			var cookiefox = <?php echo wp_json_encode($data); ?>;
		</script>
		<div id="cookiefox"></div>
		
		<?php
	}
	
	private function privacy_notice_enabled() {
		$enabled = true;
		
		$option_enabled = Helper::get_option("cookie_notice_enabled", false);
		
		if($option_enabled !== "on"){
			$enabled = false;
		}
		
		if($enabled == true && $this->is_disabled_on_privacy_page()){
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
			return true;
		}
		
		return false;
	}
}
new Frontend();

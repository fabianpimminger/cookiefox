<?php
/**
 * Integration: GA Google Analytics.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

use CookieFox\Integrations\Integration;

defined('ABSPATH') || exit;

class GA_Google_Analytics extends Integration {
	private $slug = "ga-google-analytics";
	private $done = false;
	
	public function __construct() {
		add_filter("cookiefox_integrations", array($this, "add_integration"));
		add_filter("cookiefox_consent_{$this->slug}", array($this, "consent"));
		add_action("cookiefox_integration_{$this->slug}", array($this, "do_integration"));
	}
	
	public function add_integration($integrations) {
		$integrations[$this->slug] = "GA Google Analytics";
		return $integrations;
	}
	
	public function consent() {
		ob_start();
		?>
		<script>
			var script = document.querySelector("script[data-cookiefox-integration='<?php echo esc_attr($this->slug); ?>']");
			if(script !== null){
				var el = window.cookiefox.api.cloneElement(script);
				el.removeAttribute("data-cookiefox-integration");
				el.removeAttribute("type");
				script.parentNode.insertBefore(el, script);
				script.remove();
			}
		</script>
		<?php
		return ob_get_clean();
	}
	
	public function do_integration() {
		if($this->done){
			return;
		}
		
		add_filter('ga_google_analytics_script_atts', array($this, 'script_atts'));
		add_filter('ga_google_analytics_script_atts_ext', array($this, 'script_atts'));
		
		$this->done = true;
	}
	
	public function script_atts($atts) {
		$atts = $atts." type=text/plain data-cookiefox-integration=".esc_attr($this->slug);
		return $atts;
	}

}
new GA_Google_Analytics();

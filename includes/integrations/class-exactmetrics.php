<?php
/**
 * Integration: Exactmetrics.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

use CookieFox\Integrations\Integration;

defined('ABSPATH') || exit;

class Exactmetrics extends Integration{
	private $slug = "exactmetrics";
	private $done_stats = false;

	public function __construct() {
		add_filter("cookiefox_integrations", array($this, "add_integration"));
		
		add_filter("cookiefox_consent_{$this->slug}", array($this, "consent"));
		add_action("cookiefox_integration_{$this->slug}", array($this, "do_integration"));
	}
	
	public function add_integration($integrations) {		
		$integrations["exactmetrics"] = "Exactmetrics - Google Analytics"; 
		return $integrations;
	}

	public function consent() {
		ob_start();
		?>
		<script>
			var scripts = document.querySelectorAll("script[data-cookiefox-integration='<?php echo esc_attr($this->slug); ?>']");
			scripts.forEach(function(script){
				var el = window.cookiefox.api.cloneElement(script);
				el.removeAttribute("data-cookiefox-integration");
				el.removeAttribute("type");
				script.parentNode.insertBefore(el, script);
				script.remove();
			});
			
		</script>
		<?php
		return ob_get_clean();
	}

	public function do_integration() {
		if($this->done_stats){
			return;
		}
		
		add_filter( 'exactmetrics_tracking_analytics_script_attributes', array($this, 'script_atts'));
					
		$this->done_stats = true;
	}
	
	public function script_atts($atts) {
		return array_merge(array(
			'type' => 'text/plain', 
			'data-cookiefox-integration' => $this->slug,
		), $atts);
	}

}
new Exactmetrics();

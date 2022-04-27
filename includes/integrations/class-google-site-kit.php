<?php
/**
 * Integration: Site Kit by Google.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

use CookieFox\Integrations\Integration;

defined('ABSPATH') || exit;

class Google_Site_Kit extends Integration {
	private $slug = "google-site-kit";
	private $done_analytics = false;
	private $done_adsense = false;
	private $done_tagmanager = false;

	public function __construct() {
		add_filter("cookiefox_integrations", array($this, "add_integration"));
		
		add_filter("cookiefox_consent_{$this->slug}--adsense", array($this, "consent_adsense"));
		add_action("cookiefox_integration_{$this->slug}--adsense", array($this, "do_integration_adsense"));
		
		add_filter("cookiefox_consent_{$this->slug}--analytics", array($this, "consent_analytics"));
		add_action("cookiefox_integration_{$this->slug}--analytics", array($this, "do_integration_analytics"));

		add_filter("cookiefox_consent_{$this->slug}--tagmanager", array($this, "consent_tagmanager"));
		add_action("cookiefox_integration_{$this->slug}--tagmanager", array($this, "do_integration_tagmanager"));

	}
	
	public function add_integration($integrations) {
		$integrations["google-site-kit--adsense"] = "Google Site Kit - Adsense"; 
		$integrations["google-site-kit--analytics"] = "Google Site Kit - Analytics"; 
		$integrations["google-site-kit--tagmanager"] = "Google Site Kit - Tagmanager"; 
		return $integrations;
	}

	public function consent_adsense() {
		ob_start();
		?>
		<script>
			// TODO
		</script>
		<?php
		return ob_get_clean();
	}
	
	public function do_integration_adsense() {
		if($this->done_adsense){
			return;
		}
		
		add_filter('googlesitekit_adsense_tag_block_on_consent', "__return_true");		
			
		$this->done_adsense = true;
	}

	public function consent_analytics() {
		ob_start();
		?>
		<script>
			var script = document.querySelector("script[data-cookiefox-integration='<?php echo esc_attr($this->slug); ?>--analytics']");
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

	public function do_integration_analytics() {
		if($this->done_analytics){
			return;
		}
		
		add_filter('script_loader_tag', array($this, 'analytics_script_loader_tag'), 10, 2);
			
		$this->done_analytics = true;
	}
	
	public function analytics_script_loader_tag($tag, $handle) {
		if($handle == "google_gtagjs"){
      return str_replace(' src', ' type="text/plain" data-cookiefox-integration="'.$this->slug.'--analytics" src', $tag);
		}
		
		return $tag;
	}
	
	public function consent_tagmanager() {
		ob_start();
		?>
		<script>
			// TODO
		</script>
		<?php
		return ob_get_clean();
	}
	
	public function do_integration_tagmanager() {
		if($this->done_tagmanager){
			return;
		}
		
		add_filter('googlesitekit_tagmanager_tag_block_on_consent', "__return_true");		
			
		$this->done_tagmanager = true;
	}

}
new Google_Site_Kit();

<?php
/**
 * Integration: Jetpack.
 *
 * @package CookieFox
 */

namespace CookieFox\Integrations;

use CookieFox\Integrations\Integration;

defined('ABSPATH') || exit;

class Jetpack extends Integration{
	private $slug = "jetpack";
	private $done_stats = false;

	public function __construct() {
		add_filter("cookiefox_integrations", array($this, "add_integration"));
		
		add_filter("cookiefox_consent_{$this->slug}--stats", array($this, "consent_stats"));
		add_action("cookiefox_integration_{$this->slug}--stats", array($this, "do_integration_stats"));
	}
	
	public function add_integration($integrations) {
		if(!class_exists("Jetpack") || !\Jetpack::is_module_active("stats")){
			return $integrations;
		}
		
		$integrations["jetpack--stats"] = "Jetpack - Stats"; 
		return $integrations;
	}

	public function consent_stats() {
		ob_start();
		?>
		<script>
			// TODO
		</script>
		<?php
		return ob_get_clean();
	}

	public function do_integration_stats() {
		if($this->done_stats){
			return;
		}
		
		add_filter('stats_array', array($this, 'stats_array'));
					
		$this->done_stats = true;
	}

	public function stats_array($kvs) {
		var_dump($kvs);
		die();
	}
}
new Jetpack();

<?php
/**
 * Frontend.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Admin {
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, "enqueue_scripts"));
		add_action('admin_head', array( $this, 'admin_head' ));
	}

	public function enqueue_scripts() {
		wp_enqueue_script('cmb2_conditional_logic', Main::plugin_url() . '/assets/admin/js/cmb2-conditional-logic.min.js', array('jquery'), "1.0.0", true);
	}
	
	public function admin_head() {
		?>
		<style>
		#cmb2-metabox-cookiefox_options_main .cmb2-metabox-description{
			display: block;
			padding-top: .5em;
		}
		</style>
		<?php
	}
	
	
	
}
new Admin();

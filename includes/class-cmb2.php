<?php
/**
 * CMB2.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class CMB2 {
	public function __construct() {
		add_action('admin_enqueue_scripts', array($this, "enqueue_scripts"));
		add_action('admin_head', array( $this, 'admin_head' ));
		add_filter('cmb2_wrap_classes', array($this, 'filter_class'), 10, 2 );
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
		
		#poststuff #slugdiv .inside {
		  margin: 11px 0 0;
		}
		
		.post-type-cookiefox_cookie #post_name,
		.cookiefox-mb .cmb-type-text input,
		.cookiefox-mb .cmb-type-text-url input,
		.cookiefox-mb .cmb-type-textarea-small textarea{
			width: 100%;
		}
		
		.cookiefox-mb.cmb2-wrap > .cmb-field-list > .cmb-row {
	    padding: 1em 0;
		}
		</style>
		<?php
	}
	
	public function filter_class( $classes, $box ) {

		foreach ( $box as $key => $value ) {
			if ( isset( $box->meta_box['attributes'] ) && isset( $box->meta_box['attributes']['classes'] ) ) {
				if ( ! empty( $box->meta_box['attributes']['classes'] ) ) {
					$classes[] = $box->meta_box['attributes']['classes'];
				}
			}
		}
	
		return array_unique( $classes );
	}

}
new CMB2();





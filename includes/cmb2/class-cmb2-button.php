<?php
/**
 * CMB2 Button.
 *
 * @package CookieFox
 */

namespace CookieFox\CMB2;

defined('ABSPATH') || exit;

class Button {
	public function __construct() {
		add_action('cmb2_render_button', array($this, 'render'), 10, 5);
		add_action('cmb2_sanitize_button', array($this, 'sanitize'), 10, 2);
	}
	
	public function sanitize($override_value, $value) {
		return null;
	}
	
	public function render($field, $escaped_value, $object_id, $object_type, $field_type_object) {
		
		echo '<a href="'.$field->args('url').'" class="button">'.$field->args('label').'</a>';

		$field_type_object->_desc(true, true);
	}
}
new Button();

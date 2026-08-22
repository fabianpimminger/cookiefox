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
		$confirm = $field->args('confirm');
		$onclick = '';
		if (is_string($confirm) && $confirm !== '') {
			$onclick = ' onclick="' . esc_attr('return confirm(' . wp_json_encode($confirm) . ');') . '"';
		}

		echo '<a href="'.esc_url($field->args('url')).'" class="button"'.$onclick.'>'.esc_html($field->args('label')).'</a>';

		$field_type_object->_desc(true, true);
	}
}
new Button();

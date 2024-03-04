<?php
/**
 * Frontend.
 *
 * @package CookieFox
 */

namespace CookieFox\CMB2;

defined('ABSPATH') || exit;

class Toggle {
	public function __construct() {
		add_action('cmb2_render_toggle', array($this, 'render'), 10, 5);
		add_action('cmb2_sanitize_toggle', array($this, 'sanitize'), 10, 2);
		add_action('admin_head', array( $this, 'admin_head' ));
	}
	
	public function sanitize($override_value, $value) {
		return is_null($value) ? 'off' : $value;
	}
	
	public function render($field, $escaped_value, $object_id, $object_type, $field_type_object) {
		$field_name = $field->_name();
		$active_value = null !== $field->args('active_value') ? ! empty($field->args('active_value')) ? $field->args('active_value') : 'on' : 'on';
		$inactive_value = null !== $field->args('inactive_value') ? ! empty($field->args('inactive_value')) ? $field->args('inactive_value') : 'off' : 'off';
			
		$args = array(
			'type' => 'checkbox',
			'id' => $field_name,
			'name' => $field_name,
			'desc' => '',
			'value' => $active_value,
		);

		if ($escaped_value == $active_value) {
			$args['checked'] = 'checked="checked"';
		} else {
			$args['checked'] = '';
		}

		echo '<label class="cmb2-switch">
				    <input type="checkbox" name="' . esc_attr($args['name']) . '" id="' . esc_attr($args['id']) . '" value="' . esc_attr($active_value) . '" data-inactive-value="' . esc_attr($inactive_value) . '" ' . $args['checked'] . ' />
				    <span class="knob"></span>
			      </label>';

		$field_type_object->_desc(true, true);
	}
	
	public function admin_head() {
		?>
<script>
jQuery(document).ready(function($) {
	$('.cmb2-switch').each(function() {
		var checkbox = $(this).find('input[type="checkbox"]');
		var inactiveValue = checkbox.data('inactive-value');
		var activeValue = checkbox.val();
		$(this).on('click', function() {
			if (checkbox.prop('checked')) {
				checkbox.val(activeValue);
			} else {
				checkbox.val(inactiveValue);
			}
		});
	});
});
</script>
<style>
.cmb2-switch {
	position: relative;
	display: inline-block;
	width: 34px;
	height: 20px;
	top: 3px;
	position: relative;
}

.cmb2-switch input {
	display: none;
}

.cmb2-switch .knob {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	-webkit-transition: .4s;
	transition: .4s;
	border-radius: 34px;
}

.cmb2-switch .knob:before {
	position: absolute;
	content: "";
	height: 16px;
	width: 16px;
	left: 2px;
	bottom: 2px;
	background-color: white;
	border-radius: 50%;
	-webkit-transition: .4s;
	transition: .4s;
}


#side-sortables .cmb-row .cmb2-switch+.cmb2-metabox-description {
	padding-bottom: 0;
}


#side-sortables .cmb2-wrap>.cmb-field-list>.cmb-row{
	padding: 8px 0 0px;
}

#side-sortables .cmb2-postbox .inside{
	padding-bottom: 4px;
}

input:checked+.knob {
	background-color: #0073aa;
}

input:focus+.knob {
	box-shadow: 0 0 1px #0073aa;
}

input:checked+.knob:before {
	-webkit-transform: translateX(14px);
	-ms-transform: translateX(14px);
	transform: translateX(14px);
}
</style>
<?php
	}
}
new Toggle();

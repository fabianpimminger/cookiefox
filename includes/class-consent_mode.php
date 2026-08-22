<?php
/**
 * Google Consent Mode v2.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Consent_Mode {
	const TYPES = array(
		'analytics_storage',
		'ad_storage',
		'ad_user_data',
		'ad_personalization',
	);

	public function __construct() {
		add_action('admin_notices', array($this, 'unmapped_notice'));
	}

	public static function is_enabled() {
		return Helper::get_option('consent_mode', 'off') === 'on';
	}

	public static function denied_state() {
		$state = array();
		foreach (self::TYPES as $type) {
			$state[$type] = 'denied';
		}

		return $state;
	}

	public static function granted_state() {
		$state = array();
		foreach (self::TYPES as $type) {
			$state[$type] = 'granted';
		}

		return $state;
	}

	public static function types_for_term($term_id) {
		$term_id = (int) $term_id;
		if ($term_id <= 0) {
			return array();
		}

		return self::sanitize_types(get_term_meta($term_id, 'consent_mode', true));
	}

	public static function sanitize_types($value) {
		if (!is_array($value)) {
			if (is_string($value) && $value !== '') {
				$value = array($value);
			} else {
				return array();
			}
		}

		$clean = array();
		foreach ($value as $item) {
			if (!is_string($item)) {
				continue;
			}
			if (in_array($item, self::TYPES, true)) {
				$clean[] = $item;
			}
		}

		return array_values(array_unique($clean));
	}

	public static function type_labels() {
		return array(
			'analytics_storage' => __('Analytics (analytics_storage)', 'cookiefox'),
			'ad_storage' => __('Advertising cookies (ad_storage)', 'cookiefox'),
			'ad_user_data' => __('Advertising user data (ad_user_data)', 'cookiefox'),
			'ad_personalization' => __('Personalized advertising (ad_personalization)', 'cookiefox'),
		);
	}

	public static function any_category_mapped() {
		if (!taxonomy_exists('cookiefox_category')) {
			return false;
		}

		$terms = get_terms(array(
			'taxonomy' => 'cookiefox_category',
			'hide_empty' => false,
			'fields' => 'ids',
		));

		if (empty($terms) || is_wp_error($terms)) {
			return false;
		}

		foreach ($terms as $term_id) {
			if (!empty(self::types_for_term($term_id))) {
				return true;
			}
		}

		return false;
	}

	public static function state_from_consent($consent) {
		if (Helper::get_option('consent_type') === 'category') {
			$state = self::denied_state();
			$categories = is_array($consent) ? $consent : array();

			if (!taxonomy_exists('cookiefox_category')) {
				return apply_filters('cookiefox_consent_mode_state', $state, $consent);
			}

			$terms = get_terms(array(
				'taxonomy' => 'cookiefox_category',
				'hide_empty' => false,
			));

			if (empty($terms) || is_wp_error($terms)) {
				return apply_filters('cookiefox_consent_mode_state', $state, $consent);
			}

			foreach ($terms as $term) {
				$accepted = !empty($categories[$term->slug]);
				if (!$accepted) {
					$always_on = get_term_meta($term->term_id, 'always_on', true);
					$accepted = ($always_on === 'on');
				}
				if (!$accepted) {
					continue;
				}

				foreach (self::types_for_term($term->term_id) as $type) {
					$state[$type] = 'granted';
				}
			}

			return apply_filters('cookiefox_consent_mode_state', $state, $consent);
		}

		$state = ($consent === true) ? self::granted_state() : self::denied_state();
		return apply_filters('cookiefox_consent_mode_state', $state, $consent);
	}

	public static function print_head() {
		if (!self::is_enabled()) {
			return;
		}

		$default = self::denied_state();
		$stored = self::state_from_cookie();

		if ($stored === null) {
			$default['wait_for_update'] = 500;
		}

		$json_flags = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP;
		?>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){window.dataLayer.push(arguments);}
window.gtag = window.gtag || gtag;
gtag('consent', 'default', <?php echo wp_json_encode($default, $json_flags); ?>);
<?php if (is_array($stored)) : ?>
gtag('consent', 'update', <?php echo wp_json_encode($stored, $json_flags); ?>);
<?php endif; ?>
</script>
		<?php
	}

	public function unmapped_notice() {
		if (!current_user_can('manage_options') || !self::is_enabled()) {
			return;
		}

		if (Helper::get_option('consent_type') !== 'category') {
			return;
		}

		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (!$screen || !is_object($screen)) {
			return;
		}

		$relevant = ($screen->id === 'settings_page_cookiefox' || $screen->id === 'edit-cookiefox_category');
		if (!$relevant || self::any_category_mapped()) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		esc_html_e('Google Consent Mode is enabled, but no category has any signals assigned. Accepting cookies will still send denied to Google until you assign signals on the categories.', 'cookiefox');
		echo '</p></div>';
	}

	private static function state_from_cookie() {
		$name = Helper::get_option('cookie_name', 'cookiefox_consent');
		if (!is_string($name) || $name === '' || !isset($_COOKIE[$name])) {
			return null;
		}

		$raw = wp_unslash($_COOKIE[$name]);
		if (!is_string($raw) || $raw === '') {
			return null;
		}

		$parsed = json_decode($raw, true);
		if (!is_array($parsed)) {
			return null;
		}

		$stored = isset($parsed['v']) ? (int) $parsed['v'] : 0;
		$current = (int) get_option('cookiefox_consent_version', 0);
		if ($stored !== $current) {
			return null;
		}

		if (Helper::get_option('consent_type') === 'category') {
			$categories = array();
			if (isset($parsed['consent']) && is_array($parsed['consent']) && isset($parsed['consent']['categories']) && is_array($parsed['consent']['categories'])) {
				$categories = $parsed['consent']['categories'];
			}
			return self::state_from_consent($categories);
		}

		return self::state_from_consent(!empty($parsed['consent']));
	}
}
new Consent_Mode();

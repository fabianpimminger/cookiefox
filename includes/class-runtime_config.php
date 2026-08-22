<?php
/**
 * Runtime cookie config (scripts and categories).
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Runtime_Config {
	const TRANSIENT_PREFIX = 'cookiefox_config_';
	const TRANSIENT_TTL = 43200;
	const LANGS_OPTION = 'cookiefox_config_langs';

	public function __construct() {
		add_action('updated_option', array($this, 'on_updated_option'), 10, 1);
		add_action('added_option', array($this, 'on_updated_option'), 10, 1);
		add_action('save_post_cookiefox_cookie', array(__CLASS__, 'invalidate'));
		add_action('trashed_post', array($this, 'on_cookie_post_change'));
		add_action('untrashed_post', array($this, 'on_cookie_post_change'));
		add_action('before_delete_post', array($this, 'on_cookie_post_change'));
		add_action('created_cookiefox_category', array(__CLASS__, 'invalidate'));
		add_action('edited_cookiefox_category', array(__CLASS__, 'invalidate'));
		add_action('delete_cookiefox_category', array(__CLASS__, 'invalidate'));
	}

	public function on_updated_option($option) {
		if ($option === 'cookiefox') {
			self::invalidate();
		}
	}

	public function on_cookie_post_change($post_id) {
		if (get_post_type($post_id) === 'cookiefox_cookie') {
			self::invalidate();
		}
	}

	public static function uses_rest() {
		return Helper::get_option('config_via_rest', 'off') === 'on';
	}

	public static function get($lang = null) {
		if ($lang === null || $lang === '') {
			$lang = Helper::get_language();
		}
		if ($lang === null || $lang === '') {
			$lang = 'default';
		}

		$key = self::transient_key($lang);
		$config = get_transient($key);

		if ($config === false) {
			$config = self::build();
			set_transient($key, $config, self::TRANSIENT_TTL);
			self::remember_lang($lang);
		}

		return $config;
	}

	public static function build() {
		$settings = get_option('cookiefox', array());
		if (!is_array($settings)) {
			$settings = array();
		}

		$config = array(
			'scripts_always' => self::prepare_script(isset($settings['scripts_always']) ? $settings['scripts_always'] : ''),
			'scripts_consent' => self::prepare_script(isset($settings['scripts_consent']) ? $settings['scripts_consent'] : ''),
			'scripts_no_consent' => self::prepare_script(isset($settings['scripts_no_consent']) ? $settings['scripts_no_consent'] : ''),
			'cookies' => isset($settings['cookies']) ? $settings['cookies'] : '',
			'categories' => self::build_categories(),
		);

		return apply_filters('cookiefox_runtime_config', $config);
	}

	public static function strip_scripts_from_data($data) {
		unset($data['scripts_always'], $data['scripts_consent'], $data['scripts_no_consent']);
		return $data;
	}

	public static function invalidate() {
		try {
			$langs = get_option(self::LANGS_OPTION, array());
			if (!is_array($langs)) {
				$langs = array();
			}

			foreach ($langs as $lang) {
				delete_transient(self::transient_key($lang));
			}

			delete_transient(self::transient_key('default'));
			update_option(self::LANGS_OPTION, array(), false);

			self::flush_page_caches();
		} catch (\Throwable $e) {
			return;
		}
	}

	public static function flush_page_caches() {
		try {
			self::call_if_exists('rocket_clean_domain');
			self::call_if_exists('w3tc_flush_all');
			self::call_if_exists('wp_cache_clear_cache');
			self::do_action_safe('litespeed_purge_all');
			self::call_if_exists('wpfc_clear_all_cache', array(true));
			self::do_action_safe('cache_enabler_clear_complete_cache');
			self::call_static_if_exists('Cache_Enabler', 'clear_complete_cache');
			self::call_if_exists('wpo_cache_flush');
			self::call_if_exists('sg_cachepress_purge_cache');
			self::do_action_safe('wphb_clear_page_cache');
			self::do_action_safe('breeze_clear_all_cache');
			self::flush_wpengine_caches();
			self::flush_kinsta_caches();
			self::call_if_exists('pantheon_wp_clear_edge_all');
			self::do_action_safe('rt_nginx_helper_purge_all');
			self::flush_cloudflare_caches();
			self::call_static_if_exists('FlyingPress\\Purge', 'purge_everything');
			self::call_static_if_exists('comet_cache', 'clear');
			self::do_action_safe('cookiefox_flush_caches');
		} catch (\Throwable $e) {
			return;
		}
	}

	private static function call_if_exists($function, $args = array()) {
		try {
			if (!is_string($function) || !function_exists($function) || !is_callable($function)) {
				return;
			}
			call_user_func_array($function, $args);
		} catch (\Throwable $e) {
			return;
		}
	}

	private static function call_static_if_exists($class, $method, $args = array()) {
		try {
			if (!is_string($class) || !is_string($method) || !class_exists($class) || !method_exists($class, $method) || !is_callable(array($class, $method))) {
				return;
			}
			call_user_func_array(array($class, $method), $args);
		} catch (\Throwable $e) {
			return;
		}
	}

	private static function do_action_safe($hook) {
		try {
			if (!is_string($hook) || $hook === '') {
				return;
			}
			do_action($hook);
		} catch (\Throwable $e) {
			return;
		}
	}

	private static function flush_wpengine_caches() {
		try {
			if (!class_exists('WpeCommon')) {
				return;
			}
			if (method_exists('WpeCommon', 'clear_cdn_cache') && is_callable(array('WpeCommon', 'clear_cdn_cache'))) {
				\WpeCommon::clear_cdn_cache();
			} elseif (method_exists('WpeCommon', 'clear_maxcdn_cache') && is_callable(array('WpeCommon', 'clear_maxcdn_cache'))) {
				\WpeCommon::clear_maxcdn_cache();
			}
			if (method_exists('WpeCommon', 'purge_varnish_cache') && is_callable(array('WpeCommon', 'purge_varnish_cache'))) {
				\WpeCommon::purge_varnish_cache();
			}
		} catch (\Throwable $e) {
			return;
		}
	}

	private static function flush_kinsta_caches() {
		try {
			if (!defined('KINSTAMU_VERSION') && !class_exists('Kinsta\\Cache_Purge') && !class_exists('Kinsta\\KMP') && empty($GLOBALS['kinsta_cache'])) {
				return;
			}

			wp_remote_get('https://localhost/kinsta-clear-cache-all', array(
				'sslverify' => false,
				'timeout' => 5,
				'blocking' => false,
			));
		} catch (\Throwable $e) {
			return;
		}
	}

	private static function flush_cloudflare_caches() {
		try {
			if (!class_exists('CF\\WordPress\\Hooks')) {
				return;
			}
			$hooks = new \CF\WordPress\Hooks();
			if (method_exists($hooks, 'purgeCacheEverything') && is_callable(array($hooks, 'purgeCacheEverything'))) {
				$hooks->purgeCacheEverything();
			}
		} catch (\Throwable $e) {
			return;
		}
	}

	private static function build_categories() {
		$categories = array();

		if (!taxonomy_exists('cookiefox_category')) {
			return $categories;
		}

		$terms = get_terms(array(
			'taxonomy' => 'cookiefox_category',
			'hide_empty' => false,
			'orderby' => 'meta_value_num',
			'meta_key' => 'order',
		));

		if (is_wp_error($terms) || empty($terms)) {
			return $categories;
		}

		foreach ($terms as $i => $cat) {
			$category = array(
				'name' => $cat->name,
				'slug' => $cat->slug,
				'description' => wp_kses_post(wpautop($cat->description)),
				'order' => $i,
				'cookies' => array(),
				'always_on' => false,
				'integrations' => array(),
			);

			$query_vars = array(
				'post_type' => 'cookiefox_cookie',
				'posts_per_page' => -1,
				'tax_query' => array(
					array(
						'taxonomy' => 'cookiefox_category',
						'field' => 'term_id',
						'terms' => $cat->term_id,
					)
				),
				'orderby' => 'menu_order',
				'order' => 'asc',
			);

			$cookies = new \WP_Query($query_vars);

			while ($cookies->have_posts()) {
				$cookies->the_post();

				$cookie = array(
					'name' => get_the_title(),
					'slug' => get_post_field('post_name', get_post()),
				);

				$meta_data = get_post_meta(get_the_ID());
				$include_meta_keys = array('vendor', 'description', 'privacy_policy', 'scripts_consent', 'scripts_no_consent', 'scripts_always', 'cookies', 'duration', 'hosts');

				foreach ($include_meta_keys as $meta_key) {
					if (!empty($meta_data[$meta_key])) {
						$meta_value = $meta_data[$meta_key][0];

						if ($meta_value == 'off') {
							$meta_value = false;
						} else if ($meta_value == 'on') {
							$meta_value = true;
						}

						if (in_array($meta_key, array('scripts_consent', 'scripts_no_consent', 'scripts_always'), true)) {
							$meta_value = self::prepare_script($meta_value);
						}

						if ($meta_key == 'description') {
							$meta_value = wp_kses_post(wpautop($meta_value));
						}

						$cookie[$meta_key] = $meta_value;
					} else {
						$cookie[$meta_key] = null;
					}
				}

				$category['cookies'][] = $cookie;
			}
			wp_reset_postdata();

			$always_on = get_term_meta($cat->term_id, 'always_on', true);
			if (!empty($always_on) && $always_on == 'on') {
				$category['always_on'] = true;
			}

			$unblock_embeds = get_term_meta($cat->term_id, 'unblock_embeds', true);
			if (!empty($unblock_embeds) && $unblock_embeds == 'on') {
				$category['integrations']['unblock_embeds'] = true;
			}

			$categories[] = $category;
		}

		return $categories;
	}

	private static function prepare_script($html) {
		if (!is_string($html) || $html === '') {
			return $html;
		}

		$html = apply_filters('cookiefox_prepare_scripts', $html);

		if (defined('COOKIEFOX_UNMASK_SCRIPTS') && COOKIEFOX_UNMASK_SCRIPTS == true) {
			$html = str_replace('%script%', 'script', $html);
		}

		return $html;
	}

	private static function transient_key($lang) {
		return self::TRANSIENT_PREFIX . sanitize_key((string) $lang);
	}

	private static function remember_lang($lang) {
		$langs = get_option(self::LANGS_OPTION, array());
		if (!is_array($langs)) {
			$langs = array();
		}

		$lang = sanitize_key((string) $lang);
		if ($lang === '' || in_array($lang, $langs, true)) {
			return;
		}

		$langs[] = $lang;
		update_option(self::LANGS_OPTION, $langs, false);
	}
}
new Runtime_Config();

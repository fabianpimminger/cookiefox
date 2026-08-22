<?php
/**
 * Playground-only helpers. Not shipped with the plugin.
 *
 * @package CookieFox
 */

add_action('template_redirect', function () {
	if (!isset($_GET['cookiefox_e2e_mode'])) {
		return;
	}

	$mode = sanitize_key(wp_unslash($_GET['cookiefox_e2e_mode']));
	$settings = get_option('cookiefox', array());
	if (!is_array($settings)) {
		$settings = array();
	}

	$settings = cookiefox_e2e_marker_settings($settings);
	$settings['consent_type'] = ($mode === 'category') ? 'category' : 'simple';
	$rest = isset($_GET['cookiefox_e2e_rest']) ? sanitize_key(wp_unslash($_GET['cookiefox_e2e_rest'])) : '';
	$settings['config_via_rest'] = ($rest === '1' || $rest === 'on') ? 'on' : 'off';
	$integration = isset($_GET['cookiefox_e2e_integration']) ? sanitize_key(wp_unslash($_GET['cookiefox_e2e_integration'])) : '';
	$settings['integrations'] = ($integration === '1' || $integration === 'on') ? array('e2e-fake') : array();
	$consent_mode = isset($_GET['cookiefox_e2e_consent_mode']) ? sanitize_key(wp_unslash($_GET['cookiefox_e2e_consent_mode'])) : '';
	$settings['consent_mode'] = ($consent_mode === '1' || $consent_mode === 'on') ? 'on' : 'off';

	update_option('cookiefox', $settings, true);
	wp_safe_redirect(home_url('/'));
	exit;
});

add_action('init', 'cookiefox_e2e_ensure_options', 5);
add_action('init', 'cookiefox_e2e_seed_content', 20);
add_action('wp_footer', 'cookiefox_e2e_footer', 5);

add_filter('cookiefox_integrations', function ($integrations) {
	if (!is_array($integrations)) {
		$integrations = array();
	}
	$integrations['e2e-fake'] = 'E2E Fake';
	return $integrations;
});

add_action('cookiefox_integration_e2e-fake', function () {
	add_action('wp_footer', function () {
		echo '<script type="text/plain" data-cookiefox-integration="e2e-fake">window.__cookiefoxIntegration = true;</script>';
	}, 30);
});

add_filter('cookiefox_consent_e2e-fake', function () {
	return '<script>if(window.cookiefox&&window.cookiefox.api&&window.cookiefox.api.activateIntegration){window.cookiefox.api.activateIntegration("e2e-fake");}</script>';
});

function cookiefox_e2e_marker_settings($settings) {
	if (!is_array($settings)) {
		$settings = array();
	}

	$settings['cookie_notice_enabled'] = 'on';
	$settings['notice_display'] = 'banner';
	$settings['notice_delay'] = 0;
	$settings['block_embeds'] = 'on';
	$settings['scripts_always'] = '<script>window.__cookiefoxAlways = true;</script>';
	$settings['scripts_consent'] = '<script>window.__cookiefoxConsent = "accept";</script>';
	$settings['scripts_no_consent'] = '<script>window.__cookiefoxConsent = "decline";</script>';

	return $settings;
}

function cookiefox_e2e_ensure_options() {
	$settings = cookiefox_e2e_marker_settings(get_option('cookiefox', array()));
	if (!isset($settings['consent_type']) || $settings['consent_type'] === '') {
		$settings['consent_type'] = 'simple';
	}
	update_option('cookiefox', $settings, true);
}

function cookiefox_e2e_register_types() {
	register_post_type('cookiefox_cookie', array(
		'public' => false,
		'label' => 'Cookies',
	));
	register_taxonomy('cookiefox_category', array('cookiefox_cookie'), array(
		'public' => false,
		'label' => 'Categories',
	));
}

function cookiefox_e2e_seed_content() {
	try {
		cookiefox_e2e_insert_seed_content();
	} catch (\Throwable $e) {
		return;
	}
}

function cookiefox_e2e_insert_seed_content() {
	cookiefox_e2e_register_types();

	$categories = array(
		array(
			'name' => 'Functional',
			'slug' => 'functional',
			'always_on' => 'on',
			'unblock_embeds' => 'off',
			'order' => 0,
		),
		array(
			'name' => 'Marketing',
			'slug' => 'marketing',
			'always_on' => 'off',
			'unblock_embeds' => 'off',
			'order' => 1,
		),
		array(
			'name' => 'External Media',
			'slug' => 'external-media',
			'always_on' => 'off',
			'unblock_embeds' => 'on',
			'order' => 2,
		),
	);

	$term_ids = array();
	foreach ($categories as $category) {
		$existing = term_exists($category['slug'], 'cookiefox_category');
		if ($existing) {
			$term_ids[$category['slug']] = (int) $existing['term_id'];
			continue;
		}

		$result = wp_insert_term($category['name'], 'cookiefox_category', array(
			'slug' => $category['slug'],
		));
		if (is_wp_error($result)) {
			continue;
		}

		$term_ids[$category['slug']] = (int) $result['term_id'];
		add_term_meta($result['term_id'], 'always_on', $category['always_on'], true);
		add_term_meta($result['term_id'], 'unblock_embeds', $category['unblock_embeds'], true);
		add_term_meta($result['term_id'], 'order', $category['order'], true);
	}

	if (!empty($term_ids['marketing'])) {
		update_term_meta($term_ids['marketing'], 'consent_mode', array('ad_storage', 'ad_user_data', 'ad_personalization'));
	}

	if (empty($term_ids['marketing'])) {
		return;
	}

	$existing = get_posts(array(
		'post_type' => 'cookiefox_cookie',
		'name' => 'e2e-marketing-pixel',
		'post_status' => 'any',
		'posts_per_page' => 1,
	));

	if (!empty($existing)) {
		$post_id = $existing[0]->ID;
	} else {
		$post_id = wp_insert_post(array(
			'post_type' => 'cookiefox_cookie',
			'post_status' => 'publish',
			'post_title' => 'E2E Marketing Pixel',
			'post_name' => 'e2e-marketing-pixel',
		), true);
		if (is_wp_error($post_id)) {
			return;
		}
	}

	update_post_meta($post_id, 'scripts_consent', '<script>window.__cookiefoxMarketing = true;</script>');
	update_post_meta($post_id, 'scripts_no_consent', '<script>window.__cookiefoxMarketing = false;</script>');
	wp_set_object_terms($post_id, array((int) $term_ids['marketing']), 'cookiefox_category');

	$existing_integration = get_posts(array(
		'post_type' => 'cookiefox_cookie',
		'name' => 'e2e-fake-integration',
		'post_status' => 'any',
		'posts_per_page' => 1,
	));

	if (!empty($existing_integration)) {
		$integration_id = $existing_integration[0]->ID;
	} else {
		$integration_id = wp_insert_post(array(
			'post_type' => 'cookiefox_cookie',
			'post_status' => 'publish',
			'post_title' => 'E2E Fake Integration',
			'post_name' => 'e2e-fake-integration',
		), true);
		if (is_wp_error($integration_id)) {
			return;
		}
	}

	update_post_meta($integration_id, 'type', 'integration');
	update_post_meta($integration_id, 'integration', 'e2e-fake');
	wp_set_object_terms($integration_id, array((int) $term_ids['marketing']), 'cookiefox_category');
}

function cookiefox_e2e_footer() {
	echo '<p><a class="open-cookiefox" href="#">Open CookieFox</a></p>';
	$iframe = '<iframe title="e2e-youtube" src="https://www.youtube.com/embed/dQw4w9WgXcQ" width="560" height="315"></iframe>';
	echo apply_filters('cookiefox_consent', $iframe);
}

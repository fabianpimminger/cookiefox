<?php
/**
 * Uninstall CookieFox.
 *
 * @package CookieFox
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('cookiefox');
delete_option('cookiefox_config_langs');

register_post_type('cookiefox_cookie', array(
	'public' => false,
));
register_taxonomy('cookiefox_category', array('cookiefox_cookie'));

$posts = get_posts(array(
	'post_type' => 'cookiefox_cookie',
	'post_status' => 'any',
	'numberposts' => -1,
	'fields' => 'ids',
));

foreach ($posts as $post_id) {
	wp_delete_post($post_id, true);
}

$terms = get_terms(array(
	'taxonomy' => 'cookiefox_category',
	'hide_empty' => false,
));

if (!is_wp_error($terms)) {
	foreach ($terms as $term) {
		wp_delete_term($term->term_id, 'cookiefox_category');
	}
}

<?php
/**
 * Rest_API.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Rest_API {
	public function __construct() {
		add_action('rest_api_init', array($this, 'init'));
	}

	public function init() {
		register_rest_route('cookiefox/v1', '/cookies', array(
			'methods' => 'GET',
			'callback' => array($this, 'endpoint_cookies'),
			'permission_callback' => '__return_true'
		));
	}

	public function rest_endpoint_setup() {
		do_action('cookiefox_internationalization_init');
	}

	public function endpoint_cookies(\WP_REST_Request $request) {
		$this->rest_endpoint_setup();

		return Runtime_Config::get();
	}
}
new Rest_API();

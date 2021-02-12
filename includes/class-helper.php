<?php
/**
 * Helper.
 *
 * @package CookieFox
 */

namespace CookieFox;

defined('ABSPATH') || exit;

class Helper {
	public static function get_option( $key = '', $default = false ) {
    if ( function_exists( 'cmb2_get_option' ) ) {
    	return cmb2_get_option( 'cookiefox', $key, $default );
    }

    $opts = get_option( 'cookiefox', $default );

    $val = $default;

    if ( 'all' == $key ) {
      $val = $opts;
    } elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
      $val = $opts[ $key ];
    }

    return $val;
	}
}
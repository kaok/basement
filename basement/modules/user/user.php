<?php
defined('ABSPATH') or die();

class Basement_User {
	private static $instance = null;
	public static $textdomain = 'basement';
	public static $attr_prefix = 'basement_user';

	public function __construct() {
		require 'user/login.php';
		require 'user/password.php';
		require 'user/register.php';
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_User();
		}
		return self::$instance;
	}

	public function current_page_url( $delete_query = false ) {
		if ( !empty($_SERVER[ 'HTTPS' ]) && $_SERVER[ 'HTTPS' ] == 'on' ) {
			$page_url .= "https://";
		} else {
			$page_url = 'http://';
		}

		if ($_SERVER["SERVER_PORT"] != "80") {
			$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}

		if ( $delete_query ) {
			return strtok( $page_url, '?');
		}
		return $page_url;
	}

	public function sanitize_redirect( $url, $fallback_url = '' ) {
		if ( $url ) {
			$url = wp_sanitize_redirect( $url );
			$response = wp_remote_head( $url, array( 'timeout' => 5 ) );
			$accepted_status_codes = array( 200, 301, 302 );
			if ( ! is_wp_error( $response ) &&
				in_array( wp_remote_retrieve_response_code( $response ), $accepted_status_codes ) ) {
				return $url;
			}
			if ( strpos( $url, 'http://' ) !== false || strpos( $url, 'https://' ) !== false ) {
				return $fallback_url;
			}
			return $this->sanitize_redirect( get_option( 'siteurl' ) . $url, $fallback_url );
		}
		return $fallback_url;
	}
}


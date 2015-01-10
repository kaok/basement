<?php
defined('ABSPATH') or die();

class Basement_Ecommerce_Woocommerce {

	private static $instance = null;
	private $version = '1.0.0';

	public static function init() {
		return self::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_Ecommerce_Woocommerce();
		}
		return self::$instance;
	}

	public static function enabled() {
		return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

	public static function print_notices() {
		if ( self::enabled() ) {
			wc_print_notices();
		}
	}

}
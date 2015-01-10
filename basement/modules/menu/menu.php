<?php
defined('ABSPATH') or die();

class Basement_Menu {

	private static $instance = null;

	/**
	 * Init class instance
	 */
	public function __construct() {
		$this->register_nav_menus();
	}

	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_Menu();
		}
		return self::$instance;
	}

	public function register_nav_menus() {
		if ( ( $menus = Basement_Config::section( 'menus' ) ) ) {
			register_nav_menus( $menus );
		}
	}

}




















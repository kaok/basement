<?php
/**
 * Plugin Name: Basement Framework
 * Plugin URI: http://aisconverse.com
 * Description: A solid basement for Basement themes
 * Version: 1.0
 * Author: Aisconverse team
 * Author URI: http://aisconverse.com
 * License: GPL2
 */

defined('ABSPATH') or die();

if ( !class_exists( 'Basement' ) ) {
	
	define( 'BASEMENT_TEXTDOMAIN', 'basement_framework' );

	class Basement {
		private static $url = null;

		private $widgets;

		private static $modules;
		private static $instance = null;

		public static function init() {
			add_theme_support( 'basement' );
			return self::instance();
		}

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new Basement();
			}
			return self::$instance;
		}

		/**
		 * Init class instance
		 */
		public function __construct() {
			Basement_Autoload::init();
			Basement_Theme::init();
			Basement_Hook::init();
			Basement_Menu::init();
			Basement_Widgets::init();
			Basement_Settings::init();
			Basement_Admin::init();
			$this->load_textdomain();
		}

		public function load_textdomain() {
			load_theme_textdomain( BASEMENT_TEXTDOMAIN, __DIR__ . '/translations' );
		}


		public static function directory() {
			return __DIR__;
		}

		public static function url() {
			if ( null === self::$url ) {
				self::$url = Basement_Url::of_file( __DIR__ );
			}
			return self::$url;
		}

		/**
		 * Check if POST for plugin exists
		 *
		 * @return bool
		 */
		private function is_post() {
			return count( $_POST ) && 
					isset( $_POST[ BASEMENT_TEXTDOMAIN ] ) && 
					is_array( $_POST[ BASEMENT_TEXTDOMAIN ] ) && 
					count( $_POST[ BASEMENT_TEXTDOMAIN ] ) ? $_POST[ BASEMENT_TEXTDOMAIN ] : array() ;
		}

	}

	add_action( 'after_setup_theme', 'basement_init' ); 
}



function basement_init() {
	require 'modules/autoload/autoload.php';
	Basement::init();
	do_action( 'basement_loaded' );
	do_action( 'basement_plugins_loaded' );
}

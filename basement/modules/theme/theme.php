<?php
defined('ABSPATH') or die();

class Basement_Theme {

	private static $instance = null;

	public function __construct() {
		$this->define_constants();
		add_filter( 'basement_framework_theme_settings_page_params', array( &$this, '_theme_settings_page_params_filter' ) );
		add_filter( 'basement_framework_theme_settings_page_params', array( &$this, 'theme_settings_page_params_filter' ) );
	}

	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_Theme();
		}
		return self::$instance;
	}

	public function define_constants() {
		/**
		 * Set the theme textdomain which is equal the theme name
		 */
		if ( !defined( 'THEME_TEXTDOMAIN' ) ) {
			define( 'THEME_TEXTDOMAIN', wp_get_theme()->get_template() );
		}

		if ( !defined( 'IMAGES' ) ) {
			define( 'IMAGES', get_template_directory_uri() . '/assets/images');
		}
	}

	public function _theme_settings_page_params_filter( $params = array() ) {
		$params[ 'form_type' ] = 'simple_options';
		return $params;
	}

	public function theme_settings_page_params_filter( $params = array() ) { 
		return $params; 
	}

}
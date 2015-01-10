<?php
defined('ABSPATH') or die();

class Basement_Widgets {

	private static $instance = null;

	public function __construct() {
		add_action( 'widgets_init', array( &$this, 'register_sidebars' ) );
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );
	}

	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_Widgets();
		}
		return self::$instance;
	}

	/**
	 * Register sidebars from sidebars config.
	 * The config should be placed in the theme folder /basement/configs/sidebars.php and
	 * contain an array of arrays with common WordPress sidebars config.
	 *
	 * Each sidebar config can have dependensies array. Currenlty just "woocommerce" dependency available.
	 */
	public function register_sidebars() {
		$sidebars = Basement_Config::section( 'sidebars' );

		if ( empty( $sidebars ) ) {
			return;
		}

		foreach ( $sidebars as $sidebar ) {
			$enabled = true;
			if ( !empty( $sidebar[ 'dependencies' ] ) && is_array( $sidebar[ 'dependencies' ] ) ) {
				foreach( $sidebar[ 'dependencies' ] as $dependency ) {
					if ( 'woocommerce' == $dependency && !Basement_Ecommerce_Woocommerce::enabled() ) {
						$enabled = false;
						break;
					}
				}
			}
			if ( $enabled ) {
				register_sidebar( $sidebar );
			}
		}
	}

	public function widgets_init() {
		$widgets = Basement_Config::section( 'widgets' );

		if ( empty( $widgets ) ) {
			return;
		}

		foreach ( $widgets as $widget ) {
			$class = 'Widgets_Collection_' . $widget;
			if ( class_exists( $class ) ) {
				register_widget( $class );
			}
		}

	}

}
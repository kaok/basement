<?php
defined('ABSPATH') or die();

class Basement_Admin {

	private static $instance = null;

	/**
	 * Init class instance
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'init' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
			Basement_Admin_Colorscheme::init();
		}
	}

	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_Admin();
		}
		return self::$instance;
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_style( BASEMENT_TEXTDOMAIN . '_css', Basement::url() . '/assets/css/production.min.css', array( 'wp-admin' ) );
		wp_enqueue_script( BASEMENT_TEXTDOMAIN . '_js', Basement::url() . '/assets/javascript/production.min.js', array( 'jquery' ), null, true );
	}

	/**
	 * Renders admin page
	 */
	public function admin_page() {
		if ( !current_user_can( 'manage_options' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.', BASEMENT_TEXTDOMAIN ) );
		if ( $_GET['page'] != 'basement-framework') {
			$view_name = str_replace( 'basement-framework-', '', $_GET['page'] );
		} else {
			$view_name = 'basement';
		}
		$view_path = 'views/' . $view_name . '.php';
		if ( !file_exists( __DIR__ . '/' .$view_path ) ) {
			wp_die( __( 'Sorry, the page is not found.', BASEMENT_TEXTDOMAIN ) );
		}
		require $view_path;
	}

	/**
	 * Check if POST for plugin exists
	 *
	 * @return bool
	 */
	private function is_post() {
		return count( $_POST ) && 
				isset( $_POST[ $this->textdomain ] ) && 
				is_array( $_POST[ $this->textdomain ] ) && 
				count( $_POST[ $this->textdomain ] ) ? $_POST[ $this->textdomain ] : array() ;
	}

}




















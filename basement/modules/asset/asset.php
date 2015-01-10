<?php
defined('ABSPATH') or die();

class Basement_Asset {

	private static $instance = null;
	private $version = '1.0.0';
	private $styles = array();
	private $scripts = array();
	private $inline_css = array();
	private $inline_js = array();

	public static function init() {
		return self::instance();
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_Asset();
		}
		return self::$instance;
	}

	public function __construct() {

		$this->inline_css = get_option( BASEMENT_TEXTDOMAIN . '_asset_inline_css', '' );
		$this->inline_js = get_option( BASEMENT_TEXTDOMAIN . '_asset_inline_js', '' );

		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'register_theme_settings' ) );
			add_filter( 
				BASEMENT_TEXTDOMAIN . '_theme_settings_config_filter', 
				array( &$this, 'theme_settings_config_filter' ) 
			);
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
			add_action( 'wp_footer', array( &$this, 'wp_footer' ) );
		}
	}

	public static function add_style( $handle, $src, $deps = array(), $ver = null, $media = null ) {
		self::instance()->styles[] = array(
			'handle' => $handle,
			'src' => $src,
			'deps' => $deps,
			'ver' => $ver,
			'media' => $media,
		);
	}

	public static function add_script( $handle, $src, $deps = array(), $ver = null, $in_footer = false ) {
		self::instance()->scripts[] = array(
			'handle' => $handle,
			'src' => $src,
			'deps' => $deps,
			'ver' => $ver,
			'in_footer' => ( bool )$in_footer,
		);
	}
	
	public static function add_footer_script( $handle, $src, $deps = array(), $ver = null ) {
		self::add_script( $handle, $src, $deps, $ver, true );
	}

	public function register_theme_settings() {
		register_setting( 'basement_theme_options', BASEMENT_TEXTDOMAIN . '_asset_inline_css' );
		register_setting( 'basement_theme_options', BASEMENT_TEXTDOMAIN . '_asset_inline_js' );
	}

	/**
	 * Inline assets settings config
	 */
	public function theme_settings_config_filter( $config = array() ) {
		$config[ 'inline_assets' ] = array(
			'title' => __( 'Inline assets', BASEMENT_TEXTDOMAIN ),
			'blocks' => array(
				array(
					'title' => __( 'Inline CSS', BASEMENT_TEXTDOMAIN ),
					'description' => __( 'This CSS will be printed on every page header. Don\'t use <style> tags, fill the field with plain CSS', BASEMENT_TEXTDOMAIN ),
					'inputs' => array(
						array(
							'type' => 'codeeditor',
							'name' => BASEMENT_TEXTDOMAIN . '_asset_inline_css',
							'value' => $this->inline_css,
							'editor_mode' => 'text/css'
						)
					)
				),
				array(
					'title' => __( 'Inline JavaScript', BASEMENT_TEXTDOMAIN ),
					'description' => __( 'This JavaScript will be printed on every pages footer. It\'s useful to add Google Analytics or something like that. Don\'t use <script> tags, fill the field with plain JavaScript', BASEMENT_TEXTDOMAIN ),
					'inputs' => array(
						array(
							'type' => 'codeeditor',
							'name' => BASEMENT_TEXTDOMAIN . '_asset_inline_js',
							'value' => $this->inline_js,
							'editor_mode' => 'text/javascript'
						)
					)
				)
			)
		);
		return $config;
	}

	// TODO: remove style tag if exists
	public function enqueue_scripts() {
		if ( isset( $this->inline_css ) ) {
			wp_add_inline_style( 'theme_style', $this->inline_css );
		}
		foreach ( $this->styles as $style ) {
			wp_enqueue_style( $style[ 'handle' ], $style[ 'src' ], $style[ 'deps' ], $style[ 'ver' ], $style[ 'media' ] );
		}

		foreach ( $this->scripts as $script ) {
			wp_enqueue_script( $script[ 'handle' ], $script[ 'src' ], $script[ 'deps' ], $script[ 'ver' ], $script[ 'in_footer' ] );
		}
	}	

	// TODO: remove script tag if exists
	public function wp_footer() {
		if ( isset( $this->inline_js ) ) {
			echo '<script>' . $this->inline_js . '</script>';
		}
	}


	public static function favicon() {
		$favicon_id = get_option( BASEMENT_TEXTDOMAIN . '_favicon' );
		
		if ( $favicon_id ) {
			$favicon = wp_get_attachment_url( $favicon_id );
		} else {
			$favicon = '/favicon.ico';
		}

		echo '<link href="' . $favicon . '" rel="shortcut icon" type="image/x-icon">';
	}


}

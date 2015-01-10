<?php
defined('ABSPATH') or die();

class Basement_Cpt {

	protected $post_type = 'post';
	protected $post_type_args = array();
	/**
	 * Show featured image as a column in admin post type list
	 * @var boolean
	 */
	protected $show_thumbnail_admin_column = false;

	static protected $setting_panel_config = array();

	public function __construct( $post_type = '' ) {
		if ( $post_type ) {
			$this->post_type = $post_type;
		}

		 $this->fill_post_type_args();
		 $this->register_type();
		 $this->register_taxonomies();
		 $this->add_hooks();

		add_action( 
			'add_meta_boxes_' . $this->post_type,
			array( &$this, '_add_parameters_meta_box' ) 
		);

		add_action( 
			'add_meta_boxes_' . $this->post_type,
			array( &$this, 'add_meta_boxes' ) 
		);

		add_action( 
			'do_meta_boxes', 
			array( &$this, 'do_meta_boxes' )
		);

		/* Admin columns */
		add_filter( 
			'manage_edit-' . $this->post_type . '_columns', 
			array( &$this, 'filter_admin_list_header' )
		);

		add_action( 
			'manage_' . $this->post_type . '_posts_custom_column', 
			array( &$this, 'filter_admin_list_columns' ),
			10, 
			2 
		);

		if ( $this->show_thumbnail_admin_column ) {
			add_filter( 
				'manage_edit-' . $this->post_type . '_columns', 
				array( &$this, 'add_featured_image_column_header' )
			);

			add_action( 
				'manage_' . $this->post_type . '_posts_custom_column', 
				array( &$this, 'add_featured_image_column' ),
				10, 
				2 
			);
		}

		/* Parameners panel config */
		add_filter( 
			BASEMENT_TEXTDOMAIN . '_' . $this->post_type . '_panel_config', 
			array( &$this, 'filter_parameters_panel_config' )
		);

		/**
		 * TODO: add SEO section for all public CPTs
		 */

		add_action(
			'save_post', 
			array( &$this, 'pre_save_post' )
		);


	}

	protected function fill_post_type_args() {}

	protected function register_type() {
		if ( !post_type_exists( $this->post_type ) ) {
			register_post_type( 
				apply_filters( 'basement_cpt_' . $this->post_type . '_register_filter_name', $this->post_type ),
				$this->post_type_args 
			);
		}
	}

	protected function register_taxonomies() {}

	protected function add_hooks() {}

	public function add_meta_boxes() {}

	public function do_meta_boxes() {}

	public function _add_parameters_meta_box() {
		global $post;
		self::$setting_panel_config = apply_filters( BASEMENT_TEXTDOMAIN . '_' . $this->post_type . '_panel_config', array() );
		self::$setting_panel_config = apply_filters( BASEMENT_TEXTDOMAIN . '_post_type_panel_config', self::$setting_panel_config );
		
		if ( self::$setting_panel_config ) {
			add_meta_box( 
				BASEMENT_TEXTDOMAIN . '_metabox', 
				__( 'Parameters', BASEMENT_TEXTDOMAIN ), 
				array( &$this, '_create_parameters_meta_box' ), 
				$this->post_type, 
				'normal', 
				'core' 
			);
		}

	}

	public function _create_parameters_meta_box() {

		$dom = new DOMDocument( '1.0', 'UTF-8' );
		$panel = $dom->appendChild( $dom->createElement( 'div' ) );
		$panel->setAttribute( 'id', 'basement_post_parameters_panel' );
		/**
		 * Setting_Panel config filter: basement_settings_post_panel_config
		 */
		
		$panel->appendChild( 
			$dom->importNode( 
				Basement_Settings_Panel::instance()->create_panel( 
					self::$setting_panel_config,
					apply_filters( BASEMENT_TEXTDOMAIN . '_' . $this->post_type . '_params', array( 
							'no_form' => true,
							'no_wrap_class' => true
						)
					)
				), true 
			) 
		);

		echo $dom->saveHTML();
	}

	public function filter_admin_list_header( $columns ) {
		return $columns;
	}

	public function filter_admin_list_columns( $column_name, $post_id ) {
		return $column_name;
	}

	public function add_featured_image_column_header( $columns ) {
		if ( !isset($columns['icon'] ) ) {
			$columns = array_slice( $columns, 0, 1, true ) + array( 'icon' => 'Image' ) + array_slice( $columns, 1, count( $columns ) - 1, true );
		}
		return $columns;
	}

	public function add_featured_image_column( $column_name, $post_id ) {
		switch ( $column_name ) {
			case 'icon':
				if ( !( $image_src = apply_filters( 'filter_' . $this->post_type . '_admin_column_image_src', '', $post_id ) ) ) {
					$image_src = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );
				}
				echo '<div style="max-width: 100px;"><a href="' . get_edit_post_link( $post_id ) . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', BASEMENT_TEXTDOMAIN ), get_the_title( $post_id ) ) ) . '"><img style="max-width: 100%;" src="' . $image_src .'" /></a></div>';
				break;
			default: break;
		}
		return $column_name;
	}

	public function filter_parameters_panel_config( $config ) {
		return $config;
	}

	public function pre_save_post() {
		global $post;
		if ( !( $post instanceof WP_Post ) ) {
			return;
		}
		if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || $post->post_type != $this->post_type ) {
			return $post->ID;
		}

		$this->save_post();
	}

	public function save_post() {}


}
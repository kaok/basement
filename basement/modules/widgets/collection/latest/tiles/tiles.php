<?php
defined('ABSPATH') or die();

class Basement_Widgets_Collection_Latest_Tiles extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		parent::__construct(
			false, // Base ID
			__( 'Basement latest items tiles', BASEMENT_TEXTDOMAIN ), // Name
			array( 'description' => __( 'Latest items tiles of post type', BASEMENT_TEXTDOMAIN ) )
		);
	}

	function widget( $args, $instance ) {
		$markup = $args[ 'before_widget' ];

		if ( !empty( $instance[ 'title' ] ) ) {
			if ( !empty( $instance[ 'title_link' ] ) ) {
				$header = '<a href="' . $instance[ 'title_link' ] . '">' . apply_filters( 'widget_title', $instance[ 'title' ] ) . '</a>';
			} else {
				$header = '<span>' . apply_filters( 'widget_title', $instance[ 'title' ] ) . '</span>';
			}
			$markup .= $args[ 'before_title' ] . $header . $args[ 'after_title' ];
		}

		if ( post_type_exists( $instance[ 'post_type' ] ) ) {
			
			if ( !$instance[ 'count' ] ) {
				$instance[ 'count' ] = 4;
			}

			$items = get_posts( array(
				'post_type' => $instance[ 'post_type' ],
				'posts_per_page' => $instance[ 'count' ],
				'order' => 'ASC'
			) );

			if ( count( $items ) ) {
				$dom = new DOMDocument( '1.0', 'UTF-8' );
				$ul = $dom->appendChild( $dom->createElement( 'ul' ) );
				$ul->setAttribute( 'class', 'latest-list' );


				
				foreach ( $items as $post ) {
					$li = $ul->appendChild( $dom->createElement( 'li' ) );
					
					$a = $li->appendChild( $dom->createElement( 'a' ) );
					$a->setAttribute( 'href', get_permalink( $post->ID ) );

					$image = $a->appendChild( $dom->createElement( 'img' ) );
					$src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
					if ( $src && isset( $src[ 0 ] ) ) {
						$image->setAttribute( 'src', $src[ 0 ] );
					} else {
						$image->setAttribute( 'src', '' );
					}
					$image->setAttribute( 'alt', '' );

				}
				$markup .= $dom->saveHTML();
			}

		}

		if ( !empty( $instance[ 'text' ] ) ) {
			$markup .= '<p>' . do_shortcode( nl2br( $instance[ 'text' ] ) ) . '</p>';
		}

		$markup .= $args['after_widget'];

		echo $markup;
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = !empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['title_link'] = !empty( $new_instance['title_link'] ) ? strip_tags( $new_instance['title_link'] ) : '';
		$instance['post_type'] = !empty( $new_instance['post_type'] ) ? $new_instance['post_type'] : '';
		$instance['count'] = !empty( $new_instance['count'] ) ? ( int )strip_tags( $new_instance['count'] ) : '';

		return $instance;
	}

	function form( $instance ) {
		$dom = new DOMDocument( '1.0', 'UTF-8' );
		$contaner = $dom->appendChild( $dom->createElement( 'p' ) );

		// Title
		$label = $contaner->appendChild( $dom->createElement( 'label', __( 'Title', BASEMENT_TEXTDOMAIN ) ) );
		$label->setAttribute( 'for', $this->get_field_id( 'title' ) );

		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';

		$input = new Basement_Form_Input( array(
				'name' => $this->get_field_name( 'title' ),
				'id' => $this->get_field_id( 'title' ),
				'value' => esc_attr( $title ),
				'class' => 'widefat',
				'no_wrapper' => true
			) 
		);

		$contaner->appendChild( $dom->importNode( $input->create(), true ) );

		// Title link
		$label = $contaner->appendChild( $dom->createElement( 'label', __( 'Title link', BASEMENT_TEXTDOMAIN ) ) );
		$label->setAttribute( 'for', $this->get_field_id( 'title_link' ) );

		$title_link = isset( $instance[ 'title_link' ] ) ? $instance[ 'title_link' ] : '';

		$input = new Basement_Form_Input( array(
				'name' => $this->get_field_name( 'title_link' ),
				'id' => $this->get_field_id( 'title_link' ),
				'value' => esc_attr( $title_link ),
				'class' => 'widefat',
				'no_wrapper' => true
			) 
		);

		$contaner->appendChild( $dom->importNode( $input->create(), true ) );

		// Post type
		$posts_types = get_post_types( array(), 'objects', array( '_builtin' => 'false' ) );

		$post_type = isset( $instance[ 'post_type' ] ) ? $instance[ 'post_type' ] : '';

		$values = array();

		foreach ( $posts_types as $post_type_name => $post_type_object ) {
			$values[ $post_type_name ] = $post_type_object->labels->name;
		}

		$select = new Basement_Form_Input_Select( array(
				'label_text' =>  __( 'Post type', BASEMENT_TEXTDOMAIN ),
				'name' => $this->get_field_name( 'post_type' ),
				'id' => $this->get_field_id( 'post_type' ),
				'values' => $values,
				'class' => 'basement_widget_select',
				'current_value' => isset( $instance[ 'post_type' ] ) ? $instance[ 'post_type' ] : 'post',
				'attributes'
			) 
		);

		$contaner->appendChild( $dom->importNode( $select->create(), true ) );

		// Items count
		$label = $contaner->appendChild( $dom->createElement( 'label', __( 'Count', BASEMENT_TEXTDOMAIN ) ) );
		$label->setAttribute( 'for', $this->get_field_id( 'count' ) );

		$count = isset( $instance[ 'count' ] ) ? $instance[ 'count' ] : '';

		$input = new Basement_Form_Input( array(
				'name' => $this->get_field_name( 'count' ),
				'id' => $this->get_field_id( 'count' ),
				'value' => esc_attr( $count ),
				'class' => 'widefat',
				'no_wrapper' => true
			) 
		);

		$contaner->appendChild( $dom->importNode( $input->create(), true ) );

		echo $dom->saveHTML();
	}

}
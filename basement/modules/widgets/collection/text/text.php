<?php
defined('ABSPATH') or die();

class Basement_Widgets_Collection_Text extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		parent::__construct(
			false, // Base ID
			__('Basement simple text', BASEMENT_TEXTDOMAIN ), // Name
			array( 'description' => __( 'A simple text widget with header', BASEMENT_TEXTDOMAIN ) )
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

		if ( !empty( $instance[ 'text' ] ) ) {
			$markup .= '<p>' . do_shortcode( nl2br( $instance[ 'text' ] ) ) . '</p>';
		}

		$markup .= $args['after_widget'];

		echo $markup;
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['title_link'] = ( ! empty( $new_instance['title_link'] ) ) ? strip_tags( $new_instance['title_link'] ) : '';
		$instance['text'] = ( ! empty( $new_instance['text'] ) ) ? strip_tags( $new_instance['text'] ) : '';

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

		// Text
		$label = $contaner->appendChild( $dom->createElement( 'label', __( 'Text', BASEMENT_TEXTDOMAIN ) ) );
		$label->setAttribute( 'for', $this->get_field_id( 'text' ) );

		$title = isset( $instance[ 'text' ] ) ? $instance[ 'text' ] : '';

		$input = new Basement_Form_Input_Textarea( array(
				'name' => $this->get_field_name( 'text' ),
				'id' => $this->get_field_id( 'text' ),
				'value' => esc_attr( $title ),
				'class' => 'widefat',
				'no_wrapper' => true
			) 
		);
		
		$contaner->appendChild( $dom->importNode( $input->create(), true ) );

		echo $dom->saveHTML();
	}

}
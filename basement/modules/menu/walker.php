<?php
defined('ABSPATH') or die();

class Basement_Menu_Walker extends Walker_Nav_Menu {
	// add classes to ul sub-menus
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$classes = implode( ' ', apply_filters( 'basement_nav_menu_ul_css_class', array( 'sub-menu' ) ) );
		$output .= '<ul class="' . $classes . '" ' . apply_filters( 'basement_nav_menu_ul_attributes', '' ) . '>';
	}
	  
	// add main/sub classes to li's and links
	 function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $wp_query;

		// passed classes
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$class_names = esc_attr( implode( ' ', apply_filters( 'basement_nav_menu_li_css_class', array_filter( $classes ), $item ) ) );

		// build html
		$output .= '<li id="nav-menu-item-'. $item->ID . '" class="' . $class_names . '">';
	  
		// link attributes
		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		$attributes .= ' class="' . esc_attr( implode( ' ', apply_filters( 'basement_nav_menu_a_css_class', array( 'menu-link' ), $item ) ) ) . '"';
	  
		$item_output = sprintf( '%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
			$args->before,
			apply_filters( 'basement_nav_menu_a_attributes', $attributes, $item ),
			$args->link_before,
			apply_filters( 'the_title', $item->title, $item->ID ),
			$args->link_after,
			$args->after
		);

		// build html
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	function display_element ( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
		// check, whether there are children for the given ID and append it to the element with a (new) ID
		$element->has_children = isset( $children_elements[ $element->ID ] ) && !empty( $children_elements[ $element->ID ] );
		return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}
}
<?php
defined('ABSPATH') or die();

class Basement_Taxonomy {
	private $textdomain = 'basement_taxonomy';
	private static $instance = null;
	private $version = '1.0.0';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_Taxonomy();
		}
		return self::$instance;
	}

	public function get_current_post_terms( $taxonomy = 'category' ) {
		global $post;
		if ( !taxonomy_exists( $taxonomy ) || !$post && !( $post instanceof WP_Post ) ) {
			return array();
		}

		$taxonomies = wp_get_object_terms( $post->ID, $taxonomy );

		foreach ( $taxonomies as $index => $taxonomy ) {
			if ( $taxonomy->slug == 'uncategorized') {
				unset( $taxonomies[ $index ] );
				break;
			}
		}
		return $taxonomies;
	}

	public function get_comma_separated_terms_names( $taxonomy = 'category', $show_links = true ) {
		$terms = $this->get_current_post_terms( $taxonomy );
		if ( empty( $terms ) ) {
			return '';
		}

		$links = array();

		$dom = new DOMDocument( '1.0', 'UTF-8' );

		$terms_count = count( $terms );
		$current_term_index = 1;

		foreach ($terms as $term) {
			if ( $show_links ) {
				$link = $dom->appendChild( $dom->createElement( 'a', $term->name ) );
				$link->setAttribute( 'href', get_term_link( $term, $taxonomy ) );
			} else {
				$dom->appendChild( $dom->createElement( 'span', $term->name ) );
			}
			if ( $terms_count > $current_term_index ) {
				$dom->appendChild( $dom->createTextNode( ', ' ) );
				$current_term_index++;
			}
		}
		
		return $dom->saveHTML();
	}

}



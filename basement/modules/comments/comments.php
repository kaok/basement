<?php
defined('ABSPATH') or die();

class Basement_Comments {
	private $textdomain = 'basement_comments';
	private static $instance = null;
	private $version = '1.0.0';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_Comments();
		}
		return self::$instance;
	}


	/**
	 * Returns current post comments count with or without link
	 * @param  boolean $internal Is link links to the same pages anchor
	 * @param  boolean $hide_empty  Return empty string if no comments exist
	 * @return string  HTML-markup
	 */
	public function comments_link( $internal = false, $hide_empty = false ) {
		global $post;

		$comments_number = get_comments_number();

		if ( !$comments_number && $hide_empty ) {
			return '';
		}

		$dom = new DOMDocument( '1.0', 'UTF-8' );

		$comments_line = sprintf( 
			_n( 'One comment', '%1$s Comments', get_comments_number(), BASEMENT_TEXTDOMAIN ), 
			number_format_i18n( $comments_number ) 
		);

		$container = $dom->appendChild( $dom->createElement( 'a', $comments_line  ) );
		$link_classes = array( 'comment-link' );

		if ( $internal ) {
			$link_classes[] = 'internal';
		}

		$container->setAttribute( 'class', implode( ' ', $link_classes ) );
		$container->setAttribute( 'href', ( $internal ? '' : get_permalink() ) . '#comments' );

		return $dom->saveHTML();
	}

	/**
	 * Returns current post comments count with or without link
	 * @param  boolean $hide_empty  Return empty string if no comments exist
	 * @return string  HTML-markup
	 */
	public function comments_line( $hide_empty = false ) {
		global $post;

		$comments_number = get_comments_number();

		if ( !$comments_number && $hide_empty ) {
			return '';
		}

		$dom = new DOMDocument( '1.0', 'UTF-8' );

		$comments_line = sprintf( 
			_n( 'One comment', '%1$s Comments', get_comments_number(), BASEMENT_TEXTDOMAIN ), 
			number_format_i18n( $comments_number ) 
		);

		$container = $dom->appendChild( $dom->createElement( 'span', $comments_line ) );

		return $dom->saveHTML();
	}

	/**
	 * Returns current post comments count with or without link if comments exist
	 * @param  boolean $show_link Use link on comment line
	 * @param  boolean $permalink  Use permalinks in link href
	 * @return string  HTML-markup
	 */
	public function actual_comments_link( $show_link = true, $permalink = false ) {
		return $this->comments_link( $show_link, $permalink, true );
	}

}



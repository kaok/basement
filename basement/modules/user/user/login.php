<?php
defined('ABSPATH') or die();

class Basement_User_Login {
	private static $shortcodes_added = false;
	private static $instance = null;
	private $messages = array(
		'error' => array(),
		'success' => array()
	);
	private $form_data = array( 'user_login' => '' );
	public static $textdomain = 'basement';
	public static $attr_prefix = 'basement_user_login';

	public function __construct() {
		if ( !is_admin() ) {
			add_action( 'init', array( &$this, 'signon' ) );
			add_action( 'init', array( &$this, 'logout' ) );
			add_action( 'init', array( &$this, 'add_shortcodes' ) );
		}
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_User_Login();
		}
		return self::$instance;
	}

	public function add_shortcodes() {
		if ( self::$shortcodes_added ) {
			return;
		}
		add_shortcode( 'basement_login_form', array( &$this, 'render_login_form' ) );
		self::$shortcodes_added = true;
	}

	public function render_login_form( $atts ) {
		if ( is_user_logged_in() ) {
			global $user_ID;
			$user_data = get_userdata( $user_ID );
			$user_posts_link = '<a href="' . get_author_posts_url( $user_data->ID ) . '" title="' . $user_data->display_name . '">' . $user_data->display_name . '</a>';
			$referer_link = Basement_User::instance()->current_page_url( true );
			$logout_link = '<a href="' . esc_attr( add_query_arg( array(
						'basement_logout' => 'true',
						'redirect_to' => $referer_link
					), 
					$referer_link 
				) 
			) .'" title="'. __( 'Log out of this account', BASEMENT_TEXTDOMAIN ) . '">'. __( 'Log out', BASEMENT_TEXTDOMAIN ) . ' &raquo; </a>';
			$message = '<div class="' . self::$attr_prefix . '_user_already_logged_message">'. sprintf(__('You are currently logged in as %1$s. %2$s', BASEMENT_TEXTDOMAIN ), $user_posts_link, 
				$logout_link ) . '</div>';
			return $message;
		} else {
			extract( shortcode_atts(
				array(
					'show_lost_password_link' => true,
					'redirect_to' => Basement_User::instance()->current_page_url(),
					'button_text' => __( 'Log In', BASEMENT_TEXTDOMAIN ),
					'lost_password_page_url' => ''
				),
				$atts )
			);
			if ( is_int( $lost_password_page_url ) ) {
				$lost_password_page_url = get_permalink( $lost_password_page_url );
			}

			if ( isset( $_GET[ self::$attr_prefix . '_registration_email_failed' ] ) ) {
				$this->messages['error'][ self::$attr_prefix . '_registration_email_failed' ] = __( 'Sorry, something sent wrong and we can not complete registration. Please, try later.', BASEMENT_TEXTDOMAIN );
			}
			
			$markup = '';

			foreach ( $this->messages as $type => $type_messages ) {
				if ( count( $type_messages ) ) {
					$markup .= '<div class="' . self::$attr_prefix . '_message_' . $type . '">';
					foreach ( $type_messages as $key => $message ) {
						$markup .= '<div class="' . self::$attr_prefix . '_message_' . $key . '">';
						$markup .= $message;
						$markup .= '</div>';
					}
					$markup .= '</div>';
				}
			}

			$markup .= '<div class="' . self::$attr_prefix . '_login_form_wrapper">';
			$markup .= '<form action="" method="POST" class="sign-in">';
			$markup .= '<div class="' . self::$attr_prefix . '_login_form_user_login_input_wrapper">';
			// User name or email input
			$markup .= '<label for="' . self::$attr_prefix . '_login_form_user_login">';
			$markup .= __( 'Username or email', BASEMENT_TEXTDOMAIN );
			$markup .= '<input type="text" 
							name="' . self::$attr_prefix . '_login_form[user_login]"
							id="' . self::$attr_prefix . '_login_form_user_login" 
							value="' . $this->form_data[ 'user_login' ] . '" />';
			$markup .= '</div>';
			// User password input
			$markup .= '<div class="' . self::$attr_prefix . '_login_form_user_password_input_wrapper">';
			$markup .= '<label for="' . self::$attr_prefix . '_login_form_user_password">';
			$markup .= __('Password', BASEMENT_TEXTDOMAIN );
			$markup .= '</label>';
			$markup .= '<input type="password" 
							name="' . self::$attr_prefix . '_login_form[user_password]" 
							id="' . self::$attr_prefix .'_login_form_user_password" />';
			$markup .= '</div>';
			// "Remember me" checkbox
			$markup .= '<div class="' . self::$attr_prefix . '_login_form_remember_me_input_wrapper">';
			$markup .= '<label for="' . self::$attr_prefix . '_login_form_remeber_me">';
			$markup .= '<input name="' . self::$attr_prefix . '_login_form[remember]" 
							id="' . self::$attr_prefix . '_login_form_remeber_me" 
							type="checkbox" 
							checked="checked" 
							value="forever" />';
			$markup .= __('Remember me', BASEMENT_TEXTDOMAIN );
			$markup .= '</label>';
			$markup .= '</div>';
			// Submit button
			$markup .= '<div class="' . self::$attr_prefix . '_login_form_submit_input_wrapper">';
			$markup .= '<input type="submit" 
							name="submit" 
							value="' . __( 'Log in', BASEMENT_TEXTDOMAIN ) .'" />';
			$markup .= '</div>';
			// Hidden fields
			$markup .= '<input type="hidden" 
					name="' . self::$attr_prefix . '_login_form[redirect_to]" 
					value="' . $redirect_to . '" />';
			// Lost password link
			if ( $show_lost_password_link === true ) {
					$lost_password_page_url = Basement_User::instance()->sanitize_redirect( $lost_password_page_url, get_option('siteurl').'/wp-login.php?action=lostpassword' );
				$markup .= '<div class="' . self::$attr_prefix . '_login_form_lost_password_input_wrapper">';
				$markup .= '<a href="' . $lost_password_page_url .'">'. __( 'Lost password?', BASEMENT_TEXTDOMAIN ) . '</a>';
				$markup .= '</div>';
			}
			$markup .= wp_nonce_field( self::$attr_prefix . '_login_nonce' , self::$attr_prefix . '_login_form[nonce]', true, false );
			$markup .= '</form>';
			$markup .= '</div>';

			return $markup;
		}
	}

	public function signon() {
		if ( 'POST' != $_SERVER[ 'REQUEST_METHOD' ] ||
				empty( $_POST[ self::$attr_prefix . '_login_form' ] ) ||
				!is_array( $_POST[ self::$attr_prefix . '_login_form' ] ) ) {
			return;
		}

		extract( $_POST[ self::$attr_prefix . '_login_form' ] );

		if ( !wp_verify_nonce( $nonce, self::$attr_prefix . '_login_nonce' ) ) {
			$this->messages[ 'error' ][ self::$attr_prefix . '_login_invalid_nonce' ] = __( 'Form data is expired. Reload the page and try again, please.', BASEMENT_TEXTDOMAIN );
			return;
		}

		global $wppb_login, $wpdb;
		$user_login = !empty( $user_login ) && trim( $user_login ) ? $user_login : '' ;
		$user_password = !empty( $user_password ) && trim( $user_password ) ? $user_password : '' ;

		$this->form_data[ 'user_login' ] = $user_login;

		if ( !$user_login ) {
			$this->messages[ 'error' ][ self::$attr_prefix . '_login_username_is_empty' ] = __( 'Username or email are required.', BASEMENT_TEXTDOMAIN );
			return;
		}

		if ( !$user_password ) {
			$this->messages[ 'error' ][ self::$attr_prefix . '_login_password_is_empty' ] = __( 'Password is required.', BASEMENT_TEXTDOMAIN );
			return;
		}

		$remember = !empty( $remember ) && trim( $remember ) ? true : false ;
		$redirect_to = !empty( $redirect_to ) ? $redirect_to : '' ;

		if ( $db_user_login = $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM $wpdb->users WHERE user_email= %s LIMIT 1", $user_login ) ) ) {
			$user_login = $db_user_login;
		}

		$login_result = wp_signon(
			array(
				'user_login' => $user_login,
				'user_password' => $user_password,
				'remember' => $remember
			),
			false
		);

		if ( $login_result instanceof WP_Error ) {
			$error_codes = $login_result->get_error_codes();
			if ( in_array( 'invalid_username', $error_codes) ) {
				$this->messages[ 'error' ][ self::$attr_prefix . '_login_invalid_username' ] = __( 'Sorry, the username you entered is incorrect. Try again, please.', BASEMENT_TEXTDOMAIN );
			}
			if ( in_array( 'incorrect_password', $error_codes) ) {
				// TODO: add ability to set lost password page url
				$this->messages[ 'error' ][ self::$attr_prefix . '_login_incorrect_password' ] = __( 'Sorry, the password you entered is incorrect. Try again, please.', BASEMENT_TEXTDOMAIN );
			}
			return;
		}

		wp_redirect( $redirect_to = Basement_User::instance()->sanitize_redirect( $redirect_to, Basement_User::instance()->current_page_url( true ) ) );
		exit;
	}

	public function logout() {
		if ( 'GET' == $_SERVER[ 'REQUEST_METHOD' ] &&
			!empty( $_GET[ 'basement_logout' ] ) && (bool)$_GET[ 'basement_logout' ] ) {
			wp_logout();
			$redirect_to = empty( $_GET[ 'redirect_to' ] )  ? home_url() : $_GET[ 'redirect_to' ];
			wp_redirect( Basement_User::instance()->sanitize_redirect( $redirect_to, home_url() ) );
			exit;
		}
	}

}
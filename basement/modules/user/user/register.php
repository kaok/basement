<?php
defined('ABSPATH') or die();

class Basement_User_Register {
	private static $shortcodes_added = false;
	private static $instance = null;
	private $messages = array(
		'error' => array(),
		'success' => array()
	);
	private $form_data = array(
		'user_login' => '',
		'user_email' => ''
	);
	public static $textdomain = 'basement';
	public static $attr_prefix = 'basement_user_register';

	public function __construct() {
		if ( !is_admin() ) {
			add_action( 'init', array( &$this, 'create_registration_transient' ) );
			add_action( 'init', array( &$this, 'add_shortcodes' ) );
		}
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_User_Register();
		}
		return self::$instance;
	}

	public function add_shortcodes() {
		if ( self::$shortcodes_added ) {
			return;
		}
		add_shortcode( 'basement_register_form', array( &$this, 'render_register_form' ) );
		self::$shortcodes_added = true;
	}

	public function render_register_form( $atts ) {
		$activation_key = null;
		$hide_form = false;
		if ( !empty( $_GET[ 'key' ] ) && !empty( $_GET[ 'email' ] ) ) {
			extract( $_GET );
			if ( !is_email( $email ) || strlen( $key ) != 32 ) {
				$this->messages[ 'error' ][ self::$attr_prefix . '_wrong_data' ] = __( 'Sorry, the link data is not correct. Please, try to register again.', BASEMENT_TEXTDOMAIN );
			} else {
				$transient = get_site_transient( $key );
				if ( !$transient ) {
					$this->messages[ 'error' ][ self::$attr_prefix . '_transient_expired' ] = __( 'Sorry, it seems your activation is expired. Please, try to register again.', BASEMENT_TEXTDOMAIN );
				} else if ( $transient[ 'user_email' ] !== $email ) {
					$this->messages[ 'error' ][ self::$attr_prefix . '_account_activation_email_incorrect' ] = __( 'Sorry, email is not correct. Please, try to register again.', BASEMENT_TEXTDOMAIN );
				} 	else if ( email_exists( $transient[ 'user_email' ] ) ) {
					$this->messages[ 'error' ][ self::$attr_prefix . '_account_activation_email_exists' ] = __( 'Sorry, email is already exists. Please, try to register again with another email.', BASEMENT_TEXTDOMAIN );
				} else if ( username_exists( $transient[ 'user_login' ] ) ) {
					$this->messages[ 'error' ][ self::$attr_prefix . '_account_activation_username_exists' ] = __( 'Sorry, username is already exists. Please, try to register again with another username.', BASEMENT_TEXTDOMAIN );
				}
				if ( is_multisite() ) {
					$blogname = $GLOBALS['current_site']->site_name;
				} else {
					$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
				}
				$user_id = wp_create_user( $transient[ 'user_login' ], $transient[ 'user_password' ], $transient[ 'user_email' ] );
				apply_filters( self::$attr_prefix . '_after_user_creation', array( 
						'user_id' => 1,
						'transient_data' => $transient
					)
				);
				
				if ( isset( $transient[ 'meta' ] ) && is_array( $transient[ 'meta' ] ) ) {
					foreach ( $transient[ 'meta' ] as $meta_key => $meta_value ) {
						if ( $meta_key && is_scalar( $meta_key ) ) {
							update_user_meta( $user_id, $meta_key, $meta_value );
						}
					}
				}

				if ( $user_id instanceof WP_Error ) {
					$this->messages[ 'error' ][ self::$attr_prefix . '_account_activation_user_creation_error' ] = __( 'Sorry, something went wrong on user creation. Please, try to register again with another username.', BASEMENT_TEXTDOMAIN );
				} else {
					$this->messages[ 'success' ][ self::$attr_prefix . '_account_activated' ] = __( 'Your account is activated! Use your email or username and a password you\'ve entered', BASEMENT_TEXTDOMAIN );
					$hide_form = true;
				}
				delete_site_transient( $key );
			}
		}

		if ( isset( $_GET[ self::$attr_prefix . '_registration_email_failed' ] ) ) {
			$this->messages['error'][ self::$attr_prefix . '_registration_email_failed' ] = __( 'Sorry, something sent wrong and we can not complete registration. Please, try later.', BASEMENT_TEXTDOMAIN );
		} else if ( isset( $_GET[ self::$attr_prefix . '_registration_email_sent' ] ) ) {
			$this->messages['success'][ self::$attr_prefix . '_registration_email_sent' ] = __( 'Check you email to activate you account, please.', BASEMENT_TEXTDOMAIN );
			$hide_form = true;
		}

		extract( shortcode_atts(
			array(
				'redirect_to' => Basement_User::instance()->current_page_url()
			),
			$atts )
		);

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
		if ( !$hide_form ) {
			$markup .= '<div class="' . self::$attr_prefix . '_form_wrapper">';
			$markup .= '<form action="" method="POST" class="sign-in">';
			// User name input
			$markup .= apply_filters( self::$attr_prefix . '_before_user_login_input', '' );
			$markup .= '<div class="' . self::$attr_prefix . '_form_user_login_input_wrapper">';
			$markup .= '<label for="' . self::$attr_prefix . '_form_user_login">';
			$markup .= __( 'User name', BASEMENT_TEXTDOMAIN );
			$markup .= '<input type="text" 
							name="' . self::$attr_prefix . '_form[user_login]"
							id="' . self::$attr_prefix . '_form_user_login" 
							value="' . $this->form_data['user_login'] . '" />';
			$markup .= '</div>';
			// User email input
			$markup .= apply_filters( self::$attr_prefix . '_before_user_email_input', '' );
			$markup .= '<div class="' . self::$attr_prefix . '_form_user_email_input_wrapper">';
			$markup .= '<label for="' . self::$attr_prefix . '_form_user_email">';
			$markup .= __( 'Email', BASEMENT_TEXTDOMAIN );
			$markup .= '<input type="text" 
							name="' . self::$attr_prefix . '_form[user_email]"
							id="' . self::$attr_prefix . '_form_user_email"  
							value="' . $this->form_data['user_email'] . '" />';
			$markup .= '</div>';
			// Submit button
			$markup .= apply_filters( self::$attr_prefix . '_before_submit_input', '' );
			$markup .= '<div class="' . self::$attr_prefix . '_form_submit_input_wrapper">';
			$markup .= '<input type="submit" 
							name="submit" 
							value="' . __( 'Register', BASEMENT_TEXTDOMAIN ) .'" />';
			$markup .= '</div>';
			// Hidden fields
			$markup .= '<input type="hidden" 
					name="' . self::$attr_prefix . '_form[redirect_to]" 
					value="' . $redirect_to . '" />';
			$markup .= wp_nonce_field( self::$attr_prefix . '_register_nonce' , self::$attr_prefix . '_form[nonce]', true, false );
			$markup .= apply_filters( self::$attr_prefix . '_before_form_close', '' );
			$markup .= '</form>';
			$markup .= '</div>';
		}
		return $markup;
	}

	public function create_registration_transient() {
		$form_name = self::$attr_prefix . '_form';
		if ( 'POST' != $_SERVER[ 'REQUEST_METHOD' ] ||
				empty( $_POST[ $form_name ] ) ||
				!is_array( $_POST[ $form_name ] ) ) {
			return;
		}

		$filter_result = apply_filters( self::$attr_prefix . '_post', $_POST );
		$_POST = empty( $filter_result[ 'post_data' ] ) ? $_POST : $filter_result[ 'post_data' ] ;
		$filter_error = !empty( $filter_result[ 'error' ] );

		if ( $filter_error ) {
			$this->messages[ 'error' ][ self::$attr_prefix . '_error_from_filter' ] = __( 'Error from filter.', BASEMENT_TEXTDOMAIN );
		}

		extract( $_POST[ $form_name ] );

		$this->form_data['user_login'] = $user_login;
		$this->form_data['user_email'] = $user_email;

		if ( $user_login_empty = empty( $user_login ) ) {
			$this->messages[ 'error' ][ self::$attr_prefix . '_user_login_empty' ] = __( 'Username is required.', BASEMENT_TEXTDOMAIN );
		}
		
		if ( $username_exists = (bool)username_exists( $user_login ) ) {
			$this->messages[ 'error' ][ self::$attr_prefix . '_username_exists' ] = __( 'Username exists.', BASEMENT_TEXTDOMAIN );
		}
		if ( $user_email_empty = empty( $user_email ) ) {
			$this->messages[ 'error' ][ self::$attr_prefix . '_user_email_empty' ] = __( 'Email is required.', BASEMENT_TEXTDOMAIN );
		} else if ( $not_email = !(bool)is_email( $user_email ) ) {
			$this->messages[ 'error' ][ self::$attr_prefix . '_not_email' ] = __( 'Enter valid email.', BASEMENT_TEXTDOMAIN );
		} else if ( $email_exists = (bool)email_exists( $user_email ) ) {
			$this->messages[ 'error' ][ self::$attr_prefix . '_email_exists' ] = __( 'Email exists.', BASEMENT_TEXTDOMAIN );
		}

		if ( $user_login_empty || $user_email_empty ||
				$username_exists || $not_email || $email_exists ||
				$filter_error ) {
			return;
		}

		$password = wp_generate_password( 20, false );
		$activation_key = md5( time() . $user_login . $user_email );
		
		$activate_account_page_url = add_query_arg(
			array(
				'key' => $activation_key,
				'email' => $user_email
			),
			Basement_User::instance()->current_page_url( true )
		);

		if ( is_multisite() ) {
			$blogname = $GLOBALS['current_site']->site_name;
		} else {
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		}

		$message = sprintf(__('You have registered to "%s" website', BASEMENT_TEXTDOMAIN ), '<a href="' . network_home_url( '/' ) . '">' . $blogname . '</a>' ) . "<br><br>";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.', BASEMENT_TEXTDOMAIN ) . "<br><br>";
		$message .= __( 'Username: your email or login name you have entered', BASEMENT_TEXTDOMAIN ) . "<br>";
		$message .= sprintf(__('Password: %s', BASEMENT_TEXTDOMAIN ), $password) . "<br><br>";
		$message .= __( 'To activate your account, visit the following address:', BASEMENT_TEXTDOMAIN ) . "<br><br>";
		$message .= '<a href="' . $activate_account_page_url . '">' . $activate_account_page_url . '</a><br>';

		$title = sprintf( __('[%s] Password Reset', BASEMENT_TEXTDOMAIN ), $blogname );

		if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message, 'Content-type: text/html' ) ) {
			wp_redirect( add_query_arg( self::$attr_prefix . '_registration_email_failed', 'true', Basement_User::instance()->current_page_url( true ) ) );
			exit;
		}

		$value = array(
			'user_login' => $user_login,
			'user_email' => $user_email,
			'user_password' => $password
		);

		$transient_filter_result = apply_filters( self::$attr_prefix . '_transient', $_POST[ $form_name ] );

		if ( is_array( $transient_filter_result ) ) {
			$value = array_merge( $transient_filter_result, $value );
		}

		set_site_transient( $activation_key, $value, 3600 );

		wp_redirect( add_query_arg( self::$attr_prefix . '_registration_email_sent', 'true', Basement_User::instance()->current_page_url( true ) ) );
		exit;
	}

}

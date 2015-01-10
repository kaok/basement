<?php
defined('ABSPATH') or die();

class Basement_User_Password {
	private static $shortcodes_added = false;
	private static $instance = null;
	public static $textdomain = 'basement';
	public static $attr_prefix = 'basement_user_password';

	public function __construct() {
		if ( !is_admin() ) {
			add_action( 'init', array( &$this, 'retrieve_password' ) );
			add_action( 'init', array( &$this, 'reset_password' ) );
			add_action( 'init', array( &$this, 'add_shortcodes' ) );
		}
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new Basement_User_Password();
		}
		return self::$instance;
	}

	public function add_shortcodes() {
		if ( self::$shortcodes_added ) {
			return;
		}
		add_shortcode( 'basement_lost_password_form', array( &$this, 'render_lost_password_form' ) );
		add_shortcode( 'basement_reset_password_form', array( &$this, 'render_reset_password_form' ) );
		self::$shortcodes_added = true;
	}

	public function render_lost_password_form( $atts ) {
		if (!is_user_logged_in() ) {
			$messages = array(
				'error' => array(
					self::$attr_prefix . '_lost_password_email_failed' => __( 'The e-mail could not be sent.', BASEMENT_THEME_TEXTDOMAIN ) . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.', BASEMENT_THEME_TEXTDOMAIN ),
					self::$attr_prefix . '_lost_password_user_not_found' => __( 'Sorry, the user with login or email you provided wasn\'t found.', BASEMENT_THEME_TEXTDOMAIN ),
					self::$attr_prefix . '_lost_password_invalid_nonce' => __( 'Sorry, your request didn\'t pass a security check.', BASEMENT_THEME_TEXTDOMAIN ),
					self::$attr_prefix . '_lost_password_reset_not_allowed' => __( 'Sorry, it is not allowed to reset password.', BASEMENT_THEME_TEXTDOMAIN ),
					self::$attr_prefix . '_lost_password_undefined_error' => __( 'Sorry, undefined error occured', BASEMENT_THEME_TEXTDOMAIN )
				),
				'success' => array(
					self::$attr_prefix . '_lost_password_email_sent' => __( 'To reset your password use the link from letter sent to your email', BASEMENT_THEME_TEXTDOMAIN )
				)
			);
			foreach ( $messages as $type => $type_messages ) {
				foreach ( $type_messages as $key => $message ) {
					if ( !isset( $_GET[ $key ]) ) {
						unset( $messages[$type][ $key ] );
					}
				}
			}
			extract( shortcode_atts(
				array(
					'new_password_page_url' => ''
				),
				$atts )
			);
			$hide_form = isset( $_GET[ self::$attr_prefix . '_lost_password_email_sent' ] );

			$markup = '';
			foreach ( $messages as $type => $type_messages ) {
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
				$markup .= '<form name="' . self::$attr_prefix . '_lost_password_form"
								id="' . self::$attr_prefix . '_form"
								action="' . esc_url( Basement_User::instance()->current_page_url() ) .'"
								method="POST">';
				$markup .= '<div>';
				$markup .= '<label for="' . self::$attr_prefix . '_lost_password_form_login" >' . __( 'Username or E-mail:', BASEMENT_THEME_TEXTDOMAIN ) . '</label>';
				$markup .= '<input type="text"
									name="' . self::$attr_prefix . '_lost_password_form[login]"
									id="' . self::$attr_prefix . '_lost_password_form_login"
									size="20" />';
				$markup .= '</div>';
				$markup .= '<div>';
				if ( $new_password_page_url = Basement_User::instance()->sanitize_redirect( $new_password_page_url )) {
					$markup .= '<input type="hidden"
									name="' . self::$attr_prefix . '_lost_password_form[new_password_page_url]"
									value="' . esc_attr( $new_password_page_url ) . '" />';
				}
				$markup .= '<input type="submit"
								name="wp-submit"
								id="wp-submit"
								value="' . esc_attr_x( 'Get New Password', BASEMENT_THEME_TEXTDOMAIN ) . '" />';
				$markup .= '</div>';
				$markup .= wp_nonce_field( self::$attr_prefix . '_lost_password_nonce' , self::$attr_prefix . '_lost_password_form[nonce]', true, false );
				$markup .= '</form>';
			}
			return $markup;
		}
		return '';
	}

	public function render_reset_password_form( $atts ) {
		$user = null;
		$activation_key = null;
		$hide_form = false;
		$messages = array(
			'error' => array(
				self::$attr_prefix . '_reset_password_invalid_link' => __( 'Sorry, the link data is broken. Try to request new password reset link, please.', BASEMENT_THEME_TEXTDOMAIN ),
				self::$attr_prefix . '_reset_password_empty_passwords' => __( 'Fill poth password fields, please.', BASEMENT_THEME_TEXTDOMAIN ),
				self::$attr_prefix . '_reset_password_different_passwords' => __( 'Passwords you\'ve entered are not identical.', BASEMENT_THEME_TEXTDOMAIN )
			),
			'success' => array(
				self::$attr_prefix . '_reset_password_successful' => __( 'Your password is successfully changed.', BASEMENT_THEME_TEXTDOMAIN  )
			)
		);

		foreach ( $messages as $type => $type_messages ) {
			foreach ( $type_messages as $key => $message ) {
				if ( !isset( $_GET[ $key ]) ) {
					unset( $messages[$type][ $key ] );
				}
			}
		}

		if ( !isset( $_GET[ 'hide_reset_password_form' ] ) ) {
			if ( isset( $_GET[ 'login' ] ) ) {
				$user_login = $_GET[ 'login' ];
				$user = get_user_by( 'login', $user_login );
			}

			if ( isset( $_GET[ 'key' ] ) ) {
				$activation_key = urldecode( $_GET[ 'key' ] );
			}

			if ( !$user || !$activation_key || $activation_key != md5( $user->data->user_activation_key ) ) {
				$hide_form = true;
				$messages['error'][ self::$attr_prefix . '_reset_password_invalid_link' ] = __( 'Sorry, the link data is broken. Try to request new password reset link, please.', BASEMENT_THEME_TEXTDOMAIN );
			}
		} else {
			$hide_form = true;
		}

		$markup = '';
		foreach ( $messages as $type => $type_messages ) {
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
			$markup .= '<form name="' . self::$attr_prefix . '_reset_password_form"
							id="' . self::$attr_prefix . '_form"
							action="' . esc_url( Basement_User::instance()->current_page_url() ) .'"
							method="POST">';
			$markup .= '<div>';
			$markup .= '<label for="' . self::$attr_prefix . '_reset_password_form_password" >' . __( 'New password:', BASEMENT_THEME_TEXTDOMAIN ) . '</label>';
			$markup .= '<input type="password"
								name="' . self::$attr_prefix . '_reset_password_form[password]"
								id="' . self::$attr_prefix . '_reset_password_form_password"
								size="20" />';
			$markup .= '</div>';
			$markup .= '<div>';
			$markup .= '<label for="' . self::$attr_prefix . '_reset_password_form_password_2" >' . __( 'Repeat password:', BASEMENT_THEME_TEXTDOMAIN ) . '</label>';
			$markup .= '<input type="password"
								name="' . self::$attr_prefix . '_reset_password_form[password_2]"
								id="' . self::$attr_prefix . '_reset_password_form_password_2"
								size="20" />';
			$markup .= '</div>';
			$markup .= '<div>';
			$markup .= '<input type="hidden"
								name="' . self::$attr_prefix . '_reset_password_form[key]"
								value="' . $activation_key . '" />';
			$markup .= '<input type="hidden"
								name="' . self::$attr_prefix . '_reset_password_form[user_login]"
								value="' . $user_login . '" />';
			$markup .= '<input type="submit"
								name="wp-submit"
								id="wp-submit"
								value="' . esc_attr_x( 'Set New Password', BASEMENT_THEME_TEXTDOMAIN ) . '" />';
			$markup .= '</div>';
			$markup .= wp_nonce_field( self::$attr_prefix . '_reset_password_form' , self::$attr_prefix . '_reset_password_form[nonce]', true, false );
			$markup .= '</form>';
		}
		return $markup;
	}

	public function retrieve_password() {
		if ( 'POST' != $_SERVER[ 'REQUEST_METHOD' ] ||
				empty( $_POST[ self::$attr_prefix . '_lost_password_form' ] ) ||
				!is_array( $_POST[ self::$attr_prefix . '_lost_password_form' ] ) ) {
			return;
		}

		extract( $_POST[ self::$attr_prefix . '_lost_password_form' ] );

		if ( !wp_verify_nonce( $nonce, self::$attr_prefix . '_lost_password_nonce' ) ) {
			wp_redirect( add_query_arg( self::$attr_prefix . '_lost_password_invalid_nonce', 'true', Basement_User::instance()->current_page_url( true ) ) );
			exit;
		}

		if ( ( is_email( $login ) && $user_id = email_exists( $login ) ) || $user_id = username_exists( $login ) ) {
			global $wpdb, $wp_hasher;

			$user = get_user_by( 'id', $user_id);
			$user_login = $user->data->user_login;
			$user_email = $user->data->user_email;
			do_action( 'retrieve_password', $user_login );

			// Filter whether to allow a password to be reset.
			$allow_reset = apply_filters( 'allow_password_reset', true, $user->data->ID );
			if ( ! $allow_reset ) {
				wp_redirect( add_query_arg( self::$attr_prefix . '_lost_password_reset_not_allowed', 'true', Basement_User::instance()->current_page_url( true ) ) );
				exit;
			} else if ( is_wp_error($allow_reset) ) {
				wp_redirect( add_query_arg( self::$attr_prefix . '_lost_password_undefined_error', 'true', Basement_User::instance()->current_page_url( true ) ) );
				exit;
			}

			// Generate something random for a password reset key.
			$key = wp_generate_password( 20, false );

			do_action( 'retrieve_password_key', $user_login, $key );

			// Now insert the key, hashed, into the DB.
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . WPINC . '/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}

			$hashed = $wp_hasher->HashPassword( $key );

			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

			$new_password_page_url = Basement_User::instance()->sanitize_redirect( $new_password_page_url );
			if ( !$new_password_page_url ) {
				$new_password_page_url = add_query_arg( array(
					'key' => rawurlencode( $hashed ),
					'login' => rawurlencode($user_login)
				), network_site_url('wp-login.php?action=rp', 'login') );
			} else {
				$new_password_page_url = add_query_arg( array(
					'key' => rawurlencode( md5( $hashed ) ),
					'login' => rawurlencode($user_login)
				), $new_password_page_url );
			}

			$message = __('Someone requested that the password be reset for the following account:', BASEMENT_THEME_TEXTDOMAIN ) . "<br><br>";
			$message .= network_home_url( '/' ) . "<br><br>";
			$message .= sprintf(__('Username: %s', BASEMENT_THEME_TEXTDOMAIN ), $user_login) . "<br><br>";
			$message .= __('If this was a mistake, just ignore this email and nothing will happen.' , BASEMENT_THEME_TEXTDOMAIN) . "<br><br>";
			$message .= __('To reset your password, visit the following address:', BASEMENT_THEME_TEXTDOMAIN ) . "<br><br>";
			$message .= '<a href="' . $new_password_page_url . '">' . $new_password_page_url . '</a><br>';

			if ( is_multisite() ) {
				$blogname = $GLOBALS['current_site']->site_name;
			} else {
				$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			}

			$title = sprintf( __( '[%s] Password Reset', BASEMENT_THEME_TEXTDOMAIN ), $blogname );
			/**
			 * Filter the subject of the password reset email.
			 *
			 * @since 2.8.0
			 *
			 * @param string $title Default email title.
			 */
			$title = apply_filters( 'retrieve_password_title', $title );
			/**
			 * Filter the message body of the password reset mail.
			 *
			 * @since 2.8.0
			 *
			 * @param string $message Default mail message.
			 * @param string $key     The activation key.
			 */
			$message = apply_filters( 'retrieve_password_message', $message, $key );
			if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message, 'Content-type: text/html' ) ) {
				wp_redirect( add_query_arg( self::$attr_prefix . '_lost_password_email_failed', 'true', Basement_User::instance()->current_page_url( true ) ) );
				exit;
			}
			wp_redirect( add_query_arg( self::$attr_prefix . '_lost_password_email_sent', 'true', Basement_User::instance()->current_page_url( true ) ) );
			exit;
		}

		wp_redirect( add_query_arg( self::$attr_prefix . '_lost_password_user_not_found', 'true', Basement_User::instance()->current_page_url( true ) ) );
		exit;
	}



	public function reset_password() {
		if ( 'POST' != $_SERVER[ 'REQUEST_METHOD' ] ||
				empty( $_POST[ self::$attr_prefix . '_reset_password_form' ] ) ||
				!is_array( $_POST[ self::$attr_prefix . '_reset_password_form' ] ) ) {
			return;
		}

		extract( $_POST[ self::$attr_prefix . '_reset_password_form' ] );

		if ( !$user_login ||
				!$key ||
				!( $user = get_user_by( 'login', $user_login ) ) ||
				$key != md5( $user->data->user_activation_key ) ) {
			$error_argument = '_reset_password_invalid_link';
		}

		if ( empty( $password ) || empty( $password_2 ) ) {
			$error_argument = '_reset_password_empty_passwords';
		}

		if ( $password !== $password_2 ) {
			$error_argument = '_reset_password_different_passwords';
		}

		if ( !empty( $error_argument ) ) {
			wp_redirect( add_query_arg( array(
					'key' => $key,
					'login' => $user_login,
					self::$attr_prefix . $error_argument => 'true'
				), 
				Basement_User::instance()->current_page_url( true ) ) );
			exit;
		}

		wp_update_user( array( 
				'ID' => $user->data->ID, 
				'user_pass' => esc_html( $password )
			)
		);

		global $wpdb;

		$wpdb->update( $wpdb->users, 
						array( 'user_activation_key' => '' ), 
						array( 'ID' => $user->data->ID ) 
		);

		wp_redirect( add_query_arg( array(
				self::$attr_prefix . '_reset_password_successful' => 'true',
				'hide_reset_password_form' => 'true'
			), Basement_User::instance()->current_page_url( true ) ) );
		exit;
	}

}

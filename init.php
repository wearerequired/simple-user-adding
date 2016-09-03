<?php
/**
 * @package Simple_User_Adding
 */

/**
 * Returns the Simple User Adding controller instance.
 *
 * @since 2.0.0
 *
 * @return \Required\Simple_User_Adding\Controller
 */
function simple_user_adding() {
	static $controller = null;

	if ( null === $controller ) {
		$controller = new \Required\Simple_User_Adding\Controller( __FILE__ );
	}

	return $controller;
}

// Initialize the plugin.
add_action( 'plugins_loaded', [ simple_user_adding(), 'add_hooks' ] );

if ( ! function_exists( 'wp_new_user_notification' ) ) :
	simple_user_adding()->can_modify_email( true );

	global $wp_version;

	if ( version_compare( $wp_version, '4.3', '<' ) ) {
		/**
		 * Email login credentials to a newly-registered user.
		 *
		 * A new user registration notification is also sent to admin email.
		 *
		 * @since 2.0.0
		 *
		 * @param int    $user_id        User ID.
		 * @param string $plaintext_pass Optional. The user's plaintext password. Default empty.
		 */
		function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {
			$user = get_userdata( $user_id );

			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			if ( class_exists( 'WP_Digest_Queue' ) ) {
				WP_Digest_Queue::add( get_option( 'admin_email' ), 'new_user_notification', $user_id );
			} else {
				$message = sprintf( __( 'New user registration on your site %s:', 'simple-user-adding' ), $blogname ) . "\r\n\r\n";
				$message .= sprintf( __( 'Username: %s', 'simple-user-adding' ), $user->user_login ) . "\r\n\r\n";
				$message .= sprintf( __( 'Email: %s', 'simple-user-adding' ), $user->user_email ) . "\r\n";

				@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration', 'simple-user-adding' ), $blogname ), $message );
			}

			if ( empty( $plaintext_pass ) ) {
				return;
			}

			$message = sprintf( __( 'Username: %s', 'simple-user-adding' ), $user->user_login ) . "\r\n";
			$message .= sprintf( __( 'Password: %s', 'simple-user-adding' ), $plaintext_pass ) . "\r\n";
			$message .= wp_login_url() . "\r\n";

			$message = apply_filters( 'sua_notification_message', $message, $user );

			wp_mail( $user->user_email, sprintf( __( '[%s] Your username and password', 'simple-user-adding' ), $blogname ), $message );
		}
	} else {
		/**
		 * Email login credentials to a newly-registered user.
		 *
		 * A new user registration notification is also sent to admin email.
		 *
		 * @since 2.0.0
		 * @since 4.3.0 The `$plaintext_pass` parameter was changed to `$notify`.
		 *
		 * @param int    $user_id User ID.
		 * @param string $notify  Optional. Type of notification that should happen. Accepts 'admin' or an empty
		 *                        string (admin only), or 'both' (admin and user). The empty string value was kept
		 *                        for backward-compatibility purposes with the renamed parameter. Default empty.
		 */
		function wp_new_user_notification( $user_id, $notify = '' ) {
			global $wpdb;
			$user = get_userdata( $user_id );

			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

			if ( class_exists( 'WP_Digest_Queue' ) ) {
				WP_Digest_Queue::add( get_option( 'admin_email' ), 'new_user_notification', $user_id );
			} else {
				$message = sprintf( __( 'New user registration on your site %s:', 'simple-user-adding' ), $blogname ) . "\r\n\r\n";
				$message .= sprintf( __( 'Username: %s', 'simple-user-adding' ), $user->user_login ) . "\r\n\r\n";
				$message .= sprintf( __( 'Email: %s', 'simple-user-adding' ), $user->user_email ) . "\r\n";

				@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration', 'simple-user-adding' ), $blogname ), $message );
			}

			if ( 'admin' === $notify || empty( $notify ) ) {
				return;
			}

			// Generate something random for a password reset key.
			$key = wp_generate_password( 20, false );

			/** This action is documented in wp-login.php */
			do_action( 'retrieve_password_key', $user->user_login, $key );

			// Now insert the key, hashed, into the DB.
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . WPINC . '/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}
			$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

			$message = sprintf( __( 'Username: %s', 'simple-user-adding' ), $user->user_login ) . "\r\n\r\n";
			$message .= __( 'To set your password, visit the following address:', 'simple-user-adding' ) . "\r\n\r\n";
			$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n\r\n";

			$message .= wp_login_url() . "\r\n";

			$message = apply_filters( 'sua_notification_message', $message, $user );

			wp_mail( $user->user_email, sprintf( __( '[%s] Your username and password info', 'simple-user-adding' ), $blogname ), $message );
		}
	}
endif;

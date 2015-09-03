<?php
/**
 * Simple User Adding
 *
 * @package Simple_User_Adding
 */

defined( 'WPINC' ) or die;

/**
 * Simple_User_Adding_Plugin class.
 */
class Simple_User_Adding_Plugin extends WP_Stack_Plugin2 {
	/**
	 * Plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Can we overwrite the pluggable wp_new_user_notification() function?
	 *
	 * @var bool
	 */
	public static $can_modify_email = false;

	/**
	 * Instance of this class.
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Custom notification message being sent to the newly added user.
	 *
	 * @var string
	 */
	protected $notification_message = '';

	/**
	 * Constructs the object, hooks in to `plugins_loaded`.
	 */
	protected function __construct() {
		$this->hook( 'plugins_loaded', 'add_hooks' );
	}

	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
		$this->hook( 'init' );

		// Add the options page with the custom admin footer text.
		$this->hook( 'admin_menu' );
		$this->hook( 'admin_footer_text' );

		// Add help tab.
		$this->hook( 'load-users_page_simple-user-adding', 'admin_help_tab' );

		// Load admin style sheet and JavaScript.
		$this->hook( 'admin_enqueue_scripts' );

		// Handle form submissions.
		$this->hook( 'admin_post_simple_user_adding', 'create_user' );
	}

	/**
	 * Initializes the plugin, registers textdomain, etc.
	 */
	public function init() {
		$this->load_textdomain( 'simple-user-adding', '/languages' );
	}

	/**
	 * Add a new admin menu item.
	 */
	public function admin_menu() {
		add_users_page(
			__( 'Add New User', 'simple-user-adding' ),
			__( 'Add New', 'simple-user-adding' ),
			'create_users',
			'simple-user-adding',
			array( $this, 'display_admin_page' )
		);
		remove_submenu_page( 'users.php', 'user-new.php' );
	}

	/**
	 * Output the content for the new admin page.
	 */
	public function display_admin_page() {
		$this->include_file( 'views/simple-user-adding-form.php' );
	}

	/**
	 * Add some text in the footer of our admin page.
	 *
	 * @return string|void
	 */
	public function admin_footer_text() {
		$screen = get_current_screen();
		if ( 'users_page_simple-user-adding' !== $screen->id ) {
			return;
		}
		$text = sprintf( __( '%s is brought to you by %s. We &hearts; WordPress.', 'simple-user-adding' ), 'Simple User Adding', '<a href="http://required.ch">required+</a>' );
		$text .= ' <a href="' . admin_url( 'user-new.php' ) . '">' . __( 'Looking for the original Add User form?', 'simple-user-adding' ) . '</a>';

		return $text;
	}

	/**
	 * Add help text to our admin page.
	 */
	public function admin_help_tab() {
		$screen = get_current_screen();
		if ( 'users_page_simple-user-adding' !== $screen->id ) {
			return;
		}

		$help = '<p>' . __( 'To add a new user to your site, fill in the form on this screen and click the Add New User button at the bottom.', 'simple-user-adding' ) . '</p>';

		if ( is_multisite() ) {
			$help .= '<p>' . __( 'Because this is a multisite installation, you may add accounts that already exist on the Network by specifying a username or email, and defining a role. For more options, you have to be a Network Administrator and use the hover link under an existing user&#8217;s name to Edit the user profile under Network Admin > All Users.', 'simple-user-adding' ) . '</p>';
		}

		$help .= '<p>' . __( 'New users will receive an email letting them know they&#8217;ve been added as a user for your site. This email will also contain their automatically generated password.', 'simple-user-adding' ) . '</p>';
		$help .= '<p>' . __( 'Remember to click the Add New User button at the bottom of this screen when you are finished.', 'simple-user-adding' ) . '</p>';

		$screen->add_help_tab( array(
			'id'      => 'overview',
			'title'   => __( 'Overview', 'simple-user-adding' ),
			'content' => $help,
		) );

		$screen->add_help_tab( array(
			'id'      => 'user-roles',
			'title'   => __( 'User Roles', 'simple-user-adding' ),
			'content' => '<p>' . __( 'Here is a basic overview of the different user roles and the permissions associated with each one:', 'simple-user-adding' ) . '</p>' .
			             '<ul>' .
			             '<li>' . __( 'Subscribers can read comments/comment/receive newsletters, etc. but cannot create regular site content.', 'simple-user-adding' ) . '</li>' .
			             '<li>' . __( 'Contributors can write and manage their posts but not publish posts or upload media files.', 'simple-user-adding' ) . '</li>' .
			             '<li>' . __( 'Authors can publish and manage their own posts, and are able to upload files.', 'simple-user-adding' ) . '</li>' .
			             '<li>' . __( 'Editors can publish posts, manage posts as well as manage other people&#8217;s posts, etc.', 'simple-user-adding' ) . '</li>' .
			             '<li>' . __( 'Administrators have access to all the administration features.', 'simple-user-adding' ) . '</li>' .
			             '</ul>',
		) );

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'simple-user-adding' ) . '</strong></p>' .
			'<p>' . __( '<a href="http://codex.wordpress.org/Users_Add_New_Screen" target="_blank">Documentation on Adding New Users</a>', 'simple-user-adding' ) . '</p>' .
			'<p>' . __( '<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>', 'simple-user-adding' ) . '</p>'
		);
	}

	/**
	 * Enqueue scripts and styles on our admin page.
	 */
	public function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( 'users_page_simple-user-adding' !== $screen->id ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style( 'sua-admin-styles', $this->get_url() . 'css/simple-user-adding' . $suffix . '.css', array(), self::VERSION );
		wp_enqueue_script( 'sua-admin-script', $this->get_url() . 'js/simple-user-adding' . $suffix . '.js', array( 'jquery' ), self::VERSION );
	}

	/**
	 * Handle form submissions.
	 */
	public function create_user() {
		/**
		 * This checks for the correct referrer and the nonce.
		 * On failure, the function dies after calling the wp_nonce_ays() function.
		 */
		check_admin_referer( 'simple-user-adding', 'simple_user_adding_nonce' );

		if ( ! current_user_can( 'create_users' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?', 'simple-user-adding' ), 403 );
		}

		// Check required fields.
		if ( ! isset( $_POST['user_login'] ) || empty( $_POST['user_login'] )
		     || ! isset( $_POST['email'] ) || empty( $_POST['email'] )
		) {
			wp_redirect( add_query_arg(
				array( 'message' => 'required_fields_missing' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		// Check if the email address is valid.
		$user_email = wp_unslash( $_POST['email'] );
		if ( ! is_email( $user_email ) ) {
			wp_redirect( add_query_arg(
				array( 'message' => 'enter_email' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		// Check if a user with this email address already exists.
		if ( get_user_by( 'email', $user_email ) ) {
			wp_redirect( add_query_arg(
				array( 'message' => 'user_email_exists' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		// Check if a user with this login already exists.
		if ( get_user_by( 'login', wp_unslash( $_POST['user_login'] ) ) ) {
			wp_redirect( add_query_arg(
				array( 'message' => 'user_name_exists' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		// Set passwords for use in edit_user().
		$_POST['pass2'] = $_POST['pass1'] = wp_generate_password( 24 );

		// Set the flag to send a notification mail to the user.
		$_POST['send_password'] = true;

		// Filter the user notification when there's a custom message.
		if ( self::$can_modify_email && isset( $_POST['notification_msg'] ) && ! empty( $_POST['notification_msg'] ) ) {
			$this->notification_message = wp_kses( $_POST['notification_msg'], array() );

			add_filter( 'sua_notification_message', array( $this, 'modify_notification_message' ) );
		}

		// This creates (or updates) a user.
		$user_id = edit_user();
		if ( is_wp_error( $user_id ) ) {
			wp_redirect( add_query_arg(
				array( 'message' => 'failure' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		wp_redirect( add_query_arg(
			array( 'message' => 'success' ),
			admin_url( 'users.php?page=simple-user-adding' )
		) );
		die();
	}

	/**
	 * Filter the new user notification message.
	 *
	 * @param string $message The notification message.
	 *
	 * @return string
	 */
	public function modify_notification_message( $message ) {
		if ( ! empty( $this->notification_message ) ) {
			$message = $this->notification_message . "\r\n\r\n" . $message;
		}

		return $message;
	}
}

if ( ! function_exists( 'wp_new_user_notification' ) ) :
	Simple_User_Adding_Plugin::$can_modify_email = true;

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

			$message = sprintf( __( 'New user registration on your site %s:', 'simple-user-adding' ), $blogname ) . "\r\n\r\n";
			$message .= sprintf( __( 'Username: %s', 'simple-user-adding' ), $user->user_login ) . "\r\n\r\n";
			$message .= sprintf( __( 'Email: %s', 'simple-user-adding' ), $user->user_email ) . "\r\n";

			@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration', 'simple-user-adding' ), $blogname ), $message );

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

			$message = sprintf( __( 'New user registration on your site %s:', 'simple-user-adding' ), $blogname ) . "\r\n\r\n";
			$message .= sprintf( __( 'Username: %s', 'simple-user-adding' ), $user->user_login ) . "\r\n\r\n";
			$message .= sprintf( __( 'Email: %s', 'simple-user-adding' ), $user->user_email ) . "\r\n";

			@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration', 'simple-user-adding' ), $blogname ), $message );

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

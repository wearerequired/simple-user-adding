<?php
/**
 * Simple User Adding
 *
 * @package Simple_User_Adding
 */

namespace Required\Simple_User_Adding;

/**
 * Simple_User_Adding_Plugin class.
 */
class Controller {
	/**
	 * Plugin version.
	 */
	const VERSION = '1.1.1';

	/**
	 * Can we overwrite the pluggable wp_new_user_notification() function?
	 *
	 * @var bool
	 */
	protected $can_modify_email = false;

	/**
	 * Custom notification message being sent to the newly added user.
	 *
	 * @var string
	 */
	protected $notification_message = '';

	/**
	 * The full path and filename of the main plugin file.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Constructs the object, hooks in to {@see 'plugins_loaded'}.
	 *
	 * @param string $file Full path to the main plugin file.
	 */
	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Add the options page with the custom admin footer text.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_footer_text', array( $this, 'admin_footer_text' ) );

		// Add help tab.
		add_action( 'load-users_page_simple-user-adding', array( $this, 'admin_help_tab' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Handle form submissions.
		add_action( 'admin_post_simple_user_adding', array( $this, 'create_user' ) );
	}

	/**
	 * Returns the URL to the plugin directory (with trailing slash).
	 *
	 * @return string The URL to the plugin directory.
	 */
	public function get_url() {
		return plugin_dir_url( $this->file );
	}

	/**
	 * Returns the absolute path to the plugin directory (with trailing slash).
	 *
	 * @return string The absolute path to the plugin directory.
	 */
	public function get_path() {
		return plugin_dir_path( $this->file );
	}

	/**
	 * Returns the basename of the plugin.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_basename() {
		return plugin_basename( $this->file );
	}

	/**
	 * Initializes the plugin, registers textdomain, etc.
	 *
	 * @return bool True if the textdomain was loaded successfully, false otherwise.
	 */
	public function load_textdomain() {
		return load_plugin_textdomain( 'simple-user-adding', false, $this->get_path() . 'languages' );
	}

	/**
	 * Whether the plugin can modify the emails being sent or not.
	 *
	 * @param bool $possible Optional. The value to set. Default null.
	 * @return bool Whether the plugin can modify the emails being sent or not.
	 */
	public function can_modify_email( $possible = null ) {
		if ( null !== $possible ) {
			$this->can_modify_email = (bool) $possible;
		}

		return $this->can_modify_email;
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
		include $this->get_path() . 'views/simple-user-adding-form.php';
	}

	/**
	 * Add some text in the footer of our admin page.
	 *
	 * @return string
	 */
	public function admin_footer_text() {
		$screen = get_current_screen();
		if ( 'users_page_simple-user-adding' !== $screen->id ) {
			return '';
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

		$dependencies = array( 'jquery' );

		if ( is_multisite() ) {
			$dependencies[] = 'user-suggest';
		}

		wp_enqueue_script( 'sua-admin-script', $this->get_url() . 'js/simple-user-adding' . $suffix . '.js', $dependencies, self::VERSION );
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
		if ( $this->can_modify_email && isset( $_POST['notification_msg'] ) && ! empty( $_POST['notification_msg'] ) ) {
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

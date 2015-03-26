<?php
/**
 * Simple User Adding
 *
 * @package   Simple_User_Adding
 * @author    Pascal Birchler <pascal@required.ch>
 * @license   GPL-2.0+
 * @link      https://github.com/wearerequired/user-feedback/
 * @copyright 2015 required gmbh
 *
 * @wordpress-plugin
 * Plugin Name: Simple User Adding
 * Plugin URI:  https://github.com/wearerequired/simple-user-adding
 * Description: This plugin makes adding users to your WordPress site easier than ever before.
 * Version:     1.0.0
 * Author:      required+
 * Author URI:  http://required.ch
 * Text Domain: simple-user-adding
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// Don't call this file directly
defined( 'ABSPATH' ) or die;

/**
 * Class User_Feedback
 */
final class Simple_User_Adding {

	const VERSION = '1.0.0';

	/**
	 * Add all hooks on init
	 */
	public static function init() {
		// Load plugin text domain
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( __CLASS__, 'add_plugin_admin_menu' ) );
		add_filter( 'admin_footer_text', array( __CLASS__, 'add_admin_footer' ) );

		// Add help tab
		add_action( 'load-users_page_simple-user-adding', array( __CLASS__, 'add_admin_help_tab' ) );

		// Handle form submissions
		add_action( 'admin_post_simple_user_adding', array( __CLASS__, 'create_user' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
	}

	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'simple-user-adding', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public static function add_plugin_admin_menu() {
		add_users_page(
			__( 'Add New User', 'simple-user-adding' ),
			__( 'Add New', 'simple-user-adding' ),
			'create_users',
			'simple-user-adding',
			array( __CLASS__, 'display_admin_page' )
		);

		remove_submenu_page( 'users.php', 'user-new.php' );
	}

	public static function display_admin_page() {
		include_once( plugin_dir_path( __FILE__ ) . '/includes/simple-user-adding-form.php' );
	}

	public static function add_admin_footer() {
		$screen = get_current_screen();
		if ( 'users_page_simple-user-adding' !== $screen->id ) {
			return;
		}

		$text = sprintf( __( '%s is brought to you by %s. We &hearts; WordPress.', 'simple-user-adding' ), 'Simple User Adding', '<a href="http://required.ch">required+</a>' );
		$text .= ' <a href="' . admin_url( 'user-new.php' ) . '">' . __( 'Looking for the original Add User form?', 'wp-widget-disable' ) . '</a>';

		return $text;
	}

	public static function add_admin_help_tab() {
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
			             '</ul>'
		) );

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'simple-user-adding' ) . '</strong></p>' .
			'<p>' . __( '<a href="http://codex.wordpress.org/Users_Add_New_Screen" target="_blank">Documentation on Adding New Users</a>', 'simple-user-adding' ) . '</p>' .
			'<p>' . __( '<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>', 'simple-user-adding' ) . '</p>'
		);
	}

	public static function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'users_page_simple-user-adding' !== $screen->id ) {
			return;
		}

		wp_enqueue_style( 'sua-admin-styles', plugins_url( 'css/simple-user-adding.css', __FILE__ ), array(), self::VERSION );
		wp_enqueue_script( 'sua-admin-script', plugins_url( 'js/simple-user-adding.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	public static function create_user() {
		/**
		 * This checks for the correct referrer and the nonce.
		 * On failure, the function dies after calling the wp_nonce_ays() function.
		 */
		check_admin_referer( 'simple-user-adding', 'simple_user_adding_nonce' );

		if ( ! current_user_can( 'create_users' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?', 'simple-user-adding' ), 403 );
		}

		// todo: process form

		// Check required fields
		if ( ! isset( $_POST['user_login'] ) || empty( $_POST['user_login'] )
		     || ! isset( $_POST['email'] ) || empty( $_POST['email'] )
		) {
			wp_redirect( add_query_arg(
				array( 'message' => 'required_fields_missing' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		// Check if the email address at least contains an @ sign
		$user_email = wp_unslash( $_POST['email'] );
		if ( false === strpos( $user_email, '@' ) ) {
			wp_redirect( add_query_arg(
				array( 'message' => 'enter_email' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		// Check if a user with this email address already exists
		if ( get_user_by( 'email', $user_email ) ) {
			wp_redirect( add_query_arg(
				array( 'message' => 'user_email_exists' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		// Check if a user with this login already exists
		if ( get_user_by( 'login', wp_unslash( $_POST['user_login'] ) ) ) {
			wp_redirect( add_query_arg(
				array( 'message' => 'user_name_exists' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		// Set passwords for use in edit_user()
		$_POST['pass2'] = $_POST['pass1'] = wp_generate_password( 24 );

		// Set the flag to send a notification mail to the user
		$_POST['send_password'] = true;

		// This creates (or updates) a user
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

}

add_action( 'plugins_loaded', array( 'Simple_User_Adding', 'init' ) );
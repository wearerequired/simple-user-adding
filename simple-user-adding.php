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
		// Add the options page and menu item.
		add_action( 'admin_menu', array( __CLASS__, 'add_plugin_admin_menu' ) );
		add_filter( 'admin_footer_text', array( __CLASS__, 'add_admin_footer' ) );

		// Handle form submissions
		add_action( 'admin_post_simple_user_adding', array( __CLASS__, 'create_user' ) );

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
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

	public static function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'users_page_simple-user-adding' !== $screen->id ) {
			return;
		}

		wp_enqueue_style( 'sua-admin-styles', plugins_url( 'css/simple-user-adding.css', __FILE__ ), array(), self::VERSION );
		wp_enqueue_script( 'sua-admin-script', plugins_url( 'js/simple-user-adding.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	public static function create_user() {
		if ( ! isset( $_POST['simple_user_adding_nonce'] )
		     || ! wp_verify_nonce( $_POST['simple_user_adding_nonce'], 'simple-user-adding' )
		) {
			wp_redirect( add_query_arg(
				array( 'message' => 'failure' ),
				admin_url( 'users.php?page=simple-user-adding' )
			) );
			die();
		}

		// todo: process form

		wp_redirect( add_query_arg(
			array( 'message' => 'success' ),
			admin_url( 'users.php?page=simple-user-adding' )
		) );
		die();
	}

}

add_action( 'plugins_loaded', array( 'Simple_User_Adding', 'init' ) );
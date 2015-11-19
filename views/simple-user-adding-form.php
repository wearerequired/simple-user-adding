<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   WP_Widget_Disable
 * @author    Silvan Hagen <silvan@required.ch>
 * @license   GPL-2.0+
 * @link      http://wp.required.ch/plugins/wp-widget-disable
 * @copyright 2015 required gmbh
 */
?>

<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<p><?php _e( 'Create a brand new user and add them to this site.', 'simple-user-adding' ); ?></p>

	<?php
	$message = array();
	if ( isset( $_GET['message'] ) ) {
		switch ( $_GET['message'] ) {
			case 'required_fields_missing':
				$message = array(
					'class' => 'notice notice-error is-dismissible',
					'text'  => __( 'Required fields missing.', 'simple-user-adding' )
				);
				break;
			case 'enter_email':
				$message = array(
					'class' => 'notice notice-error is-dismissible',
					'text'  => __( 'Please enter a valid email address.', 'simple-user-adding' )
				);
				break;
			case 'user_email_exists':
				$message = array(
					'class' => 'notice notice-error is-dismissible',
					'text'  => __( 'A user with this email address already exists.', 'simple-user-adding' )
				);
				break;
			case 'user_name_exists':
				$message = array(
					'class' => 'notice notice-error is-dismissible',
					'text'  => __( 'A user with this username already exists.', 'simple-user-adding' )
				);
				break;
			case 'success':
				$message = array(
					'class' => 'notice notice-success is-dismissible',
					'text'  => __( 'User successfully added.', 'simple-user-adding' )
				);
				break;
			case 'failure':
				$message = array(
					'class' => 'notice notice-success is-dismissible',
					'text'  => __( 'There was an error adding the user. Please try again.', 'simple-user-adding' )
				);
				break;
		}
	}

	if ( ! empty( $message ) ) {
		echo '<div id="message" class="' . esc_attr( $message['class'] ) . '"><p>' . esc_html( $message['text'] ) . '</p></div>';
	}
	?>

	<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" name="sua_createuser" id="sua_createuser" novalidate <?php
	/**
	 * Fires inside the adduser form tag.
	 *
	 * @since 1.0.0
	 */
	do_action( 'user_new_form_tag' );
	?>>
		<table class="form-table">
			<tr class="form-required">
				<th scope="row">
					<label for="user_login"><?php _e( 'Username', 'simple-user-adding' ); ?>
						<span class="description"><?php _e( '(required)', 'simple-user-adding' ); ?></span>
					</label>
				</th>
				<td>
					<input type="text" id="user_login" name="user_login" class="regular-text" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" />
				</td>
			</tr>
			<tr class="form-required">
				<th scope="row">
					<label for="email"><?php _e( 'E-mail', 'simple-user-adding' ); ?>
						<span class="description"><?php _e( '(required)', 'simple-user-adding' ); ?></span>
					</label>
				</th>
				<td>
					<input type="email" id="email" name="email" class="regular-text" aria-required="true" autocapitalize="none" autocorrect="off" />

					<div id="sua_email_note" class="hidden">
						<p>
							<?php printf( __( 'Is this %s?', 'simple-user-adding' ), '<span id="sua_email_name"></span>' ); ?>
							<a href="#" id="sua_email_note_insert"><?php _e( 'Insert name', 'simple-user-adding' ); ?></a>
						</p>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="role"><?php _e( 'Role', 'simple-user-adding' ); ?></label>
				</th>
				<td>
					<select name="role" id="role">
						<?php wp_dropdown_roles( get_option( 'default_role' ) ); ?>
					</select>
				</td>
			</tr>
			<tr class="additional hidden">
				<th scope="row">
					<label for="first_name"><?php _e( 'Name', 'simple-user-adding' ); ?></label>
				</th>
				<td>
					<input type="text" id="first_name" name="first_name" class="regular-text" placeholder="<?php esc_attr_e( 'First Name', 'simple-user-adding' ); ?>" />
					<input type="text" id="last_name" name="last_name" class="regular-text" placeholder="<?php esc_attr_e( 'Last Name', 'simple-user-adding' ); ?>" />
				</td>
			</tr>
			<tr class="additional hidden">
				<th scope="row"><label for="url"><?php _e( 'Website', 'simple-user-adding' ) ?></label></th>
				<td>
					<input name="url" type="url" id="url" class="regular-text code" />
				</td>
			</tr>
			<?php foreach ( wp_get_user_contact_methods() as $name => $desc ) : ?>
				<tr class="additional hidden">
					<th>
						<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( apply_filters( 'user_{$name}_label', $desc ) ); ?></label>
					</th>
					<td>
						<input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" class="regular-text" />
					</td>
				</tr>
			<?php endforeach ?>
			<?php if ( Simple_User_Adding_Plugin::$can_modify_email ) : ?>
				<tr class="additional hidden">
					<th scope="row"><label for="notification_msg"><?php _e( 'Message', 'simple-user-adding' ) ?></label>
					</th>
					<td>
						<textarea name="notification_msg" id="notification_msg" class="regular-text"></textarea>

						<p class="description"><?php _e( 'This text is shown to the user in the confirmation email they receive.', 'simple-user-adding' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th scope="row"></th>
				<td>
					<input type="button" id="sua_showmore" class="button-secondary" data-more="<?php _e( 'Show More', 'simple-user-adding' ); ?>" data-less="<?php _e( 'Show Less', 'simple-user-adding' ); ?>" value="<?php _e( 'Show More', 'simple-user-adding' ); ?>" />
				</td>
			</tr>
		</table>

		<?php
		/**
		 * Fires at the end of the new user form.
		 *
		 * Passes a contextual string to make both types of new user forms
		 * uniquely targetable. Contexts are 'add-existing-user' (Multisite),
		 * and 'add-new-user' (single site and network admin).
		 *
		 * @since 1.0.0
		 *
		 * @param string $type A contextual string specifying which type of new user form the hook follows.
		 */
		do_action( 'user_new_form', 'add-existing-user' );
		?>

		<input type="hidden" name="action" value="simple_user_adding">
		<?php wp_nonce_field( 'simple-user-adding', 'simple_user_adding_nonce' ); ?>
		<?php submit_button( __( 'Add New User', 'simple-user-adding' ), 'primary', 'submit' ); ?>
	</form>
</div>

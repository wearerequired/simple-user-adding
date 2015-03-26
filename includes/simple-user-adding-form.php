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
					'class' => 'error',
					'text'  => __( 'Required fields missing.', 'simple-user-adding' )
				);
				break;
			case 'enter_email':
				$message = array(
					'class' => 'error',
					'text'  => __( 'Please enter a valid email address.', 'simple-user-adding' )
				);
				break;
			case 'user_email_exists':
				$message = array(
					'class' => 'error',
					'text'  => __( 'A user with this email address already exists.', 'simple-user-adding' )
				);
				break;
			case 'user_name_exists':
				$message = array(
					'class' => 'error',
					'text'  => __( 'A user with this username already exists.', 'simple-user-adding' )
				);
				break;
			case 'success':
				$message = array(
					'class' => 'updated',
					'text'  => __( 'User successfully added.', 'simple-user-adding' )
				);
				break;
		}
	}

	if ( ! empty( $message ) ) {
		echo '<div id="message" class="' . esc_attr( $message['class'] ) . '"><p>' . esc_html( $message['text'] ) . '</p></div>';
	}
	?>

	<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" name="sua_createuser" id="sua_createuser" novalidate>
		<table class="form-table">
			<tr class="form-required">
				<th scope="row">
					<label for="sua_username"><?php _e( 'Username', 'simple-user-adding' ); ?>
						<span class="description"><?php _e( '(required)', 'simple-user-adding' ); ?></span>
					</label>
				</th>
				<td>
					<input type="text" id="sua_username" name="sua_username" class="regular-text" />
				</td>
			</tr>
			<tr class="form-required">
				<th scope="row">
					<label for="sua_email"><?php _e( 'E-mail', 'simple-user-adding' ); ?>
						<span class="description"><?php _e( '(required)', 'simple-user-adding' ); ?></span>
					</label>
				</th>
				<td>
					<input type="email" id="sua_email" name="sua_email" class="regular-text" />

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
					<label for="sua_role"><?php _e( 'Role', 'simple-user-adding' ); ?></label>
				</th>
				<td>
					<select name="role" id="sua_role">
						<?php wp_dropdown_roles( get_option( 'default_role' ) ); ?>
					</select>
				</td>
			</tr>
			<tr class="additional hidden">
				<th scope="row">
					<label for="sua_first_name"><?php _e( 'First Name', 'simple-user-adding' ); ?></label>
				</th>
				<td>
					<input type="text" id="sua_first_name" name="sua_first_name" class="regular-text" />
				</td>
			</tr>
			<tr class="additional hidden">
				<th scope="row">
					<label for="sua_last_name"><?php _e( 'Last Name', 'simple-user-adding' ); ?></label>
				</th>
				<td>
					<input type="text" id="sua_last_name" name="sua_last_name" class="regular-text" />
				</td>
			</tr>
			<tr class="additional hidden">
				<th scope="row"><label for="sua_url"><?php _e( 'Website' ) ?></label></th>
				<td>
					<input name="url" type="sua_url" id="sua_url" class="regular-text code" />
				</td>
			</tr>
			<?php foreach ( wp_get_user_contact_methods() as $name => $desc ) : ?>
				<tr class="additional hidden">
					<th>
						<label for="sua_<?php echo esc_attr( $name ); ?>"><?php echo apply_filters( "user_{$name}_label", $desc ); ?></label>
					</th>
					<td>
						<input type="text" name="sua_<?php echo esc_attr( $name ); ?>" id="sua_<?php echo esc_attr( $name ); ?>" class="regular-text" />
					</td>
				</tr>
			<?php endforeach ?>
			<tr>
				<th scope="row">
				</th>
				<td>
					<button id="sua_showmore" class="button-secondary" data-more="<?php _e( 'Show More', 'simple-user-adding' ); ?>" data-less="<?php _e( 'Show Less', 'simple-user-adding' ); ?>"><?php _e( 'Show More', 'simple-user-adding' ); ?></button>
				</td>
			</tr>
		</table>

		<input type="hidden" name="action" value="simple_user_adding">
		<?php wp_nonce_field( 'simple-user-adding', 'simple_user_adding_nonce' ); ?>
		<?php submit_button( __( 'Add New User', 'simple-user-adding' ), 'primary', 'submit' ); ?>
	</form>
</div>
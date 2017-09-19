<?php
/**
 * Profile Editor
 *
 * This template is used to display the profile editor with [give_profile_editor]
 *
 * @copyright    Copyright (c) 2016, WordImpress
 * @license      https://opensource.org/licenses/gpl-license GNU Public License
 */
$current_user     = wp_get_current_user();

if ( is_user_logged_in() ):
	$user_id = get_current_user_id();
	$first_name   = get_user_meta( $user_id, 'first_name', true );
	$last_name    = get_user_meta( $user_id, 'last_name', true );
	$display_name = $current_user->display_name;
	$address      = give_get_donor_address( $user_id );

	if ( isset( $_GET['updated'] ) && $_GET['updated'] == true && ! give_get_errors() ): ?>
		<p class="give_success">
			<strong><?php esc_html_e( 'Success:', 'give' ); ?></strong> <?php esc_html_e( 'Your profile has been updated.', 'give' ); ?>
		</p>
	<?php endif; ?>

	<?php Give()->notices->render_frontend_notices( 0 ); ?>

	<?php
	/**
	 * Fires in the profile editor shortcode, before the form.
	 *
	 * Allows you to add new elements before the form.
	 *
	 * @since 1.0
	 */
	do_action( 'give_profile_editor_before' );
	?>

	<form id="give_profile_editor_form" class="give-form" action="<?php echo give_get_current_page_url(); ?>" method="post">

		<fieldset>

			<legend id="give_profile_name_label"><?php esc_html_e( 'Change your Name', 'give' ); ?></legend>

			<p id="give_profile_first_name_wrap" class="form-row form-row-first form-row-responsive">
				<label for="give_first_name"><?php esc_html_e( 'First Name', 'give' ); ?></label>
				<input name="give_first_name" id="give_first_name" class="text give-input" type="text" value="<?php echo esc_attr( $first_name ); ?>"/>
			</p>

			<p id="give_profile_last_name_wrap" class="form-row form-row-last form-row-responsive">
				<label for="give_last_name"><?php esc_html_e( 'Last Name', 'give' ); ?></label>
				<input name="give_last_name" id="give_last_name" class="text give-input" type="text" value="<?php echo esc_attr( $last_name ); ?>"/>
			</p>

			<p id="give_profile_display_name_wrap" class="form-row form-row-first form-row-responsive">
				<label for="give_display_name"><?php esc_html_e( 'Display Name', 'give' ); ?></label>
				<select name="give_display_name" id="give_display_name" class="select give-select">
					<?php if ( ! empty( $current_user->first_name ) ): ?>
						<option <?php selected( $display_name, $current_user->first_name ); ?> value="<?php echo esc_attr( $current_user->first_name ); ?>"><?php echo esc_html( $current_user->first_name ); ?></option>
					<?php endif; ?>
					<option <?php selected( $display_name, $current_user->user_nicename ); ?> value="<?php echo esc_attr( $current_user->user_nicename ); ?>"><?php echo esc_html( $current_user->user_nicename ); ?></option>
					<?php if ( ! empty( $current_user->last_name ) ): ?>
						<option <?php selected( $display_name, $current_user->last_name ); ?> value="<?php echo esc_attr( $current_user->last_name ); ?>"><?php echo esc_html( $current_user->last_name ); ?></option>
					<?php endif; ?>
					<?php if ( ! empty( $current_user->first_name ) && ! empty( $current_user->last_name ) ): ?>
						<option <?php selected( $display_name, $current_user->first_name . ' ' . $current_user->last_name ); ?> value="<?php echo esc_attr( $current_user->first_name . ' ' . $current_user->last_name ); ?>"><?php echo esc_html( $current_user->first_name . ' ' . $current_user->last_name ); ?></option>
						<option <?php selected( $display_name, $current_user->last_name . ' ' . $current_user->first_name ); ?> value="<?php echo esc_attr( $current_user->last_name . ' ' . $current_user->first_name ); ?>"><?php echo esc_html( $current_user->last_name . ' ' . $current_user->first_name ); ?></option>
					<?php endif; ?>
				</select>
				<?php
				/**
				 * Fires in the profile editor shortcode, to the name section.
				 *
				 * Allows you to add new elements to the name section.
				 *
				 * @since 1.0
				 */
				do_action( 'give_profile_editor_name' );
				?>
			</p>

			<?php
			/**
			 * Fires in the profile editor shortcode, after the name field.
			 *
			 * Allows you to add new fields after the name field.
			 *
			 * @since 1.0
			 */
			do_action( 'give_profile_editor_after_name' );
			?>

			<p class="form-row form-row-last form-row-responsive">
				<label for="give_email"><?php esc_html_e( 'Email Address', 'give' ); ?></label>
				<input name="give_email" id="give_email" class="text give-input required" type="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required aria-required="true"/>
				<?php
				/**
				 * Fires in the profile editor shortcode, to the email section.
				 *
				 * Allows you to add new elements to the email section.
				 *
				 * @since 1.0
				 */
				do_action( 'give_profile_editor_email' );
				?>
			</p>

			<?php
			/**
			 * Fires in the profile editor shortcode, after the email field.
			 *
			 * Allows you to add new fields after the email field.
			 *
			 * @since 1.0
			 */
			do_action( 'give_profile_editor_after_email' );
			?>

			<legend id="give_profile_billing_address_label"><?php esc_html_e( 'Change your Billing Address', 'give' ); ?></legend>

			<div id="give_profile_billing_address_wrap">
				<?php
				// Get selected country from address.
				$selected_country = ( ! empty( $address['country'] ) ? $address['country'] : '' );

				$selected_state = '';
				if ( $selected_country === give_get_country() ) {
					// Get defalut selected state by admin.
					$selected_state = give_get_state();
				}

				// Get selected state from address.
				$selected_state = ! empty( $address['state'] ) ? $address['state'] : $selected_state;

				$label        = __( 'State', 'give' );
				$states_label = give_get_states_label();
				// Check if $country code exists in the array key for states label.
				if ( array_key_exists( $selected_country, $states_label ) ) {
					$label = $states_label[ $selected_country ];
				}

				$states = give_get_states( $selected_country );

				// Get the country list that do not have any states init.
				$no_states_country = give_no_states_country_list();

				// Get the country list that does not require states.
				$states_not_required_country_list = give_states_not_required_country_list();
				?>

				<p id="give-card-country-wrap" class="form-row form-row-wide">
					<label for="give_address_country"><?php esc_html_e( 'Country', 'give' ); ?></label>
					<select name="give_address_country" id="give_address_country" class="select give-select">
						<?php foreach ( give_get_country_list() as $key => $country ) : ?>
							<option value="<?php echo $key; ?>"<?php selected( $selected_country, $key ); ?>><?php echo esc_html( $country ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p id="give-card-address-wrap" class="form-row form-row-wide">
					<label for="give_address_line1"><?php esc_html_e( 'Address 1', 'give' ); ?></label>
					<input name="give_address_line1" id="give_address_line1" class="text give-input" type="text"
					       value="<?php echo esc_attr( $address['line1'] ); ?>"/>
				</p>

				<p id="give-card-address-2-wrap" class="form-row form-row-wide">
					<label for="give_address_line2"><?php esc_html_e( 'Address 2', 'give' ); ?></label>
					<input name="give_address_line2" id="give_address_line2" class="text give-input" type="text"
					       value="<?php echo esc_attr( $address['line2'] ); ?>"/>
				</p>


				<p id="give-card-state-wrap"
				   class="form-row form-row-wide <?php echo ( ! empty( $selected_country ) && array_key_exists( $selected_country, $no_states_country ) ) ? 'give-hidden' : ''; ?>">
					<label for="give_address_state"><?php esc_html_e( 'State / Province / County', 'give' ); ?></label>
					<?php
					if ( ! empty( $states ) ) : ?>
						<select
								name="give_address_state"
								id="give_address_state"
								class="give_address_state"
						<?php
						foreach ( $states as $state_code => $state ) {
							echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
						}
						?>
						</select>
					<?php else : ?>
						<input type="text" size="6" name="give_address_state" id="give_address_state"
						       class="give_address_state give-input"
						       placeholder="<?php echo $label; ?>" value="<?php echo $selected_state; ?>"/>
					<?php endif;
					?>
				</p>

				<p id="give-card-city-wrap" class="form-row form-row-first form-row-responsive">
					<label for="give_address_city"><?php esc_html_e( 'City', 'give' ); ?></label>
					<input name="give_address_city" id="give_address_city" class="text give-input" type="text"
					       value="<?php echo esc_attr( $address['city'] ); ?>"/>
				</p>

				<p id="give-card-zip-wrap" class="form-row form-row-last form-row-responsive">
					<label for="give_address_zip"><?php esc_html_e( 'Zip / Postal Code', 'give' ); ?></label>
					<input name="give_address_zip" id="give_address_zip" class="text give-input" type="text"
					       value="<?php echo esc_attr( $address['zip'] ); ?>"/>
				</p>

				<?php
				/**
				 * Fires in the profile editor shortcode, to the address section.
				 *
				 * Allows you to add new elements to the address section.
				 *
				 * @since 1.0
				 */
				do_action( 'give_profile_editor_address' );
				?>

			</div>

			<?php
			/**
			 * Fires in the profile editor shortcode, after the address field.
			 *
			 * Allows you to add new fields after the address field.
			 *
			 * @since 1.0
			 */
			do_action( 'give_profile_editor_after_address' );
			?>

			<legend id="give_profile_password_label"><?php esc_html_e( 'Change your Password', 'give' ); ?></legend>

			<div id="give_profile_password_wrap" class="give-clearfix">
				<p id="give_profile_password_wrap_1" class="form-row form-row-first form-row-responsive">
					<label for="give_new_user_pass1"><?php esc_html_e( 'New Password', 'give' ); ?></label>
					<input name="give_new_user_pass1" id="give_new_user_pass1" class="password give-input" type="password"/>
				</p>

				<p id="give_profile_password_wrap_2" class="form-row form-row-last form-row-responsive">
					<label for="give_new_user_pass2"><?php esc_html_e( 'Re-enter Password', 'give' ); ?></label>
					<input name="give_new_user_pass2" id="give_new_user_pass2" class="password give-input" type="password"/>
					<?php
					/**
					 * Fires in the profile editor shortcode, to the password section.
					 *
					 * Allows you to add new elements to the password section.
					 *
					 * @since 1.0
					 */
					do_action( 'give_profile_editor_password' );
					?>
				</p>
			</div>

			<p class="give_password_change_notice"><?php esc_html_e( 'Please note after changing your password, you must log back in.', 'give' ); ?></p>

			<?php
			/**
			 * Fires in the profile editor shortcode, after the password field.
			 *
			 * Allows you to add new fields after the password field.
			 *
			 * @since 1.0
			 */
			do_action( 'give_profile_editor_after_password' );
			?>

			<p id="give_profile_submit_wrap">
				<input type="hidden" name="give_profile_editor_nonce" value="<?php echo wp_create_nonce( 'give-profile-editor-nonce' ); ?>"/>
				<input type="hidden" name="give_action" value="edit_user_profile"/>
				<input type="hidden" name="give_redirect" value="<?php echo esc_url( give_get_current_page_url() ); ?>"/>
				<input name="give_profile_editor_submit" id="give_profile_editor_submit" type="submit" class="give_submit" value="<?php esc_attr_e( 'Save Changes', 'give' ); ?>"/>
			</p>

		</fieldset>

	</form><!-- #give_profile_editor_form -->

	<?php
	/**
	 * Fires in the profile editor shortcode, after the form.
	 *
	 * Allows you to add new elements after the form.
	 *
	 * @since 1.0
	 */
	do_action( 'give_profile_editor_after' );
	?>

	<?php
else:
	esc_html_e( 'You need to login to edit your profile.', 'give' );
	echo give_login_form();
endif;

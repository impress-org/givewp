<?php
/**
 * Give Form Template
 *
 * @package     Give
 * @subpackage  Forms
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Donation Form
 *
 * @since 1.0
 *
 * @param array $args Arguments for display
 *
 * @return string $purchase_form
 */
function give_get_donation_form( $args = array() ) {

	global $post;

	$post_id = is_object( $post ) ? $post->ID : 0;

	if ( isset( $args['id'] ) ) {
		$post_id = $args['id'];
	}

	$defaults = apply_filters( 'give_form_args_defaults', array(
		'form_id' => $post_id
	) );

	$args = wp_parse_args( $args, $defaults );

	$form = new Give_Donate_Form( $args['form_id'] );

	//bail if no form ID
	if ( empty( $form->ID ) ) {
		return false;
	}

	$payment_mode = give_get_chosen_gateway( $form->ID );

	$form_action = esc_url( add_query_arg( apply_filters( 'give_form_action_args', array(
		'payment-mode' => $payment_mode,
	) ),
		get_permalink()
	) );

	if ( 'publish' !== $form->post_status && ! current_user_can( 'edit_product', $form->ID ) ) {
		return false; // Product not published or user doesn't have permission to view drafts
	}

	$display_option = ( isset( $args['display_style'] ) && ! empty( $args['display_style'] ) )
		? $args['display_style']
		: get_post_meta( $form->ID, '_give_payment_display', true );

	$float_labels_option = give_is_float_labels_enabled( $args )
		? ' float-labels-enabled'
		: '';


	$form_classes_array = apply_filters( 'give_form_classes', array(
		'give-form-wrap',
		'give-display-' . $display_option
	), $form->ID, $args );

	$form_classes = implode( ' ', $form_classes_array );

	ob_start();

	/**
	 * Fires before the post form outputs.
	 *
	 * @since 1.0
	 *
	 * @param int   $form ->ID The current form ID
	 * @param array $args An array of form args
	 */
	do_action( 'give_pre_form_output', $form->ID, $args );

	?>

	<div id="give-form-<?php echo $form->ID; ?>-wrap" class="<?php echo $form_classes; ?>">

		<?php
		if ( isset( $args['show_title'] ) && $args['show_title'] == true ) {

			echo apply_filters( 'give_form_title', '<h2  class="give-form-title">' . get_the_title( $post_id ) . '</h2>' );

		} ?>

		<?php do_action( 'give_pre_form', $form->ID, $args ); ?>

		<form id="give-form-<?php echo $post_id; ?>" class="give-form give-form_<?php echo absint( $form->ID ); ?><?php echo $float_labels_option; ?>" action="<?php echo $form_action; ?>" method="post">
			<input type="hidden" name="give-form-id" value="<?php echo $form->ID; ?>" />
			<input type="hidden" name="give-form-title" value="<?php echo htmlentities( $form->post_title ); ?>" />
			<input type="hidden" name="give-current-url" value="<?php echo htmlspecialchars( get_permalink() ); ?>" />
			<input type="hidden" name="give-form-url" value="<?php echo htmlspecialchars( get_permalink( $post_id ) ); ?>" />
			<?php
			//Price ID hidden field for variable (mult-level) donation forms
			if ( give_has_variable_prices( $post_id ) ) {
				//get default selected price ID
				$prices   = apply_filters( 'give_form_variable_prices', give_get_variable_prices( $post_id ), $post_id );
				$price_id = 0;
				//loop through prices
				foreach ( $prices as $price ) {
					if ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) {
						$price_id = $price['_give_id']['level_id'];
					};
				}
				?>
				<input type="hidden" name="give-price-id" value="<?php echo $price_id; ?>" />
			<?php }

			do_action( 'give_checkout_form_top', $form->ID, $args );

			do_action( 'give_payment_mode_select', $form->ID, $args );

			do_action( 'give_checkout_form_bottom', $form->ID, $args );

			?>

		</form>

		<?php do_action( 'give_post_form', $form->ID, $args ); ?>

		<!--end #give-form-<?php echo absint( $form->ID ); ?>--></div>
	<?php

	/**
	 * Fires after the post form outputs.
	 *
	 * @since 1.0
	 *
	 * @param int   $form ->ID The current form ID
	 * @param array $args An array of form args
	 */
	do_action( 'give_post_form_output', $form->ID, $args );

	$final_output = ob_get_clean();

	echo apply_filters( 'give_donate_form', $final_output, $args );
}


/**
 *
 * Give Show Purchase Form
 *
 * Renders the Donation Form, hooks are provided to add to the checkout form.
 * The default Purchase Form rendered displays a list of the enabled payment
 * gateways, a user registration form (if enable) and a credit card info form
 * if credit cards are enabled
 *
 * @since 1.0
 *
 * @param int $form_id      ID of the Give Form
 *
 * @global    $give_options Array of all the Give options
 * @return string
 */
function give_show_purchase_form( $form_id ) {

	$payment_mode = give_get_chosen_gateway( $form_id );

	if ( ! isset( $form_id ) && isset( $_POST['give_form_id'] ) ) {
		$form_id = $_POST['give_form_id'];
	}

	do_action( 'give_purchase_form_top', $form_id );

	if ( give_can_checkout() && isset( $form_id ) ) {

		do_action( 'give_purchase_form_before_register_login', $form_id );

		$show_register_form = apply_filters( 'give_show_register_form', get_post_meta( $form_id, '_give_show_register_form', true ) );

		if ( ( $show_register_form === 'registration' || ( $show_register_form === 'both' && ! isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) : ?>
			<div id="give_checkout_login_register">
				<?php do_action( 'give_purchase_form_register_fields', $form_id ); ?>
			</div>
		<?php elseif ( ( $show_register_form === 'login' || ( $show_register_form === 'both' && isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) : ?>
			<div id="give_checkout_login_register">
				<?php do_action( 'give_purchase_form_login_fields', $form_id ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ( ! isset( $_GET['login'] ) && is_user_logged_in() ) || ! isset( $show_register_form ) || 'none' === $show_register_form ) {
			do_action( 'give_purchase_form_after_user_info', $form_id );
		}

		do_action( 'give_purchase_form_before_cc_form', $form_id );

		// Load the credit card form and allow gateways to load their own if they wish
		if ( has_action( 'give_' . $payment_mode . '_cc_form' ) ) {
			do_action( 'give_' . $payment_mode . '_cc_form', $form_id );
		} else {
			do_action( 'give_cc_form', $form_id );
		}

		do_action( 'give_purchase_form_after_cc_form', $form_id );

	} else {
		// Can't checkout
		do_action( 'give_purchase_form_no_access', $form_id );

	}

	do_action( 'give_purchase_form_bottom', $form_id );
}

add_action( 'give_purchase_form', 'give_show_purchase_form' );

/**
 * Donation Levels Output
 *
 * Outputs donation levels based on the specific form ID.
 * The output generated can be overridden by the filters provided or by removing
 * the action and adding your own custom action.
 *
 * @since 1.0
 *
 * @param int   $form_id Give Form ID
 * @param array $args
 *
 * @return void
 */
function give_output_donation_levels( $form_id = 0, $args = array() ) {

	global $give_options;

	$variable_pricing    = give_has_variable_prices( $form_id );
	$allow_custom_amount = get_post_meta( $form_id, '_give_custom_amount', true );
	$currency_position   = isset( $give_options['currency_position'] ) ? $give_options['currency_position'] : 'before';
	$symbol              = isset( $give_options['currency'] ) ? give_currency_symbol( $give_options['currency'] ) : '$';
	$currency_output     = '<span class="give-currency-symbol give-currency-position-' . $currency_position . '">' . $symbol . '</span>';
	$default_amount      = give_format_amount( give_get_default_form_amount( $form_id ) );
	$custom_amount_text  = get_post_meta( $form_id, '_give_custom_amount_text', true );

	do_action( 'give_before_donation_levels', $form_id );

	//Set Price, No Custom Amount Allowed means hidden price field
	if ( $allow_custom_amount == 'no' ) {
		?>

		<input id="give-amount" class="give-amount-hidden" type="hidden" name="give-amount" value="<?php echo $default_amount; ?>" required>
		<p class="set-price give-donation-amount form-row-wide">
			<?php
			if ( $currency_position == 'before' ) {
				echo $currency_output;
			}
			?>
			<span id="give-amount" class="give-text-input"><?php echo $default_amount; ?></span>
		</p>
		<?php
	} else {
		//Custom Amount Allowed
		?>
		<div class="give-total-wrap">
			<div class="give-donation-amount form-row-wide">
				<?php

				if ( $currency_position == 'before' ) {
					echo $currency_output;
				}
				?>

				<input class="give-text-input" id="give-amount" name="give-amount" type="tel" placeholder="" value="<?php echo $default_amount; ?>" required autocomplete="off">

				<?php if ( $currency_position == 'after' ) {
					echo $currency_output;
				} ?>

				<p class="give-loading-text give-updating-price-loader" style="display: none;">
					<span class="give-loading-animation"></span> <?php _e( 'Updating Amount', 'give' ); ?>
					<span class="elipsis">.</span><span class="elipsis">.</span><span class="elipsis">.</span></p>
			</div>
		</div>
	<?php }

	//Custom Amount Text
	if ( ! empty( $custom_amount_text ) && ! $variable_pricing && $allow_custom_amount == 'yes' ) { ?>
		<p class="give-custom-amount-text"><?php echo $custom_amount_text; ?></p>
	<?php }

	//Output Variable Pricing Levels
	if ( $variable_pricing ) {
		give_output_levels( $form_id );
	}
	do_action( 'give_after_donation_levels', $form_id, $args );
}

add_action( 'give_checkout_form_top', 'give_output_donation_levels', 10, 2 );


/**
 * Outputs the Donation Levels in various formats such as dropdown, radios, and buttons
 *
 * @since 1.0
 *
 * @param int $form_id Give Form ID
 *
 * @return string
 */
function give_output_levels( $form_id ) {

	//Get variable pricing
	$prices             = apply_filters( 'give_form_variable_prices', give_get_variable_prices( $form_id ), $form_id );
	$display_style      = get_post_meta( $form_id, '_give_display_style', true );
	$custom_amount      = get_post_meta( $form_id, '_give_custom_amount', true );
	$custom_amount_text = apply_filters( '', get_post_meta( $form_id, '_give_custom_amount_text', true ) );
	$output             = '';
	$counter            = 0;

	switch ( $display_style ) {
		case 'buttons':

			$output .= '<ul id="give-donation-level-button-wrap" class="give-donation-levels-wrap give-list-inline">';

			foreach ( $prices as $price ) {
				$counter ++;
				$level_text = apply_filters( 'give_form_level_text', ! empty( $price['_give_text'] ) ? $price['_give_text'] : give_currency_filter( give_format_amount( $price['_give_amount'] ) ), $form_id, $price );

				$output .= '<li>';
				$output .= '<button type="button" data-price-id="' . $price['_give_id']['level_id'] . '" class="give-donation-level-btn give-btn give-btn-level-' . $counter . ' ' . ( ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) ? 'give-default-level' : '' ) . '" value="' . give_format_amount( $price['_give_amount'] ) . '">';
				$output .= $level_text;
				$output .= '</button>';
				$output .= '</li>';

			}

			//Custom Amount
			if ( $custom_amount === 'yes' && ! empty( $custom_amount_text ) ) {
				$output .= '<li>';
				$output .= '<button type="button" data-price-id="custom" class="give-donation-level-btn give-btn give-btn-level-custom" value="custom">';
				$output .= $custom_amount_text;
				$output .= '</button>';
				$output .= '</li>';
			}

			$output .= '</ul>';

			break;

		case 'radios':

			$output .= '<ul id="give-donation-level-radio-list" class="give-donation-levels-wrap">';

			foreach ( $prices as $price ) {
				$counter ++;
				$level_text = apply_filters( 'give_form_level_text', ! empty( $price['_give_text'] ) ? $price['_give_text'] : give_currency_filter( give_format_amount( $price['_give_amount'] ) ), $form_id, $price );

				$output .= '<li>';
				$output .= '<input type="radio" data-price-id="' . $price['_give_id']['level_id'] . '" class="give-radio-input give-radio-input-level give-radio-level-' . $counter . ( ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) ? ' give-default-level' : '' ) . '" name="give-radio-donation-level" id="give-radio-level-' . $counter . '" ' . ( ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) ? 'checked="checked"' : '' ) . ' value="' . give_format_amount( $price['_give_amount'] ) . '">';

				$output .= '<label for="give-radio-level-' . $counter . '">' . $level_text . '</label>';

				$output .= '</li>';

			}

			//Custom Amount
			if ( $custom_amount === 'yes' && ! empty( $custom_amount_text ) ) {
				$output .= '<li>';
				$output .= '<input type="radio" data-price-id="custom" class="give-radio-input give-radio-input-level give-radio-level-custom" name="give-radio-donation-level" id="give-radio-level-custom" value="custom">';
				$output .= '<label for="give-radio-level-custom">' . $custom_amount_text . '</label>';
				$output .= '</li>';
			}

			$output .= '</ul>';

			break;

		case 'dropdown':

			$output .= '<select id="give-donation-level-' . $form_id . '" class="give-select give-select-level">';

			//first loop through prices
			foreach ( $prices as $price ) {
				$level_text = apply_filters( 'give_form_level_text', ! empty( $price['_give_text'] ) ? $price['_give_text'] : give_currency_filter( give_format_amount( $price['_give_amount'] ) ), $form_id, $price );

				$output .= '<option data-price-id="' . $price['_give_id']['level_id'] . '" class="give-donation-level-' . $form_id . '" ' . ( ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) ? 'selected="selected"' : '' ) . ' value="' . give_format_amount( $price['_give_amount'] ) . '">';
				$output .= $level_text;
				$output .= '</option>';

			}

			//Custom Amount
			if ( $custom_amount === 'yes' && ! empty( $custom_amount_text ) ) {
				$output .= '<option data-price-id="custom" class="give-donation-level-custom" value="custom">' . $custom_amount_text . '</option>';
			}

			$output .= '</select>';

			break;
	}

	echo apply_filters( 'give_form_level_output', $output, $form_id );
}


/**
 * Display Reveal & Lightbox Button
 *
 * @description: Outputs a button to reveal form fields
 *
 * @param int   $form_id
 * @param array $args
 *
 */
function give_display_checkout_button( $form_id, $args ) {

	$display_option = ( isset( $args['display_style'] ) && ! empty( $args['display_style'] ) )
		? $args['display_style']
		: get_post_meta( $form_id, '_give_payment_display', true );

	//no btn for onpage
	if ( $display_option === 'onpage' ) {
		return;
	}

	$display_label_field = get_post_meta( $form_id, '_give_reveal_label', true );
	$display_label       = ( ! empty( $display_label_field ) ? $display_label_field : __( 'Donate Now', 'give' ) );

	$output = '<button type="button" class="give-btn give-btn-' . $display_option . '">' . $display_label . '</button>';

	echo apply_filters( 'give_display_checkout_button', $output );
}

add_action( 'give_after_donation_levels', 'give_display_checkout_button', 10, 2 );

/**
 * Shows the User Info fields in the Personal Info box, more fields can be added
 * via the hooks provided.
 *
 * @since 1.0
 *
 * @param int $form_id
 *
 * @return void
 */
function give_user_info_fields( $form_id ) {

	if ( is_user_logged_in() ) :
		$user_data = get_userdata( get_current_user_id() );
	endif;

	do_action( 'give_purchase_form_before_personal_info', $form_id );
	?>
	<fieldset id="give_checkout_user_info">
		<legend><?php echo apply_filters( 'give_checkout_personal_info_text', __( 'Personal Info', 'give' ) ); ?></legend>
		<p id="give-first-name-wrap" class="form-row form-row-first">
			<label class="give-label" for="give-first">
				<?php _e( 'First Name', 'give' ); ?>
				<?php if ( give_field_is_required( 'give_first', $form_id ) ) { ?>
					<span class="give-required-indicator">*</span>
				<?php } ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'We will use this to personalize your account experience.', 'give' ); ?>"></span>
			</label>
			<input class="give-input required" type="text" name="give_first" placeholder="<?php _e( 'First name', 'give' ); ?>" id="give-first" value="<?php echo is_user_logged_in() ? $user_data->first_name : ''; ?>" required />
		</p>

		<p id="give-last-name-wrap" class="form-row form-row-last">
			<label class="give-label" for="give-last">
				<?php _e( 'Last Name', 'give' ); ?>
				<?php if ( give_field_is_required( 'give_last', $form_id ) ) { ?>
					<span class="give-required-indicator">*</span>
				<?php } ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'We will use this as well to personalize your account experience.', 'give' ); ?>"></span>
			</label>

			<input class="give-input<?php if ( give_field_is_required( 'give_last', $form_id ) ) {
				echo ' required';
			} ?>" type="text" name="give_last" id="give-last" placeholder="<?php _e( 'Last name', 'give' ); ?>" value="<?php echo is_user_logged_in() ? $user_data->last_name : ''; ?>" />
		</p>

		<?php do_action( 'give_purchase_form_before_email', $form_id ); ?>
		<p id="give-email-wrap" class="form-row form-row-wide">
			<label class="give-label" for="give-email">
				<?php _e( 'Email Address', 'give' ); ?>
				<?php if ( give_field_is_required( 'give_email', $form_id ) ) { ?>
					<span class="give-required-indicator">*</span>
				<?php } ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'We will send the purchase receipt to this address.', 'give' ); ?>"></span>
			</label>

			<input class="give-input required" type="email" name="give_email" placeholder="<?php _e( 'Email address', 'give' ); ?>" id="give-email" value="<?php echo is_user_logged_in() ? $user_data->user_email : ''; ?>" required />

		</p>
		<?php do_action( 'give_purchase_form_after_email', $form_id ); ?>

		<?php do_action( 'give_purchase_form_user_info', $form_id ); ?>
	</fieldset>
	<?php
	do_action( 'give_purchase_form_after_personal_info', $form_id );

}

add_action( 'give_purchase_form_after_user_info', 'give_user_info_fields' );
add_action( 'give_register_fields_before', 'give_user_info_fields' );

/**
 * Renders the credit card info form.
 *
 * @since 1.0
 *
 * @param int $form_id
 *
 * @return void
 */
function give_get_cc_form( $form_id ) {

	ob_start();

	do_action( 'give_before_cc_fields', $form_id ); ?>

	<fieldset id="give_cc_fields-<?php echo $form_id ?>" class="give-do-validate">
		<legend><?php _e( 'Credit Card Info', 'give' ); ?></legend>
		<?php if ( is_ssl() ) : ?>
			<div id="give_secure_site_wrapper-<?php echo $form_id ?>">
				<span class="give-icon padlock"></span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'give' ); ?></span>
			</div>
		<?php endif; ?>
		<p id="give-card-number-wrap-<?php echo $form_id ?>" class="form-row form-row-two-thirds">
			<label for="card_number" class="give-label">
				<?php _e( 'Card Number', 'give' ); ?>
				<span class="give-required-indicator">*</span>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The (typically) 16 digits on the front of your credit card.', 'give' ); ?>"></span>
				<span class="card-type"></span>
			</label>

			<input type="tel" autocomplete="off" name="card_number" id="card_number-<?php echo $form_id ?>" required class="card-number give-input required" placeholder="<?php _e( 'Card number', 'give' ); ?>" />
		</p>

		<p id="give-card-cvc-wrap-<?php echo $form_id ?>" class="form-row form-row-one-third">
			<label for="card_cvc" class="give-label">
				<?php _e( 'CVC', 'give' ); ?>
				<span class="give-required-indicator">*</span>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'give' ); ?>"></span>
			</label>

			<input type="tel" size="4" required autocomplete="off" name="card_cvc" id="card_cvc-<?php echo $form_id ?>" class="card-cvc give-input required" placeholder="<?php _e( 'Security code', 'give' ); ?>" />
		</p>

		<p id="give-card-name-wrap-<?php echo $form_id ?>" class="form-row form-row-two-thirds">
			<label for="card_name" class="give-label">
				<?php _e( 'Name on the Card', 'give' ); ?>
				<span class="give-required-indicator">*</span>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The name printed on the front of your credit card.', 'give' ); ?>"></span>
			</label>

			<input type="text" autocomplete="off" required name="card_name" id="card_name-<?php echo $form_id ?>" class="card-name give-input required" placeholder="<?php _e( 'Card name', 'give' ); ?>" />
		</p>
		<?php do_action( 'give_before_cc_expiration' ); ?>
		<p class="card-expiration form-row form-row-one-third">
			<label for="card_expiry-<?php echo $form_id ?>" class="give-label">
				<?php _e( 'Expiration (MM/YY)', 'give' ); ?>
				<span class="give-required-indicator">*</span>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The date your credit card expires, typically on the front of the card.', 'give' ); ?>"></span>
			</label>

			<input type="hidden" id="card_exp_month-<?php echo $form_id ?>" name="card_exp_month" class="card-expiry-month" />
			<input type="hidden" id="card_exp_year-<?php echo $form_id ?>" name="card_exp_year" class="card-expiry-year" />

			<input type="tel" required autocomplete="off" name="card_expiry" id="card_expiry-<?php echo $form_id ?>" class="card-expiry give-input required" placeholder="<?php _e( 'MM / YY', 'give' ); ?>" />
		</p>
		<?php do_action( 'give_after_cc_expiration', $form_id ); ?>

	</fieldset>
	<?php
	do_action( 'give_after_cc_fields', $form_id );

	echo ob_get_clean();
}

add_action( 'give_cc_form', 'give_get_cc_form' );

/**
 * Outputs the default credit card address fields
 *
 * @since 1.0
 *
 * @param int $form_id
 *
 * @return void
 */
function give_default_cc_address_fields( $form_id ) {

	$logged_in = is_user_logged_in();

	if ( $logged_in ) {
		$user_address = get_user_meta( get_current_user_id(), '_give_user_address', true );
	}
	$line1 = $logged_in && ! empty( $user_address['line1'] ) ? $user_address['line1'] : '';
	$line2 = $logged_in && ! empty( $user_address['line2'] ) ? $user_address['line2'] : '';
	$city  = $logged_in && ! empty( $user_address['city'] ) ? $user_address['city'] : '';
	$zip   = $logged_in && ! empty( $user_address['zip'] ) ? $user_address['zip'] : '';
	ob_start(); ?>
	<fieldset id="give_cc_address" class="cc-address">
		<legend><?php _e( 'Billing Details', 'give' ); ?></legend>
		<?php do_action( 'give_cc_billing_top' ); ?>
		<p id="give-card-address-wrap" class="form-row form-row-two-thirds">
			<label for="card_address" class="give-label">
				<?php _e( 'Address', 'give' ); ?>
				<?php
				if ( give_field_is_required( 'card_address', $form_id ) ) { ?>
					<span class="give-required-indicator">*</span>
				<?php } ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The primary billing address for your credit card.', 'give' ); ?>"></span>
			</label>

			<input type="text" id="card_address" name="card_address" class="card-address give-input<?php if ( give_field_is_required( 'card_address', $form_id ) ) {
				echo ' required';
			} ?>" placeholder="<?php _e( 'Address line 1', 'give' ); ?>" value="<?php echo $line1; ?>" />
		</p>

		<p id="give-card-address-2-wrap" class="form-row form-row-one-third">
			<label for="card_address_2" class="give-label">
				<?php _e( 'Address Line 2', 'give' ); ?>
				<?php if ( give_field_is_required( 'card_address_2', $form_id ) ) { ?>
					<span class="give-required-indicator">*</span>
				<?php } ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( '(optional) The suite, apt no, PO box, etc, associated with your billing address.', 'give' ); ?>"></span>
			</label>

			<input type="text" id="card_address_2" name="card_address_2" class="card-address-2 give-input<?php if ( give_field_is_required( 'card_address_2', $form_id ) ) {
				echo ' required';
			} ?>" placeholder="<?php _e( 'Address line 2', 'give' ); ?>" value="<?php echo $line2; ?>" />
		</p>

		<p id="give-card-city-wrap" class="form-row form-row-two-thirds">
			<label for="card_city" class="give-label">
				<?php _e( 'City', 'give' ); ?>
				<?php if ( give_field_is_required( 'card_city', $form_id ) ) { ?>
					<span class="give-required-indicator">*</span>
				<?php } ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The city for your billing address.', 'give' ); ?>"></span>
			</label>
			<input type="text" id="card_city" name="card_city" class="card-city give-input<?php if ( give_field_is_required( 'card_city', $form_id ) ) {
				echo ' required';
			} ?>" placeholder="<?php _e( 'City', 'give' ); ?>" value="<?php echo $city; ?>" />
		</p>

		<p id="give-card-zip-wrap" class="form-row form-row-one-third">
			<label for="card_zip" class="give-label">
				<?php _e( 'Zip / Postal Code', 'give' ); ?>
				<?php if ( give_field_is_required( 'card_zip', $form_id ) ) { ?>
					<span class="give-required-indicator">*</span>
				<?php } ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The zip or postal code for your billing address.', 'give' ); ?>"></span>
			</label>

			<input type="text" size="4" id="card_zip" name="card_zip" class="card-zip give-input<?php if ( give_field_is_required( 'card_zip', $form_id ) ) {
				echo ' required';
			} ?>" placeholder="<?php _e( 'Zip / Postal code', 'give' ); ?>" value="<?php echo $zip; ?>" />
		</p>

		<p id="give-card-country-wrap" class="form-row form-row-first">
			<label for="billing_country" class="give-label">
				<?php _e( 'Country', 'give' ); ?>
				<?php if ( give_field_is_required( 'billing_country', $form_id ) ) { ?>
					<span class="give-required-indicator">*</span>
				<?php } ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The country for your billing address.', 'give' ); ?>"></span>
			</label>

			<select name="billing_country" id="billing_country" class="billing-country billing_country give-select<?php if ( give_field_is_required( 'billing_country', $form_id ) ) {
				echo ' required';
			} ?>">
				<?php

				$selected_country = give_get_country();

				if ( $logged_in && ! empty( $user_address['country'] ) && '*' !== $user_address['country'] ) {
					$selected_country = $user_address['country'];
				}

				$countries = give_get_country_list();
				foreach ( $countries as $country_code => $country ) {
					echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
				}
				?>
			</select>
		</p>

		<p id="give-card-state-wrap" class="form-row form-row-last">
			<label for="card_state" class="give-label">
				<?php _e( 'State / Province', 'give' ); ?>
				<?php if ( give_field_is_required( 'card_state', $form_id ) ) { ?>
					<span class="give-required-indicator">*</span>
				<?php } ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The state or province for your billing address.', 'give' ); ?>"></span>
			</label>

			<?php
			$selected_state = give_get_state();
			$states         = give_get_states( $selected_country );

			if ( $logged_in && ! empty( $user_address['state'] ) ) {
				$selected_state = $user_address['state'];
			}

			if ( ! empty( $states ) ) : ?>
				<select name="card_state" id="card_state" class="card_state give-select<?php if ( give_field_is_required( 'card_state', $form_id ) ) {
					echo ' required';
				} ?>">
					<?php
					foreach ( $states as $state_code => $state ) {
						echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
					}
					?>
				</select>
			<?php else : ?>
				<input type="text" size="6" name="card_state" id="card_state" class="card_state give-input" placeholder="<?php _e( 'State / Province', 'give' ); ?>" />
			<?php endif; ?>
		</p>
		<?php do_action( 'give_cc_billing_bottom' ); ?>
	</fieldset>
	<?php
	echo ob_get_clean();
}

add_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );


/**
 * Renders the user registration fields. If the user is logged in, a login
 * form is displayed other a registration form is provided for the user to
 * create an account.
 *
 * @since 1.0
 *
 * @param int $form_id
 *
 * @return string
 */
function give_get_register_fields( $form_id ) {

	global $give_options;
	global $user_ID;

	if ( is_user_logged_in() ) {
		$user_data = get_userdata( $user_ID );
	}

	$show_register_form = apply_filters( 'give_show_register_form', get_post_meta( $form_id, '_give_show_register_form', true ) );

	ob_start(); ?>
	<fieldset id="give_register_fields">

		<?php if ( $show_register_form == 'both' ) { ?>
			<div id="give-login-account-wrap">
				<p class="give-login-message"><?php _e( 'Already have an account?', 'give' ); ?>&nbsp;
					<a href="<?php echo esc_url( add_query_arg( 'login', 1 ) ); ?>" class="give_checkout_register_login" data-action="give_checkout_login"><?php _e( 'Login', 'give' ); ?></a>
				</p>

				<p class="give-loading-text">
					<span class="give-loading-animation"></span> <?php _e( 'Loading', 'give' ); ?>
					<span class="elipsis">.</span><span class="elipsis">.</span><span class="elipsis">.</span></p>
			</div>
		<?php } ?>

		<?php do_action( 'give_register_fields_before' ); ?>

		<fieldset id="give_register_account_fields">
			<legend><?php _e( 'Create an account', 'give' );
				if ( ! give_no_guest_checkout( $form_id ) ) {
					echo ' <span class="sub-text">' . __( '(optional)', 'give' ) . '</span>';
				} ?></legend>
			<?php do_action( 'give_register_account_fields_before' ); ?>
			<p id="give-user-login-wrap" class="form-row form-row-one-third form-row-first">
				<label for="give_user_login">
					<?php _e( 'Username', 'give' ); ?>
					<?php if ( give_no_guest_checkout( $form_id ) ) { ?>
						<span class="give-required-indicator">*</span>
					<?php } ?>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The username you will use to log into your account.', 'give' ); ?>"></span>
				</label>

				<input name="give_user_login" id="give_user_login" class="<?php if ( give_no_guest_checkout( $form_id ) ) {
					echo 'required ';
				} ?>give-input" type="text" placeholder="<?php _e( 'Username', 'give' ); ?>" title="<?php _e( 'Username', 'give' ); ?>" />
			</p>

			<p id="give-user-pass-wrap" class="form-row form-row-one-third">
				<label for="password">
					<?php _e( 'Password', 'give' ); ?>
					<?php if ( give_no_guest_checkout( $form_id ) ) { ?>
						<span class="give-required-indicator">*</span>
					<?php } ?>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'The password used to access your account.', 'give' ); ?>"></span>
				</label>

				<input name="give_user_pass" id="give_user_pass" class="<?php if ( give_no_guest_checkout( $form_id ) ) {
					echo 'required ';
				} ?>give-input" placeholder="<?php _e( 'Password', 'give' ); ?>" type="password" />
			</p>

			<p id="give-user-pass-confirm-wrap" class="give_register_password form-row form-row-one-third">
				<label for="password_again">
					<?php _e( 'Password Again', 'give' ); ?>
					<?php if ( give_no_guest_checkout( $form_id ) ) { ?>
						<span class="give-required-indicator">*</span>
					<?php } ?>
					<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php _e( 'Confirm your password.', 'give' ); ?>"></span>
				</label>

				<input name="give_user_pass_confirm" id="give_user_pass_confirm" class="<?php if ( give_no_guest_checkout( $form_id ) ) {
					echo 'required ';
				} ?>give-input" placeholder="<?php _e( 'Confirm password', 'give' ); ?>" type="password" />
			</p>
			<?php do_action( 'give_register_account_fields_after', $form_id ); ?>
		</fieldset>

		<?php do_action( 'give_register_fields_after', $form_id ); ?>

		<input type="hidden" name="give-purchase-var" value="needs-to-register" />

		<?php do_action( 'give_purchase_form_user_info', $form_id ); ?>

	</fieldset>
	<?php
	echo ob_get_clean();
}

add_action( 'give_purchase_form_register_fields', 'give_get_register_fields' );

/**
 * Gets the login fields for the login form on the checkout. This function hooks
 * on the give_purchase_form_login_fields to display the login form if a user already
 * had an account.
 *
 * @since 1.0
 *
 * @param int $form_id
 *
 * @return string
 */
function give_get_login_fields( $form_id ) {

	global $give_options;

	$color              = isset( $give_options['checkout_color'] ) ? $give_options['checkout_color'] : 'gray';
	$color              = ( $color == 'inherit' ) ? '' : $color;
	$show_register_form = give_get_option( 'show_register_form', 'none' );

	ob_start();
	?>
	<fieldset id="give_login_fields">
		<legend><?php _e( 'Login to Your Account', 'give' );
			if ( ! give_no_guest_checkout( $form_id ) ) {
				echo ' <span class="sub-text">' . __( '(optional)', 'give' ) . '</span>';
			} ?></legend>
		<?php if ( $show_register_form == 'both' ) { ?>
			<p id="give-new-account-wrap">
				<?php _e( 'Need to create an account?', 'give' ); ?>&nbsp;
				<a href="<?php echo remove_query_arg( 'login' ); ?>" class="give_checkout_register_login" data-action="checkout_register">
					<?php _e( 'Register', 'give' );
					if ( ! give_no_guest_checkout( $form_id ) ) {
						echo ' ' . __( 'or checkout as a guest.', 'give' );
					} ?>
				</a>
			</p>
		<?php } ?>
		<?php do_action( 'give_checkout_login_fields_before', $form_id ); ?>
		<p id="give-user-login-wrap" class="form-row form-row-first">
			<label class="give-label" for="give-username"><?php _e( 'Username', 'give' ); ?></label>
			<input class="<?php if ( give_no_guest_checkout( $form_id ) ) {
				echo 'required ';
			} ?>give-input" type="text" name="give_user_login" id="give_user_login" value="" placeholder="<?php _e( 'Your username', 'give' ); ?>" />
		</p>

		<p id="give-user-pass-wrap" class="give_login_password form-row form-row-last">
			<label class="give-label" for="give-password"><?php _e( 'Password', 'give' ); ?></label>
			<input class="<?php if ( give_no_guest_checkout( $form_id ) ) {
				echo 'required ';
			} ?>give-input" type="password" name="give_user_pass" id="give_user_pass" placeholder="<?php _e( 'Your password', 'give' ); ?>" />
			<input type="hidden" name="give-purchase-var" value="needs-to-login" />
		</p>

		<p id="give-user-login-submit" class="give-clearfix">
			<input type="submit" class="give-submit give-btn button <?php echo $color; ?>" name="give_login_submit" value="<?php _e( 'Login', 'give' ); ?>" />
			<span class="give-loading-animation"></span>
		</p>
		<?php do_action( 'give_checkout_login_fields_after' ); ?>
	</fieldset><!--end #give_login_fields-->
	<?php
	echo ob_get_clean();
}

add_action( 'give_purchase_form_login_fields', 'give_get_login_fields', 10, 1 );

/**
 * Payment Mode Select
 *
 * Renders the payment mode form by getting all the enabled payment gateways and
 * outputting them as radio buttons for the user to choose the payment gateway. If
 * a default payment gateway has been chosen from the Give Settings, it will be
 * automatically selected.
 *
 * @since 1.0
 *
 * @param int $form_id
 *
 * @return void
 */
function give_payment_mode_select( $form_id ) {

	$gateways = give_get_enabled_payment_gateways();

	do_action( 'give_payment_mode_top', $form_id ); ?>

	<fieldset id="give-payment-mode-select">
		<?php do_action( 'give_payment_mode_before_gateways_wrap' ); ?>
		<div id="give-payment-mode-wrap">
			<legend class="give-payment-mode-label"><?php echo apply_filters( 'give_checkout_payment_method_text', __( 'Select Payment Method', 'give' ) ); ?></legend>
			<?php

			do_action( 'give_payment_mode_before_gateways' ) ?>

			<ul id="give-gateway-radio-list">
				<?php foreach ( $gateways as $gateway_id => $gateway ) :
					$checked       = checked( $gateway_id, give_get_default_gateway( $form_id ), false );
					$checked_class = $checked ? ' give-gateway-option-selected' : '';
					echo '<li><label for="give-gateway-' . esc_attr( $gateway_id ) . '-' . $form_id . '" class="give-gateway-option' . $checked_class . '" id="give-gateway-option-' . esc_attr( $gateway_id ) . '">';
					echo '<input type="radio" name="payment-mode" class="give-gateway" id="give-gateway-' . esc_attr( $gateway_id ) . '-' . $form_id . '" value="' . esc_attr( $gateway_id ) . '"' . $checked . '>' . esc_html( $gateway['checkout_label'] );
					echo '</label></li>';
				endforeach; ?>
			</ul>
			<?php do_action( 'give_payment_mode_after_gateways' ); ?>
			<p class="give-loading-text"><span class="give-loading-animation"></span> <?php _e( 'Loading', 'give' ); ?>
				<span class="elipsis">.</span><span class="elipsis">.</span><span class="elipsis">.</span></p>
		</div>
		<?php do_action( 'give_payment_mode_after_gateways_wrap' ); ?>
	</fieldset>

	<?php do_action( 'give_payment_mode_bottom', $form_id ); ?>

	<div id="give_purchase_form_wrap">

		<?php do_action( 'give_purchase_form', $form_id ); ?>

	</div><!-- the checkout fields are loaded into this-->

	<?php do_action( 'give_purchase_form_wrap_bottom', $form_id );

}

add_action( 'give_payment_mode_select', 'give_payment_mode_select' );


/**
 * Renders the Checkout Agree to Terms, this displays a checkbox for users to
 * agree the T&Cs set in the Give Settings. This is only displayed if T&Cs are
 * set in the Give Settings.
 *
 * @since 1.0
 * @global    $give_options Array of all the Give Options
 *
 * @param int $form_id
 *
 * @return void
 */
function give_terms_agreement( $form_id ) {

	$form_option = get_post_meta( $form_id, '_give_terms_option', true );
	$label       = get_post_meta( $form_id, '_give_agree_label', true );
	$terms       = get_post_meta( $form_id, '_give_agree_text', true );

	if ( $form_option === 'yes' && ! empty( $terms ) ) {

		?>
		<fieldset id="give_terms_agreement">
			<div id="give_terms" style="display:none;">
				<?php
				do_action( 'give_before_terms' );
				echo wpautop( stripslashes( $terms ) );
				do_action( 'give_after_terms' );
				?>
			</div>
			<div id="give_show_terms">
				<a href="#" class="give_terms_links"><?php _e( 'Show Terms', 'give' ); ?></a>
				<a href="#" class="give_terms_links" style="display:none;"><?php _e( 'Hide Terms', 'give' ); ?></a>
			</div>

			<label for="give_agree_to_terms"><?php echo ! empty( $label ) ? stripslashes( $label ) : __( 'Agree to Terms?', 'give' ); ?></label>
			<input name="give_agree_to_terms" class="required" type="checkbox" id="give_agree_to_terms" value="1" />
		</fieldset>
		<?php
	}
}

add_action( 'give_purchase_form_before_submit', 'give_terms_agreement', 10, 1 );

/**
 * Checkout Final Total
 *
 * @description: Shows the final purchase total at the bottom of the checkout page
 *
 * @param int $form_id
 *
 * @since      1.0
 * @return void
 */
function give_checkout_final_total( $form_id ) {

	if ( isset( $_POST['give_total'] ) ) {
		$total = apply_filters( 'give_donation_total', $_POST['give_total'] );
	} else {
		//default total
		$total = give_get_default_form_amount( $form_id );
	}
	//Only proceed if give_total available
	if ( empty( $total ) ) {
		return;
	}
	?>
	<p id="give-final-total-wrap" class="form-wrap ">
		<span class="give-donation-total-label"><?php echo apply_filters( 'give_donation_total_label', __( 'Donation Total:', 'give' ) ); ?></span>
		<span class="give-final-total-amount" data-total="<?php echo give_format_amount( $total ); ?>"><?php echo give_currency_filter( give_format_amount( $total ) ); ?></span>
	</p>
	<?php
}

add_action( 'give_purchase_form_before_submit', 'give_checkout_final_total', 999 );


/**
 * Renders the Checkout Submit section
 *
 * @since 1.0
 *
 * @param int $form_id
 *
 * @return void
 */
function give_checkout_submit( $form_id ) {
	?>
	<fieldset id="give_purchase_submit">
		<?php do_action( 'give_purchase_form_before_submit', $form_id ); ?>

		<?php give_checkout_hidden_fields( $form_id ); ?>

		<?php echo give_checkout_button_purchase( $form_id ); ?>

		<?php do_action( 'give_purchase_form_after_submit', $form_id ); ?>

	</fieldset>
	<?php
}

add_action( 'give_purchase_form_after_cc_form', 'give_checkout_submit', 9999 );


/**
 * Give Checkout Button Purchase
 *
 * @description Renders the Purchase button on the Checkout
 * @since       1.0
 *
 * @param int $form_id
 *
 * @return string
 */
function give_checkout_button_purchase( $form_id ) {

	$display_label_field = get_post_meta( $form_id, '_give_checkout_label', true );
	$display_label       = ( ! empty( $display_label_field ) ? $display_label_field : __( 'Donate Now', 'give' ) );

	ob_start();

	?>

	<div class="give-submit-button-wrap give-clearfix">
		<input type="submit" class="give-submit give-btn" id="give-purchase-button" name="give-purchase" value="<?php echo $display_label; ?>" />
		<span class="give-loading-animation"></span>
	</div>
	<?php
	return apply_filters( 'give_checkout_button_purchase', ob_get_clean(), $form_id );
}

/**
 *
 * Give Agree to Terms
 *
 * @description Outputs the JavaScript code for the Agree to Terms section to toggle the T&Cs text
 * @since       1.0
 *
 * @param int $form_id
 *
 * @return void
 */
function give_agree_to_terms_js( $form_id ) {

	$form_option = get_post_meta( $form_id, '_give_terms_option', true );

	if ( $form_option === 'yes' ) {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function ( $ ) {
				$( 'body' ).on( 'click', '.give_terms_links', function ( e ) {
					e.preventDefault();
					$( '#give_terms' ).slideToggle();
					$( '.give_terms_links' ).toggle();
					return false;
				} );
			} );
		</script>
		<?php
	}
}

add_action( 'give_checkout_form_top', 'give_agree_to_terms_js', 10, 2 );


/**
 * Adds Actions to Render Form Content
 *
 * @since 1.0
 *
 * @param int   $form_id
 * @param array $args
 *
 * @return void
 */
function give_form_content( $form_id, $args ) {

	$show_content = ( isset( $args['show_content'] ) && ! empty( $args['show_content'] ) )
		? $args['show_content']
		: get_post_meta( $form_id, '_give_content_option', true );

	if ( $show_content !== 'none' ) {
		//add action according to value
		add_action( $show_content, 'give_form_display_content' );
	}
}

add_action( 'give_pre_form_output', 'give_form_content', 10, 2 );

/**
 * Show Give Goals
 * @since 1.0
 *
 * @param int   $form_id
 * @param array $args
 *
 * @return mixed
 */

function give_show_goal_progress( $form_id, $args ) {

	$goal_option = get_post_meta( $form_id, '_give_goal_option', true );
	$form        = new Give_Donate_Form( $form_id );
	$goal        = $form->goal;
	$income      = $form->get_earnings();
	$color       = get_post_meta( $form_id, '_give_goal_color', true );
	$show_text   = (bool) isset( $args['show_text'] ) ? filter_var( $args['show_text'], FILTER_VALIDATE_BOOLEAN ) : true;
	$show_bar    = (bool) isset( $args['show_bar'] ) ? filter_var( $args['show_bar'], FILTER_VALIDATE_BOOLEAN ) : true;

	//Sanity check - ensure form has goal set to output
	if ( empty( $form->ID )
	     || ( is_singular( 'give_forms' ) && $goal_option !== 'yes' )
	     || $goal_option !== 'yes'
	     || $goal == 0
	) {

		//not this form, bail
		return false;
	}

	$progress = round( ( $income / $goal ) * 100, 2 );

	if ( $income > $goal ) {
		$progress = 100;
	}

	$output = '<div class="goal-progress">';

	//Goal Progress Text
	if ( ! empty( $show_text ) ) {
		$output .= '<div class="raised">';
		$output .= sprintf( _x( '%s of %s raised', 'give', 'This text displays the amount of income raised compared to the goal.' ), '<span class="income">' . give_currency_filter( give_format_amount( $income ) ) . '</span>', '<span class="goal-text">' . give_currency_filter( give_format_amount( $goal ) ) ) . '</span>';
		$output .= '</div>';
	}
	//Goal Progress Bar
	if ( ! empty( $show_bar ) ) {
		$output .= '<div class="progress-bar">';
		$output .= '<span style="width: ' . esc_attr( $progress ) . '%;';
		if ( ! empty( $color ) ) {
			$output .= 'background-color:' . $color;
		}
		$output .= '"></span>';
		$output .= '</div><!-- /.progress-bar -->';
	}

	$output .= '</div><!-- /.goal-progress -->';

	echo apply_filters( 'give_goal_output', $output );

	return false;

}

add_action( 'give_pre_form', 'give_show_goal_progress', 10, 2 );

/**
 * Renders Post Form Content
 *
 * @description: Displays content for Give forms; fired by action from give_form_content
 *
 * @param int $form_id
 *
 * @return void
 * @since      1.0
 */
function give_form_display_content( $form_id ) {

	$content = wpautop( get_post_meta( $form_id, '_give_form_content', true ) );

	if ( give_get_option( 'disable_the_content_filter' ) !== 'on' ) {
		$content = apply_filters( 'the_content', $content );
	}

	$output = '<div id="give-form-content-' . $form_id . '" class="give-form-content-wrap" >' . $content . '</div>';

	echo apply_filters( 'give_form_content_output', $output );
}


/**
 * Renders the hidden Checkout fields
 *
 * @since 1.0
 *
 * @param int $form_id
 *
 * @return void
 */
function give_checkout_hidden_fields( $form_id ) {

	do_action( 'give_hidden_fields_before', $form_id );
	if ( is_user_logged_in() ) { ?>
		<input type="hidden" name="give-user-id" value="<?php echo get_current_user_id(); ?>" />
	<?php } ?>
	<input type="hidden" name="give_action" value="purchase" />
	<input type="hidden" name="give-gateway" value="<?php echo give_get_chosen_gateway( $form_id ); ?>" />
	<?php
	do_action( 'give_hidden_fields_after', $form_id );

}

/**
 * Filter Success Page Content
 *
 * Applies filters to the success page content.
 *
 * @since 1.0
 *
 * @param string $content Content before filters
 *
 * @return string $content Filtered content
 */
function give_filter_success_page_content( $content ) {

	global $give_options;

	if ( isset( $give_options['success_page'] ) && isset( $_GET['payment-confirmation'] ) && is_page( $give_options['success_page'] ) ) {
		if ( has_filter( 'give_payment_confirm_' . $_GET['payment-confirmation'] ) ) {
			$content = apply_filters( 'give_payment_confirm_' . $_GET['payment-confirmation'], $content );
		}
	}

	return $content;
}

add_filter( 'the_content', 'give_filter_success_page_content' );


/**
 * Test Mode Frontend Warning
 *
 * @description Displays a notice on the frontend for donation forms
 * @since       1.1
 */

function give_test_mode_frontend_warning() {

	$test_mode = give_get_option( 'test_mode' );

	if ( $test_mode == 'on' ) {
		echo '<div class="give_error give_warning" id="give_error_test_mode"><p><strong>' . __( 'Notice', 'give' ) . '</strong>: ' . __( 'Test mode is enabled. While in test mode no live transactions are processed.', 'give' ) . '</p></div>';
	}
}

add_action( 'give_pre_form', 'give_test_mode_frontend_warning', 10 );

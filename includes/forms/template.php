<?php
/**
 * Give Form Template
 *
 * @package     Give
 * @subpackage  Forms
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Donation Form.
 *
 * @since  1.0
 *
 * @param  array $args An array of form arguments.
 *
 * @return string Donation form.
 */
function give_get_donation_form( $args = array() ) {

	global $post;

	$form_id = is_object( $post ) ? $post->ID : 0;

	if ( isset( $args['id'] ) ) {
		$form_id = $args['id'];
	}

	$defaults = apply_filters( 'give_form_args_defaults', array(
		'form_id' => $form_id,
	) );

	$args = wp_parse_args( $args, $defaults );

	$form = new Give_Donate_Form( $args['form_id'] );

	//bail if no form ID.
	if ( empty( $form->ID ) ) {
		return false;
	}

	$payment_mode = give_get_chosen_gateway( $form->ID );

	$form_action = add_query_arg( apply_filters( 'give_form_action_args', array(
		'payment-mode' => $payment_mode,
	) ),
		give_get_current_page_url()
	);

	//Sanity Check: Donation form not published or user doesn't have permission to view drafts.
	if (
		( 'publish' !== $form->post_status && ! current_user_can( 'edit_give_forms', $form->ID ) )
		|| ( 'trash' === $form->post_status )
	) {
		return false;
	}

	//Get the form wrap CSS classes.
	$form_wrap_classes = $form->get_form_wrap_classes( $args );

	//Get the <form> tag wrap CSS classes.
	$form_classes = $form->get_form_classes( $args );

	ob_start();

	/**
	 * Fires while outputting donation form, before the form wrapper div.
	 *
	 * @since 1.0
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 */
	do_action( 'give_pre_form_output', $form->ID, $args );

	?>
    <div id="give-form-<?php echo $form->ID; ?>-wrap" class="<?php echo $form_wrap_classes; ?>">

		<?php if ( $form->is_close_donation_form() ) {

			// Get Goal thank you message.
			$goal_achieved_message = get_post_meta( $form->ID, '_give_form_goal_achieved_message', true );
			$goal_achieved_message = ! empty( $goal_achieved_message ) ? apply_filters( 'the_content', $goal_achieved_message ) : '';

			// Print thank you message.
			echo apply_filters( 'give_goal_closed_output', $goal_achieved_message, $form->ID );

		} else {
			/**
			 * Show form title:
			 * 1. if show_title params set to true
			 * 2. if admin set form display_style to button
			 */
			$form_title = apply_filters( 'give_form_title', '<h2 class="give-form-title">' . get_the_title( $form_id ) . '</h2>' );
			if (
				( isset( $args['show_title'] ) && $args['show_title'] == true )
				&& ! doing_action( 'give_single_form_summary' )
			) {
				echo $form_title;
			}

			/**
			 * Fires while outputing donation form, before the form.
			 *
			 * @since 1.0
			 *
			 * @param int   $form_id The form ID.
			 * @param array $args    An array of form arguments.
			 */
			do_action( 'give_pre_form', $form->ID, $args );
			?>

            <form id="give-form-<?php echo $form_id; ?>" class="<?php echo $form_classes; ?>"
                  action="<?php echo esc_url_raw( $form_action ); ?>" method="post">
                <input type="hidden" name="give-form-id" value="<?php echo $form->ID; ?>"/>
                <input type="hidden" name="give-form-title" value="<?php echo htmlentities( $form->post_title ); ?>"/>
                <input type="hidden" name="give-current-url"
                       value="<?php echo htmlspecialchars( give_get_current_page_url() ); ?>"/>
                <input type="hidden" name="give-form-url"
                       value="<?php echo htmlspecialchars( give_get_current_page_url() ); ?>"/>
                <input type="hidden" name="give-form-minimum"
                       value="<?php echo give_format_amount( give_get_form_minimum_price( $form->ID ) ); ?>"/>

                <!-- The following field is for robots only, invisible to humans: -->
                <span class="give-hidden" style="display: none !important;">
					<label for="give-form-honeypot-<?php echo $form_id; ?>"></label>
					<input id="give-form-honeypot-<?php echo $form_id; ?>" type="text" name="give-honeypot"
                           class="give-honeypot give-hidden"/>
				</span>

				<?php

				// Price ID hidden field for variable (mult-level) donation forms.
				if ( give_has_variable_prices( $form_id ) ) {
					// Get default selected price ID.
					$prices   = apply_filters( 'give_form_variable_prices', give_get_variable_prices( $form_id ), $form_id );
					$price_id = 0;
					//loop through prices.
					foreach ( $prices as $price ) {
						if ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) {
							$price_id = $price['_give_id']['level_id'];
						};
					}
					?>
                    <input type="hidden" name="give-price-id" value="<?php echo $price_id; ?>"/>
				<?php }

				/**
				 * Fires while outputting donation form, before all other fields.
				 *
				 * @since 1.0
				 *
				 * @param int   $form_id The form ID.
				 * @param array $args    An array of form arguments.
				 */
				do_action( 'give_checkout_form_top', $form->ID, $args );

				/**
				 * Fires while outputing donation form, for payment gatways fields.
				 *
				 * @since 1.7
				 *
				 * @param int   $form_id The form ID.
				 * @param array $args    An array of form arguments.
				 */
				do_action( 'give_payment_mode_select', $form->ID, $args );

				/**
				 * Fires while outputing donation form, after all other fields.
				 *
				 * @since 1.0
				 *
				 * @param int   $form_id The form ID.
				 * @param array $args    An array of form arguments.
				 */
				do_action( 'give_checkout_form_bottom', $form->ID, $args );

				?>
            </form>

			<?php
			/**
			 * Fires while outputing donation form, after the form.
			 *
			 * @since 1.0
			 *
			 * @param int   $form_id The form ID.
			 * @param array $args    An array of form arguments.
			 */
			do_action( 'give_post_form', $form->ID, $args );

		}
		?>

    </div><!--end #give-form-<?php echo absint( $form->ID ); ?>-->
	<?php

	/**
	 * Fires while outputing donation form, after the form wapper div.
	 *
	 * @since 1.0
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 */
	do_action( 'give_post_form_output', $form->ID, $args );

	$final_output = ob_get_clean();

	echo apply_filters( 'give_donate_form', $final_output, $args );
}

/**
 * Give Show Donation Form.
 *
 * Renders the Donation Form, hooks are provided to add to the checkout form.
 * The default Donation Form rendered displays a list of the enabled payment
 * gateways, a user registration form (if enable) and a credit card info form
 * if credit cards are enabled.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return string
 */
function give_show_purchase_form( $form_id ) {

	$payment_mode = give_get_chosen_gateway( $form_id );

	if ( ! isset( $form_id ) && isset( $_POST['give_form_id'] ) ) {
		$form_id = $_POST['give_form_id'];
	}

	/**
	 * Fire before donation form render.
	 *
	 * @since 1.7
	 */
	do_action( 'give_donation_form_top', $form_id );

	if ( give_can_checkout() && isset( $form_id ) ) {

		/**
		 * Fires while displaying donation form, before registration login.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_before_register_login', $form_id );

		/**
		 * Fire when register/login form fields render.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_register_login_fields', $form_id );

		/**
		 * Fire when credit card form fields render.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_before_cc_form', $form_id );

		// Load the credit card form and allow gateways to load their own if they wish.
		if ( has_action( 'give_' . $payment_mode . '_cc_form' ) ) {
			/**
			 * Fires while displaying donation form, credit card form fields for a given gateway.
			 *
			 * @since 1.0
			 *
			 * @param int $form_id The form ID.
			 */
			do_action( "give_{$payment_mode}_cc_form", $form_id );
		} else {
			/**
			 * Fires while displaying donation form, credit card form fields.
			 *
			 * @since 1.0
			 *
			 * @param int $form_id The form ID.
			 */
			do_action( 'give_cc_form', $form_id );
		}

		/**
		 * Fire after credit card form fields render.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_after_cc_form', $form_id );

	} else {
		/**
		 * Fire if user can not donate.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_no_access', $form_id );

	}

	/**
	 * Fire after donation form rendered.
	 *
	 * @since 1.7
	 */
	do_action( 'give_donation_form_bottom', $form_id );
}

add_action( 'give_donation_form', 'give_show_purchase_form' );

/**
 * Give Show Login/Register Form Fields.
 *
 * @since  1.4.1
 *
 * @param  int $form_id The form ID.
 *
 * @return void
 */
function give_show_register_login_fields( $form_id ) {

	$show_register_form = give_show_login_register_option( $form_id );

	if ( ( $show_register_form === 'registration' || ( $show_register_form === 'both' && ! isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) :
		?>
        <div id="give-checkout-login-register-<?php echo $form_id; ?>">
			<?php
			/**
			 * Fire if user registration form render.
			 *
			 * @since 1.7
			 */
			do_action( 'give_donation_form_register_fields', $form_id );
			?>
        </div>
		<?php
	elseif ( ( $show_register_form === 'login' || ( $show_register_form === 'both' && isset( $_GET['login'] ) ) ) && ! is_user_logged_in() ) :
		?>
        <div id="give-checkout-login-register-<?php echo $form_id; ?>">
			<?php
			/**
			 * Fire if user login form render.
			 *
			 * @since 1.7
			 */
			do_action( 'give_donation_form_login_fields', $form_id );
			?>
        </div>
		<?php
	endif;

	if ( ( ! isset( $_GET['login'] ) && is_user_logged_in() ) || ! isset( $show_register_form ) || 'none' === $show_register_form || 'login' === $show_register_form ) {
		/**
		 * Fire when user info render.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_after_user_info', $form_id );
	}
}

add_action( 'give_donation_form_register_login_fields', 'give_show_register_login_fields' );

/**
 * Donation Amount Field.
 *
 * Outputs the donation amount field that appears at the top of the donation forms. If the user has custom amount
 * enabled the field will output as a customizable input.
 *
 * @since  1.0
 *
 * @param  int   $form_id The form ID.
 * @param  array $args    An array of form arguments.
 *
 * @return void
 */
function give_output_donation_amount_top( $form_id = 0, $args = array() ) {

	$give_options        = give_get_settings();
	$variable_pricing    = give_has_variable_prices( $form_id );
	$allow_custom_amount = get_post_meta( $form_id, '_give_custom_amount', true );
	$currency_position   = isset( $give_options['currency_position'] ) ? $give_options['currency_position'] : 'before';
	$symbol              = give_currency_symbol( give_get_currency() );
	$currency_output     = '<span class="give-currency-symbol give-currency-position-' . $currency_position . '">' . $symbol . '</span>';
	$default_amount      = give_format_amount( give_get_default_form_amount( $form_id ) );
	$custom_amount_text  = get_post_meta( $form_id, '_give_custom_amount_text', true );

	/**
	 * Fires while displaying donation form, before donation level fields.
	 *
	 * @since 1.0
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 */
	do_action( 'give_before_donation_levels', $form_id, $args );

	//Set Price, No Custom Amount Allowed means hidden price field
	if ( ! give_is_setting_enabled( $allow_custom_amount ) ) {
		?>
        <label class="give-hidden" for="give-amount-hidden"><?php esc_html_e( 'Donation Amount:', 'give' ); ?></label>
        <input id="give-amount" class="give-amount-hidden" type="hidden" name="give-amount"
               value="<?php echo $default_amount; ?>" required aria-required="true"/>
        <div class="set-price give-donation-amount form-row-wide">
			<?php if ( $currency_position == 'before' ) {
				echo $currency_output;
			} ?>
            <span id="give-amount-text" class="give-text-input give-amount-top"><?php echo $default_amount; ?></span>
			<?php if ( $currency_position == 'after' ) {
				echo $currency_output;
			} ?>
        </div>
		<?php
	} else {
		//Custom Amount Allowed.
		?>
        <div class="give-total-wrap">
            <div class="give-donation-amount form-row-wide">
				<?php if ( $currency_position == 'before' ) {
					echo $currency_output;
				} ?>
                <label class="give-hidden" for="give-amount"><?php esc_html_e( 'Donation Amount:', 'give' ); ?></label>
                <input class="give-text-input give-amount-top" id="give-amount" name="give-amount" type="tel"
                       placeholder="" value="<?php echo $default_amount; ?>" autocomplete="off">
				<?php if ( $currency_position == 'after' ) {
					echo $currency_output;
				} ?>
            </div>
        </div>
	<?php }

	/**
	 * Fires while displaying donation form, after donation amounf field(s).
	 *
	 * @since 1.0
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 */
	do_action( 'give_after_donation_amount', $form_id, $args );

	//Custom Amount Text
	if ( ! $variable_pricing && give_is_setting_enabled( $allow_custom_amount ) && ! empty( $custom_amount_text ) ) { ?>
        <p class="give-custom-amount-text"><?php echo $custom_amount_text; ?></p>
	<?php }

	//Output Variable Pricing Levels.
	if ( $variable_pricing ) {
		give_output_levels( $form_id );
	}

	/**
	 * Fires while displaying donation form, after donation level fields.
	 *
	 * @since 1.0
	 *
	 * @param int   $form_id The form ID.
	 * @param array $args    An array of form arguments.
	 */
	do_action( 'give_after_donation_levels', $form_id, $args );
}

add_action( 'give_checkout_form_top', 'give_output_donation_amount_top', 10, 2 );

/**
 * Outputs the Donation Levels in various formats such as dropdown, radios, and buttons.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return string Donation levels.
 */
function give_output_levels( $form_id ) {

	//Get variable pricing.
	$prices             = apply_filters( 'give_form_variable_prices', give_get_variable_prices( $form_id ), $form_id );
	$display_style      = get_post_meta( $form_id, '_give_display_style', true );
	$custom_amount      = get_post_meta( $form_id, '_give_custom_amount', true );
	$custom_amount_text = get_post_meta( $form_id, '_give_custom_amount_text', true );
	if ( empty( $custom_amount_text ) ) {
		$custom_amount_text = esc_html__( 'Give a Custom Amount', 'give' );
	}

	$output  = '';
	$counter = 0;

	switch ( $display_style ) {
		case 'buttons':

			$output .= '<ul id="give-donation-level-button-wrap" class="give-donation-levels-wrap give-list-inline">';

			foreach ( $prices as $price ) {
				$counter ++;
				$level_text    = apply_filters( 'give_form_level_text', ! empty( $price['_give_text'] ) ? $price['_give_text'] : give_currency_filter( give_format_amount( $price['_give_amount'] ) ), $form_id, $price );
				$level_classes = apply_filters( 'give_form_level_classes', 'give-donation-level-btn give-btn give-btn-level-' . $counter . ' ' . ( ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) ? 'give-default-level' : '' ), $form_id, $price );

				$output .= '<li>';
				$output .= '<button type="button" data-price-id="' . $price['_give_id']['level_id'] . '" class=" ' . $level_classes . '" value="' . give_format_amount( $price['_give_amount'] ) . '">';
				$output .= $level_text;
				$output .= '</button>';
				$output .= '</li>';

			}

			//Custom Amount.
			if ( give_is_setting_enabled( $custom_amount ) && ! empty( $custom_amount_text ) ) {
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
				$level_text    = apply_filters( 'give_form_level_text', ! empty( $price['_give_text'] ) ? $price['_give_text'] : give_currency_filter( give_format_amount( $price['_give_amount'] ) ), $form_id, $price );
				$level_classes = apply_filters( 'give_form_level_classes', 'give-radio-input give-radio-input-level give-radio-level-' . $counter . ( ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) ? ' give-default-level' : '' ), $form_id, $price );

				$output .= '<li>';
				$output .= '<input type="radio" data-price-id="' . $price['_give_id']['level_id'] . '" class="' . $level_classes . '" name="give-radio-donation-level" id="give-radio-level-' . $counter . '" ' . ( ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) ? 'checked="checked"' : '' ) . ' value="' . give_format_amount( $price['_give_amount'] ) . '">';
				$output .= '<label for="give-radio-level-' . $counter . '">' . $level_text . '</label>';
				$output .= '</li>';

			}

			//Custom Amount.
			if ( give_is_setting_enabled( $custom_amount ) && ! empty( $custom_amount_text ) ) {
				$output .= '<li>';
				$output .= '<input type="radio" data-price-id="custom" class="give-radio-input give-radio-input-level give-radio-level-custom" name="give-radio-donation-level" id="give-radio-level-custom" value="custom">';
				$output .= '<label for="give-radio-level-custom">' . $custom_amount_text . '</label>';
				$output .= '</li>';
			}

			$output .= '</ul>';

			break;

		case 'dropdown':

			$output .= '<label for="give-donation-level" class="give-hidden">' . esc_html__( 'Choose Your Donation Amount', 'give' ) . ':</label>';
			$output .= '<select id="give-donation-level-' . $form_id . '" class="give-select give-select-level give-donation-levels-wrap">';

			//first loop through prices.
			foreach ( $prices as $price ) {
				$level_text    = apply_filters( 'give_form_level_text', ! empty( $price['_give_text'] ) ? $price['_give_text'] : give_currency_filter( give_format_amount( $price['_give_amount'] ) ), $form_id, $price );
				$level_classes = apply_filters( 'give_form_level_classes', 'give-donation-level-' . $form_id . ( ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) ? ' give-default-level' : '' ), $form_id, $price );

				$output .= '<option data-price-id="' . $price['_give_id']['level_id'] . '" class="' . $level_classes . '" ' . ( ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) ? 'selected="selected"' : '' ) . ' value="' . give_format_amount( $price['_give_amount'] ) . '">';
				$output .= $level_text;
				$output .= '</option>';

			}

			//Custom Amount.
			if ( give_is_setting_enabled( $custom_amount ) && ! empty( $custom_amount_text ) ) {
				$output .= '<option data-price-id="custom" class="give-donation-level-custom" value="custom">' . $custom_amount_text . '</option>';
			}

			$output .= '</select>';

			break;
	}

	echo apply_filters( 'give_form_level_output', $output, $form_id );
}

/**
 * Display Reveal & Lightbox Button.
 *
 * Outputs a button to reveal form fields.
 *
 * @since  1.0
 *
 * @param  int   $form_id The form ID.
 * @param  array $args    An array of form arguments.
 *
 * @return string Checkout button.
 */
function give_display_checkout_button( $form_id, $args ) {

	$display_option = ( isset( $args['display_style'] ) && ! empty( $args['display_style'] ) )
		? $args['display_style']
		: get_post_meta( $form_id, '_give_payment_display', true );

	if ( 'button' === $display_option ) {
		$display_option = 'modal';
	} elseif ( $display_option === 'onpage' ) {
		return '';
	}

	$display_label_field = get_post_meta( $form_id, '_give_reveal_label', true );
	$display_label       = ! empty( $args['continue_button_title'] ) ? $args['continue_button_title'] : ( ! empty( $display_label_field ) ? $display_label_field : esc_html__( 'Donate Now', 'give' ) );

	$output = '<button type="button" class="give-btn give-btn-' . $display_option . '">' . $display_label . '</button>';

	echo apply_filters( 'give_display_checkout_button', $output );
}

add_action( 'give_after_donation_levels', 'give_display_checkout_button', 10, 2 );

/**
 * Shows the User Info fields in the Personal Info box, more fields can be added via the hooks provided.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return void
 */
function give_user_info_fields( $form_id ) {
	// Get user info.
	$give_user_info = _give_get_prefill_form_field_values( $form_id );

	/**
	 * Fire before user personal information fields
	 *
	 * @since 1.7
	 */
	do_action( 'give_donation_form_before_personal_info', $form_id );
	?>
    <fieldset id="give_checkout_user_info">
        <legend><?php echo apply_filters( 'give_checkout_personal_info_text', esc_html__( 'Personal Info', 'give' ) ); ?></legend>
        <p id="give-first-name-wrap" class="form-row form-row-first form-row-responsive">
            <label class="give-label" for="give-first">
				<?php esc_html_e( 'First Name', 'give' ); ?>
				<?php if ( give_field_is_required( 'give_first', $form_id ) ) : ?>
                    <span class="give-required-indicator">*</span>
				<?php endif ?>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'We will use this to personalize your account experience.', 'give' ); ?>"></span>
            </label>
            <input
                    class="give-input required"
                    type="text"
                    name="give_first"
                    placeholder="<?php esc_attr_e( 'First Name', 'give' ); ?>"
                    id="give-first"
                    value="<?php echo isset( $give_user_info['give_first'] ) ? $give_user_info['give_first'] : ''; ?>"
				<?php echo( give_field_is_required( 'give_first', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
            />
        </p>

        <p id="give-last-name-wrap" class="form-row form-row-last form-row-responsive">
            <label class="give-label" for="give-last">
				<?php esc_html_e( 'Last Name', 'give' ); ?>
				<?php if ( give_field_is_required( 'give_last', $form_id ) ) : ?>
                    <span class="give-required-indicator">*</span>
				<?php endif ?>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'We will use this as well to personalize your account experience.', 'give' ); ?>"></span>
            </label>

            <input
                    class="give-input<?php echo( give_field_is_required( 'give_last', $form_id ) ? ' required' : '' ); ?>"
                    type="text"
                    name="give_last"
                    id="give-last"
                    placeholder="<?php esc_attr_e( 'Last Name', 'give' ); ?>"
                    value="<?php echo isset( $give_user_info['give_last'] ) ? $give_user_info['give_last'] : ''; ?>"
				<?php echo( give_field_is_required( 'give_last', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
            />
        </p>

		<?php
		/**
		 * Fire before user email field
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_before_email', $form_id );
		?>
        <p id="give-email-wrap" class="form-row form-row-wide">
            <label class="give-label" for="give-email">
				<?php esc_html_e( 'Email Address', 'give' ); ?>
				<?php if ( give_field_is_required( 'give_email', $form_id ) ) { ?>
                    <span class="give-required-indicator">*</span>
				<?php } ?>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'We will send the donation receipt to this address.', 'give' ); ?>"></span>
            </label>

            <input
                    class="give-input required"
                    type="email"
                    name="give_email"
                    placeholder="<?php esc_attr_e( 'Email Address', 'give' ); ?>"
                    id="give-email"
                    value="<?php echo isset( $give_user_info['give_email'] ) ? $give_user_info['give_email'] : ''; ?>"
				<?php echo( give_field_is_required( 'give_email', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
            />

        </p>
		<?php
		/**
		 * Fire after user email field
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_after_email', $form_id );

		/**
		 * Fire after personal email field
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_user_info', $form_id );
		?>
    </fieldset>
	<?php
	/**
	 * Fire after user personal information fields
	 *
	 * @since 1.7
	 */
	do_action( 'give_donation_form_after_personal_info', $form_id );
}

add_action( 'give_donation_form_after_user_info', 'give_user_info_fields' );
add_action( 'give_register_fields_before', 'give_user_info_fields' );

/**
 * Renders the credit card info form.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return void
 */
function give_get_cc_form( $form_id ) {

	ob_start();

	/**
	 * Fires while rendering credit card info form, before the fields.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id The form ID.
	 */
	do_action( 'give_before_cc_fields', $form_id );
	?>
    <fieldset id="give_cc_fields-<?php echo $form_id ?>" class="give-do-validate">
        <legend><?php echo apply_filters( 'give_credit_card_fieldset_heading', esc_html__( 'Credit Card Info', 'give' ) ); ?></legend>
		<?php if ( is_ssl() ) : ?>
            <div id="give_secure_site_wrapper-<?php echo $form_id ?>">
                <span class="give-icon padlock"></span>
                <span><?php esc_html_e( 'This is a secure SSL encrypted payment.', 'give' ); ?></span>
            </div>
		<?php endif; ?>
        <p id="give-card-number-wrap-<?php echo $form_id ?>" class="form-row form-row-two-thirds form-row-responsive">
            <label for="card_number-<?php echo $form_id ?>" class="give-label">
				<?php esc_html_e( 'Card Number', 'give' ); ?>
                <span class="give-required-indicator">*</span>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The (typically) 16 digits on the front of your credit card.', 'give' ); ?>"></span>
                <span class="card-type"></span>
            </label>

            <input type="tel" autocomplete="off" name="card_number" id="card_number-<?php echo $form_id ?>"
                   class="card-number give-input required" placeholder="<?php esc_attr_e( 'Card number', 'give' ); ?>"
                   required aria-required="true"/>
        </p>

        <p id="give-card-cvc-wrap-<?php echo $form_id ?>" class="form-row form-row-one-third form-row-responsive">
            <label for="card_cvc-<?php echo $form_id ?>" class="give-label">
				<?php esc_html_e( 'CVC', 'give' ); ?>
                <span class="give-required-indicator">*</span>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The 3 digit (back) or 4 digit (front) value on your card.', 'give' ); ?>"></span>
            </label>

            <input type="tel" size="4" autocomplete="off" name="card_cvc" id="card_cvc-<?php echo $form_id ?>"
                   class="card-cvc give-input required" placeholder="<?php esc_attr_e( 'Security code', 'give' ); ?>"
                   required aria-required="true"/>
        </p>

        <p id="give-card-name-wrap-<?php echo $form_id ?>" class="form-row form-row-two-thirds form-row-responsive">
            <label for="card_name-<?php echo $form_id ?>" class="give-label">
				<?php esc_html_e( 'Name on the Card', 'give' ); ?>
                <span class="give-required-indicator">*</span>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The name printed on the front of your credit card.', 'give' ); ?>"></span>
            </label>

            <input type="text" autocomplete="off" name="card_name" id="card_name-<?php echo $form_id ?>"
                   class="card-name give-input required" placeholder="<?php esc_attr_e( 'Card name', 'give' ); ?>"
                   required aria-required="true"/>
        </p>
		<?php
		/**
		 * Fires while rendering credit card info form, before expiration fields.
		 *
		 * @since 1.0
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_before_cc_expiration' );
		?>
        <p class="card-expiration form-row form-row-one-third form-row-responsive">
            <label for="card_expiry-<?php echo $form_id ?>" class="give-label">
				<?php esc_html_e( 'Expiration', 'give' ); ?>
                <span class="give-required-indicator">*</span>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The date your credit card expires, typically on the front of the card.', 'give' ); ?>"></span>
            </label>

            <input type="hidden" id="card_exp_month-<?php echo $form_id ?>" name="card_exp_month"
                   class="card-expiry-month"/>
            <input type="hidden" id="card_exp_year-<?php echo $form_id ?>" name="card_exp_year"
                   class="card-expiry-year"/>

            <input type="tel" autocomplete="off" name="card_expiry" id="card_expiry-<?php echo $form_id ?>"
                   class="card-expiry give-input required" placeholder="<?php esc_attr_e( 'MM / YY', 'give' ); ?>"
                   required aria-required="true"/>
        </p>
		<?php
		/**
		 * Fires while rendering credit card info form, after expiration fields.
		 *
		 * @since 1.0
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_after_cc_expiration', $form_id );
		?>
    </fieldset>
	<?php
	/**
	 * Fires while rendering credit card info form, before the fields.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id The form ID.
	 */
	do_action( 'give_after_cc_fields', $form_id );

	echo ob_get_clean();
}

add_action( 'give_cc_form', 'give_get_cc_form' );

/**
 * Outputs the default credit card address fields.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return void
 */
function give_default_cc_address_fields( $form_id ) {
	// Get user info.
	$give_user_info = _give_get_prefill_form_field_values( $form_id );

	$logged_in = is_user_logged_in();

	if ( $logged_in ) {
		$user_address = get_user_meta( get_current_user_id(), '_give_user_address', true );
	}
	$line1 = $logged_in && ! empty( $user_address['line1'] ) ? $user_address['line1'] : '';
	$line2 = $logged_in && ! empty( $user_address['line2'] ) ? $user_address['line2'] : '';
	$city  = $logged_in && ! empty( $user_address['city'] ) ? $user_address['city'] : '';
	$zip   = $logged_in && ! empty( $user_address['zip'] ) ? $user_address['zip'] : '';

	ob_start();
	?>
    <fieldset id="give_cc_address" class="cc-address">
        <legend><?php echo apply_filters( 'give_billing_details_fieldset_heading', esc_html__( 'Billing Details', 'give' ) ); ?></legend>
		<?php
		/**
		 * Fires while rendering credit card billing form, before address fields.
		 *
		 * @since 1.0
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_cc_billing_top' );
		?>
        <p id="give-card-address-wrap" class="form-row form-row-wide">
            <label for="card_address" class="give-label">
				<?php esc_html_e( 'Address 1', 'give' ); ?>
				<?php
				if ( give_field_is_required( 'card_address', $form_id ) ) : ?>
                    <span class="give-required-indicator">*</span>
				<?php endif; ?>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The primary billing address for your credit card.', 'give' ); ?>"></span>
            </label>

            <input
                    type="text"
                    id="card_address"
                    name="card_address"
                    class="card-address give-input<?php echo( give_field_is_required( 'card_address', $form_id ) ? ' required' : '' ); ?>"
                    placeholder="<?php esc_attr_e( 'Address line 1', 'give' ); ?>"
                    value="<?php echo isset( $give_user_info['card_address'] ) ? $give_user_info['card_address'] : ''; ?>"
				<?php echo( give_field_is_required( 'card_address', $form_id ) ? '  required aria-required="true" ' : '' ); ?>
            />
        </p>

        <p id="give-card-address-2-wrap" class="form-row form-row-wide">
            <label for="card_address_2" class="give-label">
				<?php esc_html_e( 'Address 2', 'give' ); ?>
				<?php if ( give_field_is_required( 'card_address_2', $form_id ) ) : ?>
                    <span class="give-required-indicator">*</span>
				<?php endif; ?>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( '(optional) The suite, apt no, PO box, etc, associated with your billing address.', 'give' ); ?>"></span>
            </label>

            <input
                    type="text"
                    id="card_address_2"
                    name="card_address_2"
                    class="card-address-2 give-input<?php echo( give_field_is_required( 'card_address_2', $form_id ) ? ' required' : '' ); ?>"
                    placeholder="<?php esc_attr_e( 'Address line 2', 'give' ); ?>"
                    value="<?php echo isset( $give_user_info['card_address_2'] ) ? $give_user_info['card_address_2'] : ''; ?>"
				<?php echo( give_field_is_required( 'card_address_2', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
            />
        </p>

        <p id="give-card-city-wrap" class="form-row form-row-first form-row-responsive">
            <label for="card_city" class="give-label">
				<?php esc_html_e( 'City', 'give' ); ?>
				<?php if ( give_field_is_required( 'card_city', $form_id ) ) : ?>
                    <span class="give-required-indicator">*</span>
				<?php endif; ?>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The city for your billing address.', 'give' ); ?>"></span>
            </label>
            <input
                    type="text"
                    id="card_city"
                    name="card_city"
                    class="card-city give-input<?php echo( give_field_is_required( 'card_city', $form_id ) ? ' required' : '' ); ?>"
                    placeholder="<?php esc_attr_e( 'City', 'give' ); ?>"
                    value="<?php echo isset( $give_user_info['card_city'] ) ? $give_user_info['card_city'] : ''; ?>"
				<?php echo( give_field_is_required( 'card_city', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
            />
        </p>

        <p id="give-card-zip-wrap" class="form-row form-row-last form-row-responsive">
            <label for="card_zip" class="give-label">
				<?php esc_html_e( 'Zip / Postal Code', 'give' ); ?>
				<?php if ( give_field_is_required( 'card_zip', $form_id ) ) : ?>
                    <span class="give-required-indicator">*</span>
				<?php endif; ?>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The zip or postal code for your billing address.', 'give' ); ?>"></span>
            </label>

            <input
                    type="text"
                    size="4"
                    id="card_zip"
                    name="card_zip"
                    class="card-zip give-input<?php echo( give_field_is_required( 'card_zip', $form_id ) ? ' required' : '' ); ?>"
                    placeholder="<?php esc_attr_e( 'Zip / Postal Code', 'give' ); ?>"
                    value="<?php echo isset( $give_user_info['card_zip'] ) ? $give_user_info['card_zip'] : ''; ?>"
				<?php echo( give_field_is_required( 'card_zip', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
            />
        </p>

        <p id="give-card-country-wrap" class="form-row form-row-first form-row-responsive">
            <label for="billing_country" class="give-label">
				<?php esc_html_e( 'Country', 'give' ); ?>
				<?php if ( give_field_is_required( 'billing_country', $form_id ) ) : ?>
                    <span class="give-required-indicator">*</span>
				<?php endif; ?>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The country for your billing address.', 'give' ); ?>"></span>
            </label>

            <select
                    name="billing_country"
                    id="billing_country"
                    class="billing-country billing_country give-select<?php echo( give_field_is_required( 'billing_country', $form_id ) ? ' required' : '' ); ?>"
				<?php echo( give_field_is_required( 'billing_country', $form_id ) ? ' required aria-required="true" ' : '' ); ?>
            >
				<?php

				$selected_country = give_get_country();

				if ( ! empty( $give_user_info['billing_country'] ) && '*' !== $give_user_info['billing_country'] ) {
					$selected_country = $give_user_info['billing_country'];
				}

				$countries = give_get_country_list();
				foreach ( $countries as $country_code => $country ) {
					echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
				}
				?>
            </select>
        </p>

        <p id="give-card-state-wrap" class="form-row form-row-last form-row-responsive">
            <label for="card_state" class="give-label">
				<?php esc_html_e( 'State / Province', 'give' ); ?>
				<?php if ( give_field_is_required( 'card_state', $form_id ) ) : ?>
                    <span class="give-required-indicator">*</span>
				<?php endif; ?>
                <span class="give-tooltip give-icon give-icon-question"
                      data-tooltip="<?php esc_attr_e( 'The state or province for your billing address.', 'give' ); ?>"></span>
            </label>

			<?php
			$selected_state = give_get_state();
			$states         = give_get_states( $selected_country );

			if ( ! empty( $give_user_info['card_state'] ) ) {
				$selected_state = $give_user_info['card_state'];
			}

			if ( ! empty( $states ) ) : ?>
                <select
                        name="card_state"
                        id="card_state"
                        class="card_state give-select<?php echo( give_field_is_required( 'card_state', $form_id ) ? ' required' : '' ); ?>"
					<?php echo( give_field_is_required( 'card_state', $form_id ) ? ' required aria-required="true" ' : '' ); ?>>
					<?php
					foreach ( $states as $state_code => $state ) {
						echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
					}
					?>
                </select>
			<?php else : ?>
                <input type="text" size="6" name="card_state" id="card_state" class="card_state give-input"
                       placeholder="<?php esc_attr_e( 'State / Province', 'give' ); ?>"/>
			<?php endif; ?>
        </p>
		<?php
		/**
		 * Fires while rendering credit card billing form, after address fields.
		 *
		 * @since 1.0
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_cc_billing_bottom' );
		?>
    </fieldset>
	<?php
	echo ob_get_clean();
}

add_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );


/**
 * Renders the user registration fields. If the user is logged in, a login form is displayed other a registration form
 * is provided for the user to create an account.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return string
 */
function give_get_register_fields( $form_id ) {

	global $user_ID;

	if ( is_user_logged_in() ) {
		$user_data = get_userdata( $user_ID );
	}

	$show_register_form = give_show_login_register_option( $form_id );

	ob_start(); ?>
    <fieldset id="give-register-fields-<?php echo $form_id; ?>">

		<?php if ( $show_register_form == 'both' ) { ?>
            <div class="give-login-account-wrap">
                <p class="give-login-message"><?php esc_html_e( 'Already have an account?', 'give' ); ?>&nbsp;
                    <a href="<?php echo esc_url( add_query_arg( 'login', 1 ) ); ?>" class="give-checkout-login"
                       data-action="give_checkout_login"><?php esc_html_e( 'Login', 'give' ); ?></a>
                </p>
                <p class="give-loading-text">
                    <span class="give-loading-animation"></span>
                </p>
            </div>
		<?php } ?>

		<?php
		/**
		 * Fires while rendering user registration form, before registration fields.
		 *
		 * @since 1.0
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_register_fields_before', $form_id );
		?>

        <fieldset id="give-register-account-fields-<?php echo $form_id; ?>">
            <legend>
				<?php
				echo apply_filters( 'give_create_account_fieldset_heading', esc_html__( 'Create an account', 'give' ) );
				if ( ! give_logged_in_only( $form_id ) ) {
					echo ' <span class="sub-text">' . esc_html__( '(optional)', 'give' ) . '</span>';
				}
				?>
            </legend>
			<?php
			/**
			 * Fires while rendering user registration form, before account fields.
			 *
			 * @since 1.0
			 *
			 * @param int $form_id The form ID.
			 */
			do_action( 'give_register_account_fields_before', $form_id );
			?>
            <div id="give-user-login-wrap-<?php echo $form_id; ?>"
                 class="form-row form-row-one-third form-row-first form-row-responsive">
                <label for="give-user-login-<?php echo $form_id; ?>">
					<?php esc_html_e( 'Username', 'give' ); ?>
					<?php if ( give_logged_in_only( $form_id ) ) { ?>
                        <span class="give-required-indicator">*</span>
					<?php } ?>
                    <span class="give-tooltip give-icon give-icon-question"
                          data-tooltip="<?php esc_attr_e( 'The username you will use to log into your account.', 'give' ); ?>"></span>
                </label>

                <input name="give_user_login" id="give-user-login-<?php echo $form_id; ?>" class="give-input"
                       type="text"
                       placeholder="<?php esc_attr_e( 'Username', 'give' ); ?>"<?php echo ( give_logged_in_only( $form_id ) ) ? ' required aria-required="true" ' : ''; ?>/>
            </div>

            <div id="give-user-pass-wrap-<?php echo $form_id; ?>"
                 class="form-row form-row-one-third form-row-responsive">
                <label for="give-user-pass-<?php echo $form_id; ?>">
					<?php esc_html_e( 'Password', 'give' ); ?>
					<?php if ( give_logged_in_only( $form_id ) ) { ?>
                        <span class="give-required-indicator">*</span>
					<?php } ?>
                    <span class="give-tooltip give-icon give-icon-question"
                          data-tooltip="<?php esc_attr_e( 'The password used to access your account.', 'give' ); ?>"></span>
                </label>

                <input name="give_user_pass" id="give-user-pass-<?php echo $form_id; ?>" class="give-input"
                       placeholder="<?php esc_attr_e( 'Password', 'give' ); ?>"
                       type="password"<?php echo ( give_logged_in_only( $form_id ) ) ? ' required aria-required="true" ' : ''; ?>/>
            </div>

            <div id="give-user-pass-confirm-wrap-<?php echo $form_id; ?>"
                 class="give-register-password form-row form-row-one-third form-row-responsive">
                <label for="give-user-pass-confirm-<?php echo $form_id; ?>">
					<?php esc_html_e( 'Confirm PW', 'give' ); ?>
					<?php if ( give_logged_in_only( $form_id ) ) { ?>
                        <span class="give-required-indicator">*</span>
					<?php } ?>
                    <span class="give-tooltip give-icon give-icon-question"
                          data-tooltip="<?php esc_attr_e( 'Please retype your password to confirm.', 'give' ); ?>"></span>
                </label>

                <input name="give_user_pass_confirm" id="give-user-pass-confirm-<?php echo $form_id; ?>"
                       class="give-input" placeholder="<?php esc_attr_e( 'Confirm password', 'give' ); ?>"
                       type="password"<?php echo ( give_logged_in_only( $form_id ) ) ? ' required aria-required="true" ' : ''; ?>/>
            </div>
			<?php
			/**
			 * Fires while rendering user registration form, after account fields.
			 *
			 * @since 1.0
			 *
			 * @param int $form_id The form ID.
			 */
			do_action( 'give_register_account_fields_after', $form_id );
			?>
        </fieldset>

		<?php
		/**
		 * Fires while rendering user registration form, after registration fields.
		 *
		 * @since 1.0
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_register_fields_after', $form_id );
		?>

        <input type="hidden" name="give-purchase-var" value="needs-to-register"/>

		<?php
		/**
		 * Fire after register or login form render
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_user_info', $form_id );
		?>

    </fieldset>
	<?php
	echo ob_get_clean();
}

add_action( 'give_donation_form_register_fields', 'give_get_register_fields' );

/**
 * Gets the login fields for the login form on the checkout. This function hooks
 * on the give_donation_form_login_fields to display the login form if a user already
 * had an account.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return string
 */
function give_get_login_fields( $form_id ) {

	$form_id            = isset( $_POST['form_id'] ) ? $_POST['form_id'] : $form_id;
	$show_register_form = give_show_login_register_option( $form_id );

	ob_start();
	?>
    <fieldset id="give-login-fields-<?php echo $form_id; ?>">
        <legend><?php echo apply_filters( 'give_account_login_fieldset_heading', esc_html__( 'Login to Your Account', 'give' ) );
			if ( ! give_logged_in_only( $form_id ) ) {
				echo ' <span class="sub-text">' . esc_html__( '(optional)', 'give' ) . '</span>';
			} ?>
        </legend>
		<?php if ( $show_register_form == 'both' ) { ?>
            <p class="give-new-account-link">
				<?php esc_html_e( 'Need to create an account?', 'give' ); ?>&nbsp;
                <a href="<?php echo remove_query_arg( 'login' ); ?>" class="give-checkout-register-cancel"
                   data-action="give_checkout_register">
					<?php esc_html_e( 'Register', 'give' );
					if ( ! give_logged_in_only( $form_id ) ) {
						echo ' ' . esc_html__( 'or checkout as a guest &raquo;', 'give' );
					} ?>
                </a>
            </p>
            <p class="give-loading-text">
                <span class="give-loading-animation"></span>
            </p>
		<?php } ?>
		<?php
		/**
		 * Fires while rendering checkout login form, before the fields.
		 *
		 * @since 1.0
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_checkout_login_fields_before', $form_id );
		?>
        <div id="give-user-login-wrap-<?php echo $form_id; ?>" class="form-row form-row-first form-row-responsive">
            <label class="give-label" for="give-user-login-<?php echo $form_id; ?>">
				<?php esc_html_e( 'Username', 'give' ); ?>
				<?php if ( give_logged_in_only( $form_id ) ) { ?>
                    <span class="give-required-indicator">*</span>
				<?php } ?>
            </label>

            <input class="give-input<?php echo ( give_logged_in_only( $form_id ) ) ? ' required' : ''; ?>" type="text"
                   name="give_user_login" id="give-user-login-<?php echo $form_id; ?>" value=""
                   placeholder="<?php esc_attr_e( 'Your username', 'give' ); ?>"<?php echo ( give_logged_in_only( $form_id ) ) ? ' required aria-required="true" ' : ''; ?>/>
        </div>

        <div id="give-user-pass-wrap-<?php echo $form_id; ?>"
             class="give_login_password form-row form-row-last form-row-responsive">
            <label class="give-label" for="give-user-pass-<?php echo $form_id; ?>">
				<?php esc_html_e( 'Password', 'give' ); ?>
				<?php if ( give_logged_in_only( $form_id ) ) { ?>
                    <span class="give-required-indicator">*</span>
				<?php } ?>
            </label>
            <input class="give-input<?php echo ( give_logged_in_only( $form_id ) ) ? ' required' : ''; ?>"
                   type="password" name="give_user_pass" id="give-user-pass-<?php echo $form_id; ?>"
                   placeholder="<?php esc_attr_e( 'Your password', 'give' ); ?>"<?php echo ( give_logged_in_only( $form_id ) ) ? ' required aria-required="true" ' : ''; ?>/>
            <input type="hidden" name="give-purchase-var" value="needs-to-login"/>
        </div>

        <div id="give-forgot-password-wrap-<?php echo $form_id; ?>" class="give_login_forgot_password">
			 <span class="give-forgot-password ">
				 <a href="<?php echo wp_lostpassword_url() ?>"
                    target="_blank"><?php esc_html_e( 'Reset Password', 'give' ) ?></a>
			 </span>
        </div>

        <div id="give-user-login-submit-<?php echo $form_id; ?>" class="give-clearfix">
            <input type="submit" class="give-submit give-btn button" name="give_login_submit"
                   value="<?php esc_attr_e( 'Login', 'give' ); ?>"/>
			<?php if ( $show_register_form !== 'login' ) { ?>
                <input type="button" data-action="give_cancel_login"
                       class="give-cancel-login give-checkout-register-cancel give-btn button" name="give_login_cancel"
                       value="<?php esc_attr_e( 'Cancel', 'give' ); ?>"/>
			<?php } ?>
            <span class="give-loading-animation"></span>
        </div>
		<?php
		/**
		 * Fires while rendering checkout login form, after the fields.
		 *
		 * @since 1.0
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_checkout_login_fields_after', $form_id );
		?>
    </fieldset><!--end #give-login-fields-->
	<?php
	echo ob_get_clean();
}

add_action( 'give_donation_form_login_fields', 'give_get_login_fields', 10, 1 );

/**
 * Payment Mode Select.
 *
 * Renders the payment mode form by getting all the enabled payment gateways and
 * outputting them as radio buttons for the user to choose the payment gateway. If
 * a default payment gateway has been chosen from the Give Settings, it will be
 * automatically selected.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return void
 */
function give_payment_mode_select( $form_id ) {

	$gateways = give_get_enabled_payment_gateways( $form_id );

	/**
	 * Fires while selecting payment gateways, before the fields.
	 *
	 * @since 1.7
	 *
	 * @param int $form_id The form ID.
	 */
	do_action( 'give_payment_mode_top', $form_id );
	?>

    <fieldset id="give-payment-mode-select" <?php if ( count( $gateways ) <= 1 ) {
		echo 'style="display: none;"';
	} ?>>
		<?php
		/**
		 * Fires while selecting payment gateways, before the wrap div.
		 *
		 * @since 1.7
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_payment_mode_before_gateways_wrap' );
		?>
        <legend
                class="give-payment-mode-label"><?php echo apply_filters( 'give_checkout_payment_method_text', esc_html__( 'Select Payment Method', 'give' ) ); ?>
            <span class="give-loading-text"><span
                        class="give-loading-animation"></span>
            </span>
        </legend>

        <div id="give-payment-mode-wrap">
			<?php
			/**
			 * Fires while selecting payment gateways, befire the gateways list.
			 *
			 * @since 1.7
			 */
			do_action( 'give_payment_mode_before_gateways' )
			?>
            <ul id="give-gateway-radio-list">
				<?php
				/**
				 * Loop through the active payment gateways.
				 */
				$selected_gateway  = give_get_chosen_gateway( $form_id );

				foreach ( $gateways as $gateway_id => $gateway ) :
					//Determine the default gateway.
					$checked = checked( $gateway_id, $selected_gateway, false );
					$checked_class = $checked ? ' class="give-gateway-option-selected"' : ''; ?>
                    <li<?php echo $checked_class ?>>
                        <input type="radio" name="payment-mode" class="give-gateway"
                               id="give-gateway-<?php echo esc_attr( $gateway_id ) . '-' . $form_id; ?>"
                               value="<?php echo esc_attr( $gateway_id ); ?>"<?php echo $checked; ?>>
                        <label for="give-gateway-<?php echo esc_attr( $gateway_id ) . '-' . $form_id; ?>"
                               class="give-gateway-option"
                               id="give-gateway-option-<?php echo esc_attr( $gateway_id ); ?>"> <?php echo esc_html( $gateway['checkout_label'] ); ?></label>
                    </li>
					<?php
				endforeach;
				?>
            </ul>
			<?php
			/**
			 * Fires while selecting payment gateways, before the gateways list.
			 *
			 * @since 1.7
			 */
			do_action( 'give_payment_mode_after_gateways' );
			?>
        </div>
		<?php
		/**
		 * Fires while selecting payment gateways, after the wrap div.
		 *
		 * @since 1.7
		 *
		 * @param int $form_id The form ID.
		 */
		do_action( 'give_payment_mode_after_gateways_wrap' );
		?>
    </fieldset>

	<?php
	/**
	 * Fires while selecting payment gateways, after the fields.
	 *
	 * @since 1.7
	 *
	 * @param int $form_id The form ID.
	 */
	do_action( 'give_payment_mode_bottom', $form_id );
	?>

    <div id="give_purchase_form_wrap">

		<?php
		/**
		 * Fire after payment field render.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form', $form_id );
		?>

    </div>

	<?php
	/**
	 * Fire after donation form render.
	 *
	 * @since 1.7
	 */
	do_action( 'give_donation_form_wrap_bottom', $form_id );
}

add_action( 'give_payment_mode_select', 'give_payment_mode_select' );

/**
 * Renders the Checkout Agree to Terms, this displays a checkbox for users to
 * agree the T&Cs set in the Give Settings. This is only displayed if T&Cs are
 * set in the Give Settings.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return bool
 */
function give_terms_agreement( $form_id ) {
	$form_option = get_post_meta( $form_id, '_give_terms_option', true );

	// Bailout if per form and global term and conditions is not setup.
	if (
		give_is_setting_enabled( $form_option, 'global' )
		&& give_is_setting_enabled( give_get_option( 'terms' ) )
	) {
		$label         = give_get_option( 'agree_to_terms_label', esc_html__( 'Agree to Terms?', 'give' ) );
		$terms         = $terms = give_get_option( 'agreement_text', '' );
		$edit_term_url = admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=display&section=term-and-conditions' );

	} elseif ( give_is_setting_enabled( $form_option ) ) {
		$label         = ( $label = get_post_meta( $form_id, '_give_agree_label', true ) ) ? stripslashes( $label ) : esc_html__( 'Agree to Terms?', 'give' );
		$terms         = get_post_meta( $form_id, '_give_agree_text', true );
		$edit_term_url = admin_url( 'post.php?post=' . $form_id . '&action=edit#form_terms_options' );

	} else {
		return false;
	}

	// Bailout: Check if term and conditions text is empty or not.
	if ( empty( $terms ) ) {
		if ( is_user_logged_in() && current_user_can( 'edit_give_forms' ) ) {
			echo sprintf( __( 'Please enter valid terms and conditions in <a href="%s">this form\'s settings</a>.', 'give' ), $edit_term_url );
		}

		return false;
	}

	?>
    <fieldset id="give_terms_agreement">
        <legend><?php echo apply_filters( 'give_terms_agreement_text', esc_html__( 'Terms', 'give' ) ); ?></legend>
        <div id="give_terms" class="give_terms-<?php echo $form_id; ?>" style="display:none;">
			<?php
			/**
			 * Fires while rendering terms of agreement, before the fields.
			 *
			 * @since 1.0
			 */
			do_action( 'give_before_terms' );

			echo wpautop( stripslashes( $terms ) );
			/**
			 * Fires while rendering terms of agreement, after the fields.
			 *
			 * @since 1.0
			 */
			do_action( 'give_after_terms' );
			?>
        </div>
        <div id="give_show_terms">
            <a href="#" class="give_terms_links give_terms_links-<?php echo $form_id; ?>" role="button"
               aria-controls="give_terms"><?php esc_html_e( 'Show Terms', 'give' ); ?></a>
            <a href="#" class="give_terms_links give_terms_links-<?php echo $form_id; ?>" role="button"
               aria-controls="give_terms" style="display:none;"><?php esc_html_e( 'Hide Terms', 'give' ); ?></a>
        </div>

        <input name="give_agree_to_terms" class="required" type="checkbox"
               id="give_agree_to_terms-<?php echo $form_id; ?>" value="1" required aria-required="true"/>
        <label for="give_agree_to_terms-<?php echo $form_id; ?>"><?php echo $label; ?></label>

    </fieldset>
	<?php
}

add_action( 'give_donation_form_after_cc_form', 'give_terms_agreement', 8888, 1 );

/**
 * Checkout Final Total.
 *
 * Shows the final donation total at the bottom of the checkout page.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return void
 */
function give_checkout_final_total( $form_id ) {

	if ( isset( $_POST['give_total'] ) ) {
		$total = apply_filters( 'give_donation_total', $_POST['give_total'] );
	} else {
		//default total.
		$total = give_get_default_form_amount( $form_id );
	}
	//Only proceed if give_total available.
	if ( empty( $total ) ) {
		return;
	}
	?>
    <p id="give-final-total-wrap" class="form-wrap ">
		<span
                class="give-donation-total-label"><?php echo apply_filters( 'give_donation_total_label', esc_html__( 'Donation Total:', 'give' ) ); ?></span>
        <span class="give-final-total-amount"
              data-total="<?php echo give_format_amount( $total ); ?>"><?php echo give_currency_filter( give_format_amount( $total ) ); ?></span>
    </p>
	<?php
}

add_action( 'give_donation_form_before_submit', 'give_checkout_final_total', 999 );

/**
 * Renders the Checkout Submit section.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return void
 */
function give_checkout_submit( $form_id ) {
	?>
    <fieldset id="give_purchase_submit">
		<?php
		/**
		 * Fire before donation form submit.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_before_submit', $form_id );

		give_checkout_hidden_fields( $form_id );

		echo give_checkout_button_purchase( $form_id );

		/**
		 * Fire after donation form submit.
		 *
		 * @since 1.7
		 */
		do_action( 'give_donation_form_after_submit', $form_id );
		?>
    </fieldset>
	<?php
}

add_action( 'give_donation_form_after_cc_form', 'give_checkout_submit', 9999 );

/**
 * Give Checkout Button.
 *
 * Renders the button on the Checkout.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return string
 */
function give_checkout_button_purchase( $form_id ) {

	$display_label_field = get_post_meta( $form_id, '_give_checkout_label', true );
	$display_label       = ( ! empty( $display_label_field ) ? $display_label_field : esc_html__( 'Donate Now', 'give' ) );
	ob_start(); ?>
    <div class="give-submit-button-wrap give-clearfix">
        <input type="submit" class="give-submit give-btn" id="give-purchase-button" name="give-purchase"
               value="<?php echo $display_label; ?>"/>
        <span class="give-loading-animation"></span>
    </div>
	<?php
	return apply_filters( 'give_checkout_button_purchase', ob_get_clean(), $form_id );
}

/**
 * Give Agree to Terms.
 *
 * Outputs the JavaScript code for the Agree to Terms section to toggle the T&Cs text.
 *
 * @since  1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return void
 */
function give_agree_to_terms_js( $form_id ) {

	$form_option = get_post_meta( $form_id, '_give_terms_option', true );

	if ( give_is_setting_enabled( $form_option, array( 'enabled', 'global' ) ) ) {
		?>
        <script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('body').on('click', '.give_terms_links-<?php echo $form_id;?>', function (e) {
					e.preventDefault();
					$('.give_terms-<?php echo $form_id;?>').slideToggle();
					$('.give_terms_links-<?php echo $form_id;?>').toggle();
					return false;
				});
			});
        </script>
		<?php
	}
}

add_action( 'give_checkout_form_top', 'give_agree_to_terms_js', 10, 2 );

/**
 * Show Give Goals.
 *
 * @since  1.0
 * @since  1.6   Add template for Give Goals Shortcode.
 *               More info is on https://github.com/WordImpress/Give/issues/411
 *
 * @param  int   $form_id The form ID.
 * @param  array $args    An array of form arguments.
 *
 * @return mixed
 */
function give_show_goal_progress( $form_id, $args ) {

	ob_start();
	give_get_template( 'shortcode-goal', array( 'form_id' => $form_id, 'args' => $args ) );

	echo apply_filters( 'give_goal_output', ob_get_clean() );

	return true;
}

add_action( 'give_pre_form', 'give_show_goal_progress', 10, 2 );


/**
 * Get form content position.
 *
 * @since  1.8
 *
 * @param  $form_id
 * @param  $args
 *
 * @return mixed|string
 */
function give_get_form_content_placement( $form_id, $args ) {
	$show_content = '';

	if ( isset( $args['show_content'] ) && ! empty( $args['show_content'] ) ) {
		// Content positions.
		$content_placement = array(
			'above' => 'give_pre_form',
			'below' => 'give_post_form',
		);

		// Check if content position already decoded.
		if ( in_array( $args['show_content'], $content_placement ) ) {
			return $args['show_content'];
		}

		$show_content = ( 'none' !== $args['show_content'] ? $content_placement[ $args['show_content'] ] : '' );

	} elseif ( give_is_setting_enabled( get_post_meta( $form_id, '_give_display_content', true ) ) ) {
		$show_content = get_post_meta( $form_id, '_give_content_placement', true );

	} elseif ( 'none' !== get_post_meta( $form_id, '_give_content_option', true ) ) {
		// Backward compatibility for _give_content_option for v18.
		$show_content = get_post_meta( $form_id, '_give_content_option', true );
	}

	return $show_content;
}

/**
 * Adds Actions to Render Form Content.
 *
 * @since  1.0
 *
 * @param  int   $form_id The form ID.
 * @param  array $args    An array of form arguments.
 *
 * @return void|bool
 */
function give_form_content( $form_id, $args ) {

	$show_content = give_get_form_content_placement( $form_id, $args );

	// Bailout.
	if ( empty( $show_content ) ) {
		return false;
	}

	// Add action according to value.
	add_action( $show_content, 'give_form_display_content', 10, 2 );
}

add_action( 'give_pre_form_output', 'give_form_content', 10, 2 );

/**
 * Renders Post Form Content.
 *
 * Displays content for Give forms; fired by action from give_form_content.
 *
 * @since  1.0
 *
 * @param  int   $form_id The form ID.
 * @param  array $args    An array of form arguments.
 *
 * @return void
 */
function give_form_display_content( $form_id, $args ) {

	$content      = wpautop( get_post_meta( $form_id, '_give_form_content', true ) );
	$show_content = give_get_form_content_placement( $form_id, $args );

	if ( give_is_setting_enabled( give_get_option( 'the_content_filter' ) ) ) {
		$content = apply_filters( 'the_content', $content );
	}

	$output = '<div id="give-form-content-' . $form_id . '" class="give-form-content-wrap ' . $show_content . '-content">' . $content . '</div>';

	echo apply_filters( 'give_form_content_output', $output );

	//remove action to prevent content output on addition forms on page.
	//@see: https://github.com/WordImpress/Give/issues/634.
	remove_action( $show_content, 'give_form_display_content' );
}

/**
 * Renders the hidden Checkout fields.
 *
 * @since 1.0
 *
 * @param  int $form_id The form ID.
 *
 * @return void
 */
function give_checkout_hidden_fields( $form_id ) {

	/**
	 * Fires while rendering hidden checkout fields, before the fields.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id The form ID.
	 */
	do_action( 'give_hidden_fields_before', $form_id );

	if ( is_user_logged_in() ) { ?>
        <input type="hidden" name="give-user-id" value="<?php echo get_current_user_id(); ?>"/>
	<?php } ?>
    <input type="hidden" name="give_action" value="purchase"/>
    <input type="hidden" name="give-gateway" value="<?php echo give_get_chosen_gateway( $form_id ); ?>"/>
	<?php
	/**
	 * Fires while rendering hidden checkout fields, after the fields.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id The form ID.
	 */
	do_action( 'give_hidden_fields_after', $form_id );

}

/**
 * Filter Success Page Content.
 *
 * Applies filters to the success page content.
 *
 * @since 1.0
 *
 * @param  string $content Content before filters.
 *
 * @return string $content Filtered content.
 */
function give_filter_success_page_content( $content ) {

	$give_options = give_get_settings();

	if ( isset( $give_options['success_page'] ) && isset( $_GET['payment-confirmation'] ) && is_page( $give_options['success_page'] ) ) {
		if ( has_filter( 'give_payment_confirm_' . $_GET['payment-confirmation'] ) ) {
			$content = apply_filters( 'give_payment_confirm_' . $_GET['payment-confirmation'], $content );
		}
	}

	return $content;
}

add_filter( 'the_content', 'give_filter_success_page_content' );

/**
 * Test Mode Frontend Warning.
 *
 * Displays a notice on the frontend for donation forms.
 *
 * @since 1.1
 */
function give_test_mode_frontend_warning() {

	if ( give_is_test_mode() ) {
		echo '<div class="give_error give_warning" id="give_error_test_mode"><p><strong>' . esc_html__( 'Notice:', 'give' ) . '</strong> ' . esc_html__( 'Test mode is enabled. While in test mode no live donations are processed.', 'give' ) . '</p></div>';
	}
}

add_action( 'give_pre_form', 'give_test_mode_frontend_warning', 10 );

/**
 * Members-only Form.
 *
 * If "Disable Guest Donations" and "Display Register / Login" is set to none.
 *
 * @since  1.4.1
 *
 * @param  string $final_output
 * @param  array  $args
 *
 * @return string
 */
function give_members_only_form( $final_output, $args ) {

	$form_id = isset( $args['form_id'] ) ? $args['form_id'] : 0;

	//Sanity Check: Must have form_id & not be logged in.
	if ( empty( $form_id ) || is_user_logged_in() ) {
		return $final_output;
	}

	//Logged in only and Register / Login set to none.
	if ( give_logged_in_only( $form_id ) && give_show_login_register_option( $form_id ) == 'none' ) {

		$final_output = give_output_error( esc_html__( 'Please log in in order to complete your donation.', 'give' ), false );

		return apply_filters( 'give_members_only_output', $final_output, $form_id );

	}

	return $final_output;

}

add_filter( 'give_donate_form', 'give_members_only_form', 10, 2 );

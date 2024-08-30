<?php
/**
 * Give Shortcodes
 *
 * @package     Give
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
use Give\DonationForms\DonationQuery;
use Give\Helpers\Form\Template\Utils\Frontend as FrontendFormTemplateUtils;
use Give\Helpers\Form\Utils as FormUtils;
use Give\Helpers\Frontend\ConfirmDonation;
use Give\Helpers\Frontend\Shortcode as ShortcodeUtils;
use Give\Views\IframeView;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Donation History Shortcode
 *
 * Displays a user's donation history.
 *
 * @since 3.7.0 Sanitize attributes
 * @since 3.1.0 pass form id by reference in give_totals shortcode.
 * @since  1.0
 *
 * @param array       $atts
 * @param string|bool $content
 *
 * @return string|bool
 */
function give_donation_history( $atts, $content = false ) {
    $atts = give_clean($atts);
	$donation_history_args = shortcode_atts(
		[
			'id'             => true,
			'date'           => true,
			'donor'          => false,
			'amount'         => true,
			'status'         => false,
			'payment_method' => false,
		],
		$atts,
		'donation_history'
	);

	// Always show receipt link.
	$donation_history_args['details'] = true;

	// Set Donation History Shortcode Arguments in session variable.
	Give()->session->set( 'give_donation_history_args', $donation_history_args );

	$get_data = give_clean( filter_input_array( INPUT_GET ) );

	// If payment_key query arg exists, return receipt instead of donation history.
	if (
		! empty( $get_data['donation_id'] ) ||
		(
			! empty( $get_data['action'] ) &&
			'view_in_browser' === $get_data['action']
		)
	) {
		ob_start();

		echo do_shortcode( ShortcodeUtils::getReceiptShortcodeFromConfirmationPage() );

		// Display donation history link only if Receipt Access Session is available.
		if ( give_get_receipt_session() || is_user_logged_in() ) {
			echo sprintf(
				'<a href="%s">%s</a>',
				esc_url($_SERVER['HTTP_REFERER'] ),
				__( '&laquo; Return to All Donations', 'give' )
			);
		}

		return ob_get_clean();
	}

	$email_access = give_get_option( 'email_access' );

	ob_start();

	/**
	 * Determine access
	 *
	 * A. Check if a user is logged in or does a session exists.
	 * B. Does an email-access token exist?
	 */
	if (
		is_user_logged_in()
		|| false !== Give()->session->get_session_expiration()
		|| ( give_is_setting_enabled( $email_access ) && Give()->email_access->token_exists )
		|| true === give_get_history_session()
	) {
		give_get_template_part( 'history', 'donations' );

		if ( ! empty( $content ) ) {
			echo do_shortcode( $content );
		}
	} elseif ( give_is_setting_enabled( $email_access ) ) {
		// Is Email-based access enabled?
		give_get_template_part( 'email', 'login-form' );

	} else {

		echo apply_filters( 'give_donation_history_nonuser_message', Give_Notices::print_frontend_notice( __( 'You must be logged in to view your donation history. Please login using your account or create an account using the same email you used to donate with.', 'give' ), false ) );
		echo do_shortcode( '[give_login]' );
	}

	/**
	 * Filter to modify donation history HTMl
	 *
	 * @since 2.1
	 *
	 * @param string HTML content
	 * @param array  $atts
	 * @param string $content content pass between enclose content
	 *
	 * @return string HTML content
	 */
	return apply_filters( 'give_donation_history_shortcode_html', ob_get_clean(), $atts, $content );
}

add_shortcode( 'donation_history', 'give_donation_history' );

/**
 * Donation Form Shortcode
 *
 * Show the Give donation form.
 *
 * @since 3.7.0 Sanitize attributes
 * @since 3.4.0 Add additional validations to check if the form is valid and has the 'published' status.
 * @since 2.30.0 Add short-circuit filter to allow for custom output.
 * @since  1.0
 *

 * @param array $atts Shortcode attributes
 *
 * @return string
 */
function give_form_shortcode( $atts ) {
    $atts = give_clean($atts);
	$atts = shortcode_atts( give_get_default_form_shortcode_args(), $atts, 'give_form' );

    if('fullForm' === $atts['display_style']) {
        $atts['display_style'] = 'onpage';
    }

	// Convert string to bool.
	$atts['show_title'] = filter_var( $atts['show_title'], FILTER_VALIDATE_BOOLEAN );
	$atts['show_goal']  = filter_var( $atts['show_goal'], FILTER_VALIDATE_BOOLEAN );

	// Set form id.
	$atts['id'] = $atts['id'] ?: FrontendFormTemplateUtils::getFormId();
    $formId = absint($atts['id']);

    if ( ! ShortcodeUtils::isValidForm($formId)) {
        ob_start();
        Give_Notices::print_frontend_notice(__('The shortcode is missing a valid Donation Form ID attribute.', 'give'),
            true);

        return ob_get_clean();
    }

    if ( ! ShortcodeUtils::isPublishedForm($formId)) {
        ob_start();
        Give_Notices::print_frontend_notice(__('The form is not published.', 'give'), true);

        return ob_get_clean();
    }

    _give_redirect_form_id($formId, $atts['id']);

    // Short-circuit the shortcode output if the filter returns a non-empty string.
    $output = apply_filters('givewp_form_shortcode_output', '', $atts);

    if ($output) {
        return $output;
    }

	// Fetch the Give Form.
	ob_start();

	if ( ! FormUtils::isLegacyForm( $formId ) ) {
		$showIframeInModal = 'button' === $atts['display_style'];
		$iframeView        = new IframeView();

		ConfirmDonation::storePostedDataInDonationSession();

		echo $iframeView->setFormId( $formId )
				   ->showInModal( $showIframeInModal )
				   ->setButtonTitle( $atts['continue_button_title'] )
				   ->setButtonColor( $atts['button_color'] )
				   ->render();
	} else {
		give_get_donation_form( $atts );
	}

	$final_output = ob_get_clean();

	return apply_filters( 'give_donate_form', $final_output, $atts );
}

add_shortcode( 'give_form', 'give_form_shortcode' );

/**
 * Donation Form Goal Shortcode.
 *
 * Show the Give donation form goals.
 *
 * @since 3.12.0 add start_date and end_date attributes
 * @since 3.7.0 Sanitize attributes
 * @since 3.4.0 Add additional validations to check if the form is valid and has the 'published' status.
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function give_goal_shortcode( $atts ) {
    $atts = give_clean($atts);
	$atts = shortcode_atts(
		[
			'id'        => '',
			'show_text' => true,
			'show_bar'  => true,
			'color'		=> '#66BB6A',
            'start_date' => '',
            'end_date' => '',
		],
		$atts,
		'give_goal'
	);

    _give_redirect_form_id($atts['id']);

	// get the Give Form.
	ob_start();

    $formId = (int)$atts['id'];

    // Sanity check 1: ensure there is an ID Provided.
    if ( ! ShortcodeUtils::isValidForm($formId)) {
        Give_Notices::print_frontend_notice(__('The shortcode is missing a valid Donation Form ID attribute.', 'give'),
            true);
    } elseif // Sanity check 2: ensure the form is published.
    ( ! ShortcodeUtils::isPublishedForm($formId)) {
        Give_Notices::print_frontend_notice(__('The form is not published.', 'give'), true);
    } elseif // Sanity check 3: Check the form even has Goals enabled.
    ( ! give_is_setting_enabled(give_get_meta($atts['id'], '_give_goal_option', true))) {

		Give_Notices::print_frontend_notice( __( 'The form does not have Goals enabled.', 'give' ), true );
	} else {
		// Passed all sanity checks: output Goal.
		give_show_goal_progress( $atts['id'], $atts );
	}

	$final_output = ob_get_clean();

	return apply_filters( 'give_goal_shortcode_output', $final_output, $atts );
}

add_shortcode( 'give_goal', 'give_goal_shortcode' );


/**
 * Login Shortcode.
 *
 * Shows a login form allowing users to users to log in. This function simply
 * calls the give_login_form function to display the login form.
 *
 * @since 3.7.0 Sanitize attributes
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @uses   give_login_form()
 *
 * @return string
 */
function give_login_form_shortcode( $atts ) {
    $atts = give_clean($atts);
	$atts = shortcode_atts(
		[
			// Add backward compatibility for redirect attribute.
			'redirect'        => '',
			'login-redirect'  => '',
			'logout-redirect' => '',
		],
		$atts,
		'give_login'
	);

	// Check login-redirect attribute first, if it empty or not found then check for redirect attribute and add value of this to login-redirect attribute.
	$atts['login-redirect'] = ! empty( $atts['login-redirect'] ) ? $atts['login-redirect'] : ( ! empty( $atts['redirect'] ) ? $atts['redirect'] : '' );

	return give_login_form( $atts['login-redirect'], $atts['logout-redirect'] );
}

add_shortcode( 'give_login', 'give_login_form_shortcode' );

/**
 * Register Shortcode.
 *
 * Shows a registration form allowing users to users to register for the site.
 *
 * @since 3.7.0 Sanitize attributes
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @uses   give_register_form()
 *
 * @return string
 */
function give_register_form_shortcode( $atts ) {
    $atts = give_clean($atts);
	$atts = shortcode_atts(
		[
			'redirect' => '',
		],
		$atts,
		'give_register'
	);

	return give_register_form( $atts['redirect'] );
}

add_shortcode( 'give_register', 'give_register_form_shortcode' );

/**
 * Receipt Shortcode.
 *
 * Shows a donation receipt.
 *
 * @since 3.16.0 add give_donation_confirmation_page_enqueue_scripts
 * @since 3.7.0 Sanitize and escape attributes
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function give_receipt_shortcode( $atts ) {

	global $give_receipt_args;

    $atts = give_clean($atts);

	$give_receipt_args = shortcode_atts(
		[
			'error'          => __( 'You are missing the donation id to view this donation receipt.', 'give' ),
			'price'          => true,
			'donor'          => true,
			'date'           => true,
			'payment_method' => true,
			'payment_id'     => true,
			'payment_status' => false,
			'company_name'   => false,
			'status_notice'  => true,
		],
		$atts,
		'give_receipt'
	);

	ob_start();

	$donation_id  = false;
	$receipt_type = false;
	$get_data     = give_clean( filter_input_array( INPUT_GET ) );
	$session      = give_get_purchase_session();

	if ( ! empty( $get_data['donation_id'] ) ) {
		$donation_id = $get_data['donation_id'];
	} elseif ( ! empty( $get_data['action'] ) && 'view_in_browser' === $get_data['action'] ) {
		$receipt_type = 'view_in_browser';
		$donation_id  = $get_data['_give_hash'];
	} elseif ( isset( $session['donation_id'] ) ) {
		$donation_id = $session['donation_id'];
	} elseif ( ! empty( $give_receipt_args['id'] ) ) {
		$donation_id = $give_receipt_args['id'];
	}

	// Display donation receipt placeholder while loading receipt via AJAX.
	if ( ! wp_doing_ajax() ) {
		give_get_template_part( 'receipt/placeholder' );

        do_action('give_donation_confirmation_page_enqueue_scripts');

		return apply_filters('give_receipt_shortcode_output', sprintf(
			'<div id="give-receipt" data-shortcode="%1$s" data-receipt-type="%2$s" data-donation-key="%3$s" >%4$s</div>',
			htmlspecialchars( wp_json_encode( $give_receipt_args ) ),
			esc_attr($receipt_type),
			esc_attr($donation_id),
			ob_get_clean()
		));
	}

	return give_display_donation_receipt( $atts );
}

add_shortcode( 'give_receipt', 'give_receipt_shortcode' );

/**
 * Profile Editor Shortcode.
 *
 * Outputs the Give Profile Editor to allow users to amend their details from the
 * front-end. This function uses the Give templating system allowing users to
 * override the default profile editor template. The profile editor template is located
 * under templates/shortcode-profile-editor.php, however, it can be altered by creating a
 * file called shortcode-profile-editor.php in the give_template directory in your active theme's
 * folder. Please visit the Give Documentation for more information on how the
 * templating system is used.
 *
 * @since 3.7.0 Sanitize attributes
 * @since  1.0
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string Output generated from the profile editor
 */
function give_profile_editor_shortcode( $atts ) {

    $atts = give_clean($atts);

	ob_start();

	// Restrict access to donor profile, if donor and user are disconnected.
	$is_donor_disconnected = get_user_meta( get_current_user_id(), '_give_is_donor_disconnected', true );
	if ( is_user_logged_in() && $is_donor_disconnected ) {
		Give_Notices::print_frontend_notice( __( 'Your Donor and User profile are no longer connected. Please contact the site administrator.', 'give' ), true, 'error' );

		return false;
	}

	give_get_template_part( 'shortcode', 'profile-editor' );

	return ob_get_clean();
}

add_shortcode( 'give_profile_editor', 'give_profile_editor_shortcode' );

/**
 * Process Profile Updater Form.
 *
 * Processes the profile updater form by updating the necessary fields.
 *
 * @since  1.0
 *
 * @param  array $data Data sent from the profile editor.
 *
 * @return bool
 */
function give_process_profile_editor_updates( $data ) {
	// Profile field change request.
	if ( empty( $_POST['give_profile_editor_submit'] ) && ! is_user_logged_in() ) {
		return false;
	}

	// Nonce security.
	if ( ! wp_verify_nonce( $data['give_profile_editor_nonce'], 'give-profile-editor-nonce' ) ) {
		return false;
	}

	$user_id       = get_current_user_id();
	$old_user_data = get_userdata( $user_id );

	/* @var Give_Donor $donor */
	$donor            = new Give_Donor( $user_id, true );
	$old_company_name = $donor->get_company_name();

	$display_name     = isset( $data['give_display_name'] ) ? sanitize_text_field( $data['give_display_name'] ) : $old_user_data->display_name;
	$first_name       = isset( $data['give_first_name'] ) ? sanitize_text_field( $data['give_first_name'] ) : $old_user_data->first_name;
	$last_name        = isset( $data['give_last_name'] ) ? sanitize_text_field( $data['give_last_name'] ) : $old_user_data->last_name;
	$company_name     = ! empty( $data['give_company_name'] ) ? sanitize_text_field( $data['give_company_name'] ) : $old_company_name;
	$email            = isset( $data['give_email'] ) ? sanitize_email( $data['give_email'] ) : $old_user_data->user_email;
	$password         = ! empty( $data['give_new_user_pass1'] ) ? $data['give_new_user_pass1'] : '';
	$confirm_password = ! empty( $data['give_new_user_pass2'] ) ? $data['give_new_user_pass2'] : '';

	$userdata = [
		'ID'           => $user_id,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'display_name' => $display_name,
		'user_email'   => $email,
		'user_pass'    => $password,
		'company_name' => $company_name,
	];

	/**
	 * Fires before updating user profile.
	 *
	 * @since 1.0
	 *
	 * @param int   $user_id  The ID of the user.
	 * @param array $userdata User info, including ID, first name, last name, display name and email.
	 */
	do_action( 'give_pre_update_user_profile', $user_id, $userdata );

	// Make sure to validate first name of existing donors.
	if ( empty( $first_name ) ) {
		// Empty First Name.
		give_set_error( 'empty_first_name', __( 'Please enter your first name.', 'give' ) );
	}

	// Make sure to validate passwords for existing Donors.
	give_validate_user_password( $password, $confirm_password );

	if ( empty( $email ) ) {
		// Make sure email should not be empty.
		give_set_error( 'email_empty', __( 'The email you entered is empty.', 'give' ) );

	} elseif ( ! is_email( $email ) ) {
		// Make sure email should be valid.
		give_set_error( 'email_not_valid', __( 'The email you entered is not valid. Please use another', 'give' ) );

	} elseif ( $email !== $old_user_data->user_email ) {
		// Make sure the new email doesn't belong to another user.
		if ( email_exists( $email ) ) {
			give_set_error( 'user_email_exists', __( 'The email you entered belongs to another user. Please use another.', 'give' ) );
		} elseif ( Give()->donors->get_donor_by( 'email', $email ) ) {
			// Make sure the new email doesn't belong to another user.
			give_set_error( 'donor_email_exists', __( 'The email you entered belongs to another donor. Please use another.', 'give' ) );
		}
	}

	// Check for errors.
	$errors = give_get_errors();

	if ( $errors ) {
		// Send back to the profile editor if there are errors.
		wp_redirect( $data['give_redirect'] );
		give_die();
	}

	// Update Donor First Name and Last Name.
	Give()->donors->update(
		$donor->id,
		[
			'name' => trim( "{$first_name} {$last_name}" ),
		]
	);
	Give()->donor_meta->update_meta( $donor->id, '_give_donor_first_name', $first_name );
	Give()->donor_meta->update_meta( $donor->id, '_give_donor_last_name', $last_name );
	Give()->donor_meta->update_meta( $donor->id, '_give_donor_company', $company_name );

	$current_user = wp_get_current_user();

	// Compares new values with old values to detect change in values.
	$email_update        = ( $email !== $current_user->user_email ) ? true : false;
	$display_name_update = ( $display_name !== $current_user->display_name ) ? true : false;
	$first_name_update   = ( $first_name !== $current_user->first_name ) ? true : false;
	$last_name_update    = ( $last_name !== $current_user->last_name ) ? true : false;
	$company_name_update = ( $company_name !== $old_company_name ) ? true : false;
	$update_code         = 0;

	/**
	 * True if update is done in display name, first name, last name or email.
	 *
	 * @var boolean
	 */
	$profile_update = ( $email_update || $display_name_update || $first_name_update || $last_name_update || $company_name_update );

	/**
	 * True if password fields are filled.
	 *
	 * @var boolean
	 */
	$password_update = ( ! empty( $password ) && ! empty( $confirm_password ) );

	if ( $profile_update ) {

		// If only profile fields are updated.
		$update_code = '1';

		if ( $password_update ) {

			// If profile fields AND password both are updated.
			$update_code = '2';
		}
	} elseif ( $password_update ) {

		// If only password is updated.
		$update_code = '3';
	}

	// Update the user.
	$updated = wp_update_user( $userdata );

	if ( $updated ) {

		/**
		 * Fires after updating user profile.
		 *
		 * @since 1.0
		 *
		 * @param int   $user_id  The ID of the user.
		 * @param array $userdata User info, including ID, first name, last name, display name and email.
		 */
		do_action( 'give_user_profile_updated', $user_id, $userdata );

		$profile_edit_redirect_args = [
			'updated'     => 'true',
			'update_code' => $update_code,
		];

		/**
		 * Update codes '2' and '3' indicate a password change.
		 * If the password is changed, then logout and redirect to the same page.
		 */
		if ( '2' === $update_code || '3' === $update_code ) {
			wp_logout();
		} else {
			wp_redirect( esc_url_raw( add_query_arg( $profile_edit_redirect_args, $data['give_redirect'] ) ) );
		}

		give_die();
	}

	return false;
}

add_action( 'give_edit_user_profile', 'give_process_profile_editor_updates' );

/**
 * Give totals Shortcode.
 *
 * Shows a donation total.
 *
 * @since 3.14.0 Replace "_give_form_earnings" form meta with $query->form($post)->sumAmount()
 * @since 3.7.0 Sanitize attributes
 * @since  2.1
 *
 * @param  array $atts Shortcode attributes.
 *
 * @return string
 */
function give_totals_shortcode( $atts ) {
	$total = get_option( 'give_earnings_total', false );

	$message = apply_filters( 'give_totals_message', __( 'Hey! We\'ve raised {total} of the {total_goal} we are trying to raise for this campaign!', 'give' ) );

	$atts = shortcode_atts(
		[
			'total_goal'   => 0, // integer.
			'ids'          => 0, // integer|array.
			'cats'         => 0, // integer|array.
			'tags'         => 0, // integer|array.
			'message'      => $message,
			'link'         => '', // URL.
			'link_text'    => __( 'Donate Now', 'give' ), // string,
			'progress_bar' => true, // boolean.
		],
		$atts,
		'give_totals'
	);

	// Total Goal.
	$total_goal = give_maybe_sanitize_amount( $atts['total_goal'] );

    $atts = give_clean($atts);

	/**
	 * Give Action fire before the shortcode is rendering is started.
	 *
     * @since 3.1.0 Use static function on array_map callback to pass the id as reference for _give_redirect_form_id to prevent warnings on PHP 8.0.1 or plus
	 * @since 2.1.4
	 *
	 * @param array $atts shortcode attribute.
	 */
	do_action( 'give_totals_goal_shortcode_before_render', $atts );

	// Build query based on cat, tag and Form ids.
	if ( ! empty( $atts['cats'] ) || ! empty( $atts['tags'] ) || ! empty( $atts['ids'] ) ) {

		$form_ids = [];
		if ( ! empty( $atts['ids'] ) ) {
			$form_ids = array_filter( array_map( 'trim', explode( ',', $atts['ids'] ) ) );
		}

        $form_ids = array_map(
            static function ($id) {
                _give_redirect_form_id($id);

                return $id;
            },
            $form_ids
        );

		/**
		 * Filter to modify WP Query for Total Goal.
		 *
		 * @since 2.1.4
		 *
		 * @param array WP query argument for Total Goal.
		 */
		$form_args = [
			'post_type'      => 'give_forms',
			'post_status'    => 'publish',
			'post__in'       => $form_ids,
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'tax_query'      => [
				'relation' => 'AND',
			],
		];

		if ( ! empty( $atts['cats'] ) ) {
			$cats                     = array_filter( array_map( 'trim', explode( ',', $atts['cats'] ) ) );
			$form_args['tax_query'][] = [
				'taxonomy' => 'give_forms_category',
				'terms'    => $cats,
			];
		}

		if ( ! empty( $atts['tags'] ) ) {
			$tags                     = array_filter( array_map( 'trim', explode( ',', $atts['tags'] ) ) );
			$form_args['tax_query'][] = [
				'taxonomy' => 'give_forms_tag',
				'terms'    => $tags,
			];
		}

		/**
		 * Filter to modify WP Query for Total Goal.
		 *
		 * @since 2.1.4
		 *
		 * @param array $form_args WP query argument for Total Goal.
		 *
		 * @return array $form_args WP query argument for Total Goal.
		 */
		$form_args = (array) apply_filters( 'give_totals_goal_shortcode_query_args', $form_args );

		$forms = new WP_Query( $form_args );

		if ( isset( $forms->posts ) ) {
			$total = 0;
			foreach ( $forms->posts as $post ) {
                $query = new DonationQuery();
				$form_earning = $query->form($post)->sumAmount();
				$form_earning = ! empty( $form_earning ) ? $form_earning : 0;

				/**
				 * Update Form earnings.
				 *
				 * @since 2.1
				 *
				 * @param int    $post         Form ID.
				 * @param string $form_earning Total earning of Form.
				 * @param array $atts shortcode attributes.
				 */
				$total += apply_filters( 'give_totals_form_earning', $form_earning, $post, $atts );
			}
		}
	} // End if().

	// Append link with text.
	$donate_link = '';
	if ( ! empty( $atts['link'] ) ) {
		$donate_link = sprintf( ' <a class="give-totals-text-link" href="%1$s">%2$s</a>', esc_url( $atts['link'] ), esc_html( $atts['link_text'] ) );
	}

	// Replace {total} in message.
	$message = str_replace(
		'{total}',
		give_currency_filter(
			give_format_amount(
				$total,
				[ 'sanitize' => false ]
			)
		),
		esc_html( $atts['message'] )
	);

	// Replace {total_goal} in message.
	$message = str_replace(
		'{total_goal}',
		give_currency_filter(
			give_format_amount(
				$total_goal,
				[ 'sanitize' => true ]
			)
		),
		$message
	);

	/**
	 * Update Give totals shortcode output.
	 *
	 * @since 2.1
	 *
	 * @param string $message Shortcode Message.
	 * @param array $atts ShortCode attributes.
	 */
	$message = apply_filters( 'give_totals_shortcode_message', $message, $atts );

	ob_start();
	?>
	<div class="give-totals-shortcode-wrap">
		<?php
		// Show Progress Bar if progress_bar set true.
		$show_progress_bar = isset( $atts['progress_bar'] ) ? filter_var( $atts['progress_bar'], FILTER_VALIDATE_BOOLEAN ) : true;
		if ( $show_progress_bar ) {
			give_show_goal_totals_progress( $total, $total_goal );
		}

		echo sprintf( $message ) . $donate_link;
		?>
	</div>
	<?php
	$give_totals_output = ob_get_clean();

	/**
	 * Give Action fire after the total goal shortcode rendering is end.
	 *
	 * @since 2.1.4
	 *
	 * @param array  $atts               shortcode attribute.
	 * @param string $give_totals_output shortcode output.
	 */
	do_action( 'give_totals_goal_shortcode_after_render', $atts, $give_totals_output );

	/**
	 * Give Totals Shortcode output.
	 *
	 * @since 2.1
	 *
	 * @param string $give_totals_output
	 */
	return apply_filters( 'give_totals_shortcode_output', $give_totals_output );

}

add_shortcode( 'give_totals', 'give_totals_shortcode' );


/**
 * Displays donation forms in a grid layout.
 *
 * @since 3.7.0 Sanitize attributes
 * @since  2.1.0
 *
 * @since 3.1.0 Use static function on array_map callback to pass the id as reference for _give_redirect_form_id to prevent warnings on PHP 8.0.1 or plus
 * @since      2.23.1 Updated the default text color for the donate button, see #6591.
 * @since      2.21.2 change tag_background_color, progress_bar_color to official green color #69b868.
 *             change tag_text_color color to #333333.
 * @since      2.20.0 $show_donate_button Option to show donate button
 * @since      2.20.0 $donate_button_text Default Donate
 * @since      2.20.0 $donate_button_background_color Default #66bb6a
 * @since      2.20.0 $donate_button_text_color Default #fff
 * @since      2.20.0 $show_bar Default false
 * @since      2.22.2 remove $show_bar attribute in favor of show_goal
 *
 * @param array $atts                {
 *                                   Optional. Attributes of the form grid shortcode.
 *
 * @type int    $forms_per_page      Number of forms per page. Default '12'.
 * @type bool   $paged               Whether to paginate forms. Default 'true'.
 * @type string $ids                 A comma-separated list of form IDs to display. Default empty.
 * @type string exclude              A comma-separated list of form IDs to exclude from display. Default empty.
 * @type string $cats                A comma-separated list of form categories to display. Default empty.
 * @type string $tags                A comma-separated list of form tags to display. Default empty.
 * @type string $columns             Maximum columns to display. Default 'best-fit'.
 *                                       Accepts 'best-fit', '1', '2', '3', '4'.
 * @type bool   $show_title          Whether to display form title. Default 'true'.
 * @type bool   $show_goal           Whether to display form goal. Default 'true'.
 * @type bool   $show_excerpt        Whether to display form excerpt. Default 'true'.
 * @type bool   $show_featured_image Whether to display featured image. Default 'true'.
 * @type string $image_size Featured image size. Default 'medium'. Accepts WordPress image sizes.
 * @type string $image_height        Featured image height. Default 'auto'. Accepts valid CSS heights.
 * @type int    $excerpt_length      Number of words before excerpt is truncated. Default '16'.
 * @type string $display_style How the form is displayed, either in new page or modal popup.
 *                                       Default 'redirect'. Accepts 'redirect', 'modal'.
 *
 * @return string|bool The markup of the form grid or false.
 */
function give_form_grid_shortcode( $atts ) {
    $atts = give_clean($atts);
	$give_settings = give_get_settings();

	$atts = shortcode_atts(
		[
			'forms_per_page'      => 12,
			'paged'               => true,
			'ids'                 => '',
			'exclude'             => '',
			'orderby'             => 'date',
			'order'               => 'DESC',
			'cats'                => '',
			'tags'                => '',
			'columns'             => '1',
			'show_title'          => true,
			'show_goal'           => true,
			'show_excerpt'        => true,
			'show_featured_image' => true,
			'show_donate_button'  => true,
			'donate_button_text'  => '',
			'tag_background_color' => '#69b868',
            'tag_text_color'      => '#333333',
            'donate_button_text_color' => '#000000',
			'image_size'          => 'medium',
			'image_height'        => 'auto',
			'excerpt_length'      => 16,
			'display_style'       => 'modal_reveal',
            'progress_bar_color' => '#69b868',
			'status'              => '', // open or closed.
		],
		$atts
	);

	// Validate integer attributes.
	$atts['forms_per_page'] = intval( $atts['forms_per_page'] );
	$atts['excerpt_length'] = intval( $atts['excerpt_length'] );

	// Validate boolean attributes.
	$boolean_attributes = [
		'paged',
		'show_title',
		'show_goal',
		'show_excerpt',
		'show_featured_image',
	];

	foreach ( $boolean_attributes as $att ) {
		$atts[ $att ] = filter_var( $atts[ $att ], FILTER_VALIDATE_BOOLEAN );
	}

	// Set default form query args.
	$form_args = [
		'post_type'      => 'give_forms',
		'post_status'    => 'publish',
		'posts_per_page' => $atts['forms_per_page'],
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
        'paged'          => $atts['paged'],
		'tax_query'      => [
			'relation' => 'AND',
		],
	];

	// Filter results of form grid based on form status.
	$form_closed_status = trim( $atts['status'] );

	if ( ! empty( $form_closed_status ) ) {
		$form_args['meta_query'] = [
			[
				'key'   => '_give_form_status',
				'value' => $form_closed_status,
			],
		];
	}

	// Maybe add pagination.
	if ( true === $atts['paged'] ) {
		$form_args['paged'] = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	}

	// Maybe filter forms by IDs.
	if ( ! empty( $atts['ids'] ) ) {
        $form_args['post__in'] = array_map(
            static function ($id) {
                _give_redirect_form_id($id);

                return $id;
            }, array_filter(array_map('trim', explode(',', $atts['ids'])))
        );
	}

	// Convert comma-separated form IDs into array.
	if ( ! empty( $atts['exclude'] ) ) {
		$form_args['post__not_in'] = array_filter(
			array_map(
				function( $item ) {
					return intval( trim( $item ) );
				},
				explode( ',', $atts['exclude'] )
			)
		);
	}

	// Maybe filter by form category.
	if ( ! empty( $atts['cats'] ) ) {
		$cats = array_filter( array_map( 'trim', explode( ',', $atts['cats'] ) ) );

		// Backward compatibility for term_ids.
		$term_ids = array_unique( array_filter( $cats, 'is_numeric' ) );
		if ( $term_ids ) {
			$form_args['tax_query'][] = [
				'taxonomy' => 'give_forms_category',
				'terms'    => $term_ids,
			];

		}

		$term_slug = array_unique( array_filter( array_diff( $cats, $term_ids ) ) );
		if ( $term_slug ) {
			$form_args['tax_query'][] = [
				'taxonomy' => 'give_forms_category',
				'field'    => 'slug',
				'terms'    => $term_slug,
			];

		}
	}

	// Maybe filter by form tag.
	if ( ! empty( $atts['tags'] ) ) {
		$tags = array_filter( array_map( 'trim', explode( ',', $atts['tags'] ) ) );

		// Backward compatibility for term_ids.
		$tag_ids = array_unique( array_filter( $tags, 'is_numeric' ) );
		if ( $tag_ids ) {
			$form_args['tax_query'][] = [
				'taxonomy' => 'give_forms_tag',
				'terms'    => $tag_ids,
			];

		}

		$tag_slug = array_unique( array_filter( array_diff( $tags, $tag_ids ) ) );
		if ( $tag_slug ) {
			$form_args['tax_query'][] = [
				'taxonomy' => 'give_forms_tag',
				'field'    => 'slug',
				'terms'    => $tag_slug,
			];

		}
	}

	/**
	 * Filter to modify WP Query for Total Goal.
	 *
	 * @since 2.1.4
	 *
	 * @param array $form_args WP query argument for Grid.
	 *
	 * @return array $form_args WP query argument for Grid.
	 */
	$form_args = (array) apply_filters( 'give_form_grid_shortcode_query_args', $form_args );

	// Maybe filter by form Amount Donated or Number of Donations.
	switch ( $atts['orderby'] ) {
		case 'amount_donated':
			$form_args['meta_key'] = '_give_form_earnings';
			$form_args['orderby']  = 'meta_value_num';
			break;
		case 'number_donations':
			$form_args['meta_key'] = '_give_form_sales';
			$form_args['orderby']  = 'meta_value_num';
			break;
        case 'random':
            $form_args['orderby']  = 'rand';
            break;
		case 'closest_to_goal':
			if ( give_has_upgrade_completed( 'v240_update_form_goal_progress' ) ) {
				$form_args['meta_key'] = '_give_form_goal_progress';
				$form_args['orderby']  = 'meta_value_num';
			}
			break;
	}

	// Query to output donation forms.
	$form_query = new WP_Query( $form_args );

	if ( $form_query->have_posts() ) {
		ob_start();

		add_filter( 'add_give_goal_progress_class', 'add_give_goal_progress_class', 10, 1 );
		add_filter( 'add_give_goal_progress_bar_class', 'add_give_goal_progress_bar_class', 10, 1 );
		add_filter( 'give_form_wrap_classes', 'add_class_for_form_grid', 10, 3 );
		add_action( 'give_donation_form_top', 'give_is_form_grid_page_hidden_field', 10, 3 );

		echo '<div class="give-wrap">';
		echo '<div class="give-grid give-grid--' . esc_attr( $atts['columns'] ) . '">';

		while ( $form_query->have_posts() ) {
			$form_query->the_post();

			// Give/templates/shortcode-form-grid.php.
			give_get_template( 'shortcode-form-grid', [ $give_settings, $atts ] );

		}

		wp_reset_postdata();

		echo '</div><!-- .give-grid -->';

		remove_filter( 'add_give_goal_progress_class', 'add_give_goal_progress_class' );
		remove_filter( 'add_give_goal_progress_bar_class', 'add_give_goal_progress_bar_class' );
		remove_filter( 'give_form_wrap_classes', 'add_class_for_form_grid', 10 );
		remove_action( 'give_donation_form_top', 'give_is_form_grid_page_hidden_field', 10 );

		if ( false !== $atts['paged'] ) {
			$paginate_args = [
				'current'   => max( 1, get_query_var( 'paged' ) ),
				'total'     => $form_query->max_num_pages,
				'show_all'  => false,
				'end_size'  => 1,
				'mid_size'  => 2,
				'prev_next' => true,
				'prev_text' => __( '&laquo; Previous', 'give' ),
				'next_text' => __( 'Next &raquo;', 'give' ),
				'type'      => 'plain',
				'add_args'  => false,
			];

			printf(
				'<div class="give-page-numbers">%s</div>',
				paginate_links( $paginate_args )
			);
		}
		echo '</div><!-- .give-wrap -->';

		return ob_get_clean();
	} // End if().
}

add_shortcode( 'give_form_grid', 'give_form_grid_shortcode' );


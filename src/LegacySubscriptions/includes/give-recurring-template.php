<?php
/**
 * Give Recurring frontend template
 *
 * @package     Give
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * Set Donors Choice Template Checkbox
 *
 * Outputs a checkbox that can be modified
 *
 * @param       $form_id
 *
 * @return mixed
 */
function give_output_donors_choice_checkbox( $form_id ) {

	$form_option = give_get_meta( $form_id, '_give_recurring', true );

	// Sanity check, ensure donor choice is active
	if ( $form_option !== 'yes_donor' ) {
		return false;
	}

	$period_functionality = give_get_meta( $form_id, '_give_period_functionality', true );
	$interval             = give_get_meta( $form_id, '_give_period_interval', true, 1 );
	$times                = give_get_meta( $form_id, '_give_times', true );
	$checked_option       = give_is_setting_enabled( give_get_meta( $form_id, '_give_checkbox_default', true ) );
	$checked              = $checked_option ? 'checked' : '';

	$period               = 'donors_choice' === $period_functionality ?
		give_get_meta( $form_id, '_give_period_default_donor_choice', true ) :
		give_get_meta( $form_id, '_give_period', true );

	$show_period          = 'donors_choice' === $period_functionality ?
		give_recurring_donors_choice_period_element( $form_id ) :
		give_recurring_pretty_subscription_frequency( $period, $times, true, $interval );

	$pretty_label         = give_recurring_pretty_subscription_frequency( $period, $times, false, $interval );
	$period_choice_text   = give_recurring_prepare_donor_choice_text( $form_id, array(
			'show_period' => $show_period,
			'period'      => $period,
			'times'       => $times,
	) );
	?>
	<div class="give-recurring-donors-choice">
		<?php
		echo sprintf(
			'<input id="%1$s" class="%2$s" name="%2$s" type="checkbox" data-period-label="%3$s" data-period="%6$s" data-interval="%7$s" %4$s /> <label for="%1$s">%5$s</label>',
			'give-' . $period_functionality . '-' . $form_id,
			'give-recurring-period',
			$pretty_label,
			apply_filters( 'give_recurring_donors_choice_checked', $checked, $form_id ),
			$period_choice_text,
			esc_attr( $period ),
			intval( $interval )
		);
		?>
	</div>
	<?php
	return true;
}

add_action( 'give_after_donation_levels', 'give_output_donors_choice_checkbox', 1, 1 );

/**
 * This function will prepare the donor choice text.
 *
 * @param int   $form_id Donation Form ID.
 * @param array $args    List of arguments.
 *                       show_period required Adds completed text.
 *                       period      required Recurring Period.
 *                       times       required Recurring Times.
 *
 * @since 1.7.0
 *
 * @return string
 */
function give_recurring_prepare_donor_choice_text( $form_id, $args ) {

	/**
	 * This filter hook will help you to change the donor choice text next to checkbox.
	 */
	return apply_filters(
		'give_recurring_output_donors_choice_text',
		sprintf(
			/* translators: 1. Show Period */
			esc_html__( 'Make this donation %1$s', 'give-recurring' ),
			$args['show_period']
		),
		$args['period'],
		$args['times'],
		$args['show_period'],
		$form_id
	);
}

/**
 * Allow the donor the choice to
 *
 * @since 1.4.3
 *
 * @param string $form_id
 *
 * @return string HTML output.
 */
function give_recurring_donors_choice_period_element( $form_id ) {

	$default            = give_get_meta( $form_id, '_give_period_default_donor_choice', true );
	$interval           = (int) give_get_meta( $form_id, '_give_period_interval', true, 1 );
	$times              = (int) give_get_meta( $form_id, '_give_times', true );
	$times              = give_recurring_calculate_times( $times, $interval );
	$recurring_interval = strtolower( give_recurring_pretty_interval( $interval ) );

	ob_start();

	// Show Recurring Interval.
	if ( ! empty( $recurring_interval ) ) {
		printf( __( '%s ', 'give-recurring' ), $recurring_interval );
	}

	echo '<select class="give-recurring-donors-choice-period" name="give-recurring-period-donors-choice">';
	// Loop through periods.
	foreach ( Give_Recurring()->periods() as $key => $item ) {
		if ( 'donors_choice' === $key ) {
			continue;
		}

		// Make text plural if interval is greater than 1.
		if ( $interval > '1' ) {
			$item = sprintf( __( '%ss', 'give-recurring' ), $item );
		}

		echo '<option value="' . $key . '" ' . ( $default === $key ? ' selected ' : '' ) . '>' . $item . '</option>';
	}
	echo '</select>';

	// Times enabled?
	if ( $times > 0 ) {
		printf( __( '%s times', 'give-recurring' ), ' ' . $times );
	}

	$output = ob_get_clean();


	return apply_filters( 'give_recurring_donors_choice_period_element', $output, $form_id );

}

/**
 * Set Admin Choice Template
 *
 * @param $form_id
 * @param $args
 *
 * @param $form_id
 * @param $args
 *
 * @return bool
 */
function give_output_admin_choice( $form_id, $args ) {

	$form_option  = give_get_meta( $form_id, '_give_recurring', true );
	$set_or_multi = give_get_meta( $form_id, '_give_price_option', true );

	// Sanity check: only allow admin's choice
	if ( 'yes_admin' !== $form_option ) {
		return false;
	}

	// Sanity Check: admin & multi options is handled by give_recurring_multilevel_text
	if ( 'yes_admin' === $form_option && 'multi' === $set_or_multi ) {
		return false;
	}

	$period        = give_get_meta( $form_id, '_give_period', true );
	$times         = give_get_meta( $form_id, '_give_times', true );
	$interval      = give_get_meta( $form_id, '_give_period_interval', true, 1 );
	$pretty_period = give_recurring_pretty_subscription_frequency( $period, $times, $lowercase = false, $interval );

	$output = '<span class="give-recurring-admin-choice">' . $pretty_period . '</span>';

	echo apply_filters( 'give_output_set_admin_choice_output', $output, $form_id, $args );

}

add_action( 'give_after_donation_amount', 'give_output_admin_choice', 10, 2 );

/**
 * Give Recurring Multilevel Text
 *
 * Pragmatically append, prepend, replace and/or alter multilevel donation form output
 *
 * @since 1.12.7 Do not add HTML to price name. Remove old hack which remove HTML from price name while processing donation.
 *
 * @param $level_text
 * @param $form_id
 * @param $level
 *
 * @return string
 */
function give_recurring_multilevel_text( $level_text, $form_id, $level ) {

	$form_option  = give_get_meta( $form_id, '_give_recurring', true );
	$set_or_multi = give_get_meta( $form_id, '_give_price_option', true );

	// Sanity check: Is this admin selection & multi?
	if ( 'yes_admin' !== $form_option ) {
		return $level_text;
	}
	// Sanity check: Is this multi?
	if ( 'multi' !== $set_or_multi ) {
		return $level_text;
	}

	// Sanity check: Is this level recurring enabled?
	if ( ! isset( $level['_give_recurring'] ) || $level['_give_recurring'] == 'no' ) {
		return $level_text;
	}

	$period        = isset( $level['_give_period'] ) ? $level['_give_period'] : '';
	$times         = isset( $level['_give_times'] ) ? $level['_give_times'] : 0;
	$interval      = isset( $level['_give_period_interval'] ) ? $level['_give_period_interval'] : 1;
	$pretty_period = give_recurring_pretty_subscription_frequency( $period, $times, $lowercase = false, $interval );

	$text = sprintf(
		'%1$s%2$s%3$s',
		$level_text,
		apply_filters( 'give_recurring_multilevel_text_separator', ', ', $form_id, $level ),
		$pretty_period
	);

	return apply_filters( 'give_recurring_multilevel_text', $text, $form_id, $level );

}

add_filter( 'give_form_level_text', 'give_recurring_multilevel_text', 10, 3 );


/**
 * Add a class to recurring levels
 *
 * @since 1.1
 *
 * @param $classes
 * @param $form_id
 * @param $level
 *
 * @return string $classes
 */
function give_recurring_multilevel_classes( $classes, $form_id, $level ) {

	$level_id = isset( $level['_give_id']['level_id'] ) ? $level['_give_id']['level_id'] : '';

	if ( empty( $level_id ) ) {
		return $classes;
	}

	$recurring = isset( $level['_give_recurring'] ) && $level['_give_recurring'] == 'yes' ? true : false;

	if ( $recurring ) {
		$classes .= ' give-recurring-level';
	}

	return apply_filters( 'give_recurring_multilevel_classes', $classes, $form_id, $level_id );

}

add_filter( 'give_form_level_classes', 'give_recurring_multilevel_classes', 10, 3 );


/**
 * Gets Times Billed for a subscription
 *
 * @param  Give_Subscription $subscription
 *
 * @return string
 */
function get_times_billed_text( $subscription ) {

	// Bill times will show infinite symbol if 0 == bill times
	$bill_times     = ( $subscription->bill_times == 0 ) ? __( 'Ongoing', 'give-recurring' ) : $subscription->bill_times;
	$total_payments = $subscription->get_total_payments();
	$times_billed   = $total_payments . ' / ' . $bill_times;

	return apply_filters( 'give_recurring_times_billed_text', $times_billed, $bill_times, $total_payments, $subscription );
}

/**
 * This function will be used to add renewal notice for donation receipt.
 *
 * @param mixed  $notices Notice.
 * @param int    $id      Post ID.
 * @param string $status  Payment Status.
 *
 * @since 1.8.2
 *
 * @return string
 */
function give_recurring_add_renewal_notice( $notices, $id, $status ) {

	if ( 'give_subscription' === $status ) {
		return Give()->notices->print_frontend_notice(
			__( 'Renewal Payment Complete: Thank you for donation!', 'give-recurring' ),
			false,
			'success'
		);
	}

	return $notices;
}

add_filter( 'give_receipt_status_notice', 'give_recurring_add_renewal_notice', 10, 3 );

/**
 * This function is used to fetch the HTML markup of Manage Subscriptions Link.
 *
 * @since 1.8.2
 *
 * @return void
 */
function give_recurring_get_manage_subscriptions_link() {

	// Link to the subscriptions page if set and exists.
	$donation_id = give_clean( filter_input( INPUT_GET, 'donation_id' ) );

	if (
        give_can_view_receipt( $donation_id ) ||
        is_user_logged_in()
    ) {
		echo sprintf(
			'<a href="%1$s" class="give-recurring-manage-subscriptions-receipt-link">%2$s</a>',
			esc_url( give_get_subscriptions_page_uri() ),
			esc_html__( 'Manage Subscriptions', 'give-recurring' ) . ' &raquo;'
		);
	}
}

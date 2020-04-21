<?php

use Give\Addon\Recurring as RecurringAddon;
use Give\Donation\Donation;
use function Give\Helpers\Form\Template\Utils\Frontend\getPaymentId;

$donationID = getPaymentId();

// Exit if donation is not subscription.
if ( ! RecurringAddon::isActive() || ! Donation::isRecurring( $donationID ) ) {
	return '';
}

$subscriptionDB = new Give_Subscriptions_DB();
$subscriptionId = $subscriptionDB->get_column_by( 'id', 'parent_payment_id', $donationID );
$subscription   = new Give_Subscription( $subscriptionId );

$frequency = give_recurring_pretty_subscription_frequency(
	$subscription->period,
	$subscription->bill_times,
	false,
	$subscription->frequency ?: 1
);

$subscriptionInformation = sprintf(
	'%1$s / %2$s',
	give_currency_filter(
		give_format_amount(
			$subscription->recurring_amount,
			[ 'donation_id' => $donationID ]
		),
		[ 'currency_code' => give_get_payment_currency_code( $donationID ) ]
	),
	$frequency
);

$editAmountLink = $subscription->can_update_subscription() ?
	sprintf(
		'<br><strong><a href="%3$s" title="%2$s" target="_parent">%1$s</a></strong>',
		__( 'Edit Amount', 'give' ),
		__( 'Edit amount of subscription', 'give' ),
		esc_url( $subscription->get_edit_subscription_url() )
	) :
	'';

$status = give_recurring_get_pretty_subscription_status( $subscription->status );

$renewalDate = ! empty( $subscription->expiration ) ?
	date_i18n( get_option( 'date_format' ), strtotime( $subscription->expiration ) ) :
	__( 'N/A', 'give' );

$progress = get_times_billed_text( $subscription );
?>
<div class="details">
	<h3 class="headline"><?php _e( 'Subscription Details', 'give' ); ?></h3>

	<div class="details-table">
		<div class="details-row">
			<div class="detail">
				<?php _e( 'Subscription', 'give' ); ?>
			</div>
			<div class="value">
				<?php
				printf(
					'%1$s%2$s',
					$subscriptionInformation,
					$editAmountLink
				);
				?>
			</div>
		</div>
		<div class="details-row">
			<div class="detail">
				<?php _e( 'Status', 'give' ); ?>
			</div>
			<div class="value">
				<?php echo $status; ?>
			</div>
		</div>

		<div class="details-row">
			<div class="detail">
				<?php _e( 'Renewal Date', 'give' ); ?>
			</div>
			<div class="value">
				<?php echo $renewalDate; ?>
			</div>
		</div>

		<div class="details-row">
			<div class="detail">
				<?php _e( 'Progress', 'give' ); ?>
			</div>
			<div class="value">
				<?php echo $progress; ?>
			</div>
		</div>

	</div>
</div>

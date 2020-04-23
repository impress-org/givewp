<?php

use Give\Addon\Recurring as RecurringAddon;
use Give\Donation\Donation;
use function Give\Helpers\Form\Template\Utils\Frontend\getPaymentId;

$donationID = getPaymentId();
$donation   = new Donation( $donationID );

// Exit if donation is not subscription.
if ( ! RecurringAddon::isActive() || ! $donation->isRecurring() ) {
	return '';
}

$recurringReceipt        = new RecurringAddon\Receipt( RecurringAddon::getSubscriptionFromInitialDonationId( $donationID ) );
$subscriptionInformation = $recurringReceipt->getSubscriptionVsFrequency();
$editAmountLink          = $recurringReceipt->getEditAmountLink();
$status                  = $recurringReceipt->getSubscriptionStatus();
$renewalDate             = $recurringReceipt->getRenewalDate();
$progress                = $recurringReceipt->getProgress();
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

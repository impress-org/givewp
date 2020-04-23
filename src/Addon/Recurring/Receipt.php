<?php

namespace Give\Addon\Recurring;

use Give_Subscription;

/**
 * Class Receipt
 *
 * @package Give\Addon\Recurring
 */
class Receipt {
	/**
	 * @var int
	 */
	private $subscription;

	/**
	 * @param Give_Subscription $subscription
	 *
	 * @since 2.7.0
	 */
	public function __construct( $subscription ) {
		$this->subscription = $subscription;
	}

	/**
	 * Get subscription frequesncy.
	 *
	 * @return mixed|string
	 * @since 2.7.0
	 */
	public function getSubscriptionFrequency() {
		return give_recurring_pretty_subscription_frequency(
			$this->subscription->period,
			$this->subscription->bill_times,
			false,
			$this->subscription->frequency ?: 1
		);
	}

	/**
	 * Get subscriotion amount vs frequency string.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function getSubscriptionVsFrequency() {
		return sprintf(
			'%1$s / %2$s',
			give_currency_filter(
				give_format_amount(
					$this->subscription->recurring_amount,
					[ 'donation_id' => $this->subscription->parent_payment_id ]
				),
				[ 'currency_code' => give_get_payment_currency_code( $this->subscription->parent_payment_id ) ]
			),
			$this->getSubscriptionFrequency()
		);
	}

	/**
	 * Get edit amount link.
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function getEditAmountLink() {
		return $this->subscription->can_update_subscription() ?
			sprintf(
				'<br><strong><a href="%3$s" title="%2$s" target="_parent">%1$s</a></strong>',
				__( 'Edit Amount', 'give' ),
				__( 'Edit amount of subscription', 'give' ),
				esc_url( $this->subscription->get_edit_subscription_url() )
			) :
			'';
	}

	/**
	 * Get subscription status.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getSubscriptionStatus() {
		return give_recurring_get_pretty_subscription_status( $this->subscription->status );
	}

	/**
	 * Get renewal date.
	 *
	 * @since 2.7.0
	 * @return string|void
	 */
	public function getRenewalDate() {
		return ! empty( $this->subscription->expiration ) ?
			date_i18n( get_option( 'date_format' ), strtotime( $this->subscription->expiration ) ) :
			__( 'N/A', 'give' );
	}

	/**
	 * Get progress.
	 *
	 * @since 2.7.0
	 * @return string
	 */
	public function getProgress() {
		return get_times_billed_text( $this->subscription );
	}
}

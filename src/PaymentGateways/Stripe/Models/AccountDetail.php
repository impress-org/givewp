<?php

namespace Give\PaymentGateways\Stripe\Models;

use InvalidArgumentException;

/**
 * Class AccountDetail
 *
 * @package Give\PaymentGateways\Stripe\Models
 * @unreleased
 */
class AccountDetail {
	public $type               = null;
	public $accountName        = null;
	public $accountSlug        = null;
	public $accountEmail       = null;
	public $accountCountry     = null;
	public $accountId          = null;
	public $liveSecretKey      = null;
	public $livePublishableKey = null;
	public $testSecretKey      = null;
	public $testPublishableKey = null;

	/**
	 * Return AccountDetail model
	 *
	 * @unreleased
	 * @param array $args
	 *
	 * @return AccountDetail
	 */
	public static function fromArray( $args ) {
		$accountDetail = new static();
		$accountDetail->validate( $args );

		$accountDetail->type               = $args['type'];
		$accountDetail->accountName        = $args['account_name'];
		$accountDetail->accountSlug        = $args['account_slug'];
		$accountDetail->accountEmail       = $args['account_email'];
		$accountDetail->accountCountry     = $args['account_country'];
		$accountDetail->accountId          = $args['account_id'];
		$accountDetail->liveSecretKey      = $args['live_secret_key'];
		$accountDetail->livePublishableKey = $args['live_publishable_key'];
		$accountDetail->testSecretKey      = $args['test_secret_key'];
		$accountDetail->testPublishableKey = $args['test_publishable_key'];

		return $accountDetail;
	}

	/**
	 * Validate array format.
	 *
	 * @since 2.9.0
	 *
	 * @param array $array
	 * @throws InvalidArgumentException
	 */
	private function validate( $array ) {
		$required = [
			'type',
			'account_name',
			'account_slug',
			'account_email',
			'account_country',
			'account_id',
			'live_secret_key',
			'live_publishable_key',
			'test_secret_key',
			'test_publishable_key',
		];

		if ( array_diff( $required, array_keys( $array ) ) ) {
			throw new InvalidArgumentException(
				sprintf(
					esc_html__( 'To create a %1$s object, please provide valid: %2$s', 'give' ),
					__CLASS__,
					implode( ' , ', $required )
				)
			);
		}
	}
}

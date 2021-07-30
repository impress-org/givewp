<?php

namespace Give\PaymentGateways\Stripe\DataTransferObjects;

/**
 * Class DisconnectStripeAccountDto
 * @package Give\PaymentGateways\Stripe\DataTransferObjects
 *
 * @unreleased
 */
class DisconnectStripeAccountDto {
	/**
	 * @var array|string
	 */
	public $accountType;
	/**
	 * @var array|string
	 */
	public $accountSlug;

	/**
	 * @unreleased
	 *
	 * @param $array
	 *
	 * @return self
	 */
	public static function fromArray( $array ) {
		$self = new static();

		$self->accountType = ! empty( $array['account_type'] ) ? $array['account_type'] : '';
		$self->accountSlug = ! empty( $array['account_slug'] ) ? $array['account_slug'] : '';

		return $self;
	}
}

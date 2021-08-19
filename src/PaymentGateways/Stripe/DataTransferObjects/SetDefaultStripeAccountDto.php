<?php

namespace Give\PaymentGateways\Stripe\DataTransferObjects;

/**
 * Class SetDefaultStripeAccountDto
 * @package Give\PaymentGateways\Stripe\DataTransferObjects
 *
 * @unreleased
 */
class SetDefaultStripeAccountDto {
	/**
	 * @var mixed|string
	 */
	public $accountSlug;

	/**
	 * @var int|string
	 */
	public $formId;

	/**
	 * @unreleased
	 * @param array $array
	 */
	public static function fromArray( $array ) {
		$self = new static();

		$self->accountSlug = ! empty( $array['account_slug'] ) ? $array['account_slug'] : '';
		$self->formId      = ! empty( $array['form_id'] ) ? absint( $array['form_id'] ) : '';

		return $self;
	}
}

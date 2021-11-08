<?php

namespace Give\PaymentGateways\DataTransferObjects;

/**
 * Class SubscriptionData
 * @unreleased
 */
class SubscriptionData {
	/**
	 * @var string
	 */
	public $period;

	/**
	 * @var string
	 */
	public $times;

	/**
	 * @var string
	 */
	public $frequency;

	/**
	 * Convert data from request into DTO
	 *
	 * @unreleased
	 *
	 * @return self
	 */
	public static function fromRequest( array $request ) {
		$self = new static();

		$self->period = $request['period'];
		$self->times = $request['times'];
		$self->frequency = $request['frequency'];

		return $self;
	}
}

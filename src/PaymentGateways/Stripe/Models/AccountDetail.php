<?php

namespace Give\PaymentGateways\Stripe\Models;

use Give\Helpers\ArrayDataSet;
use InvalidArgumentException;

/**
 * Class AccountDetail
 *
 * @package Give\PaymentGateways\Stripe\Models
 * @since 2.10.2
 *
 * @property-read  string $type
 * @property-read  string $accountName
 * @property-read  string $accountSlug
 * @property-read  string $accountEmail
 * @property-read  string $accountCountry
 * @property-read  string $accountId
 * @property-read  string $liveSecretKey
 * @property-read  string $livePublishableKey
 * @property-read  string $testSecretKey
 * @property-read  string $testPublishableKey
 */
class AccountDetail {
	protected $args;
	protected $propertiesArgs;
	protected $requiredArgs = [
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

	/**
	 * AccountDetail constructor.
	 *
	 * @since 2.10.2
	 * @param array $args
	 */
	public function __construct( array $args ) {
		$this->args           = $args;
		$this->propertiesArgs = ArrayDataSet::camelCaseKeys( $args );
		$this->validate( $args );
	}

	/**
	 * @since 2.10.2
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->propertiesArgs[ $key ];
	}

	/**
	 * Validate array format.
	 *
	 * @since 2.10.2
	 * @param array $array
	 *
	 * @throws InvalidArgumentException
	 */
	private function validate( $array ) {
		if ( array_diff( $this->requiredArgs, array_keys( $array ) ) ) {
			throw new InvalidArgumentException(
				sprintf(
					esc_html__( 'To create a %1$s object, please provide valid: %2$s', 'give' ),
					__CLASS__,
					implode( ' , ', $this->requiredArgs )
				)
			);
		}
	}
}

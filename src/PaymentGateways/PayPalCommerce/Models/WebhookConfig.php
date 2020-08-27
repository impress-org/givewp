<?php

namespace Give\PaymentGateways\PayPalCommerce\Models;

class WebhookConfig {
	/**
	 * @since 2.9.0
	 *
	 * @var string
	 */
	public $id;

	/**
	 * @since 2.9.0
	 *
	 * @var string
	 */
	public $returnUrl;

	/**
	 * @since 2.9.0
	 *
	 * @var string[]
	 */
	public $events;

	/**
	 * WebhookConfig constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param string   $id
	 * @param string   $returnUrl
	 * @param string[] $events
	 */
	public function __construct( $id, $returnUrl, $events ) {
		$this->id        = $id;
		$this->returnUrl = $returnUrl;
		$this->events    = $events;
	}

	/**
	 * Generates an instance from serialized data
	 *
	 * @since 2.9.0
	 *
	 * @param array $data
	 *
	 * @return WebhookConfig
	 */
	public static function fromArray( array $data ) {
		return new self( $data['id'], $data['returnUrl'], $data['events'] );
	}

	/**
	 * Generates an array for serialization
	 *
	 * @since 2.9.0
	 *
	 * @return array
	 */
	public function toArray() {
		return [
			'id'        => $this->id,
			'returnUrl' => $this->returnUrl,
			'events'    => $this->events,
		];
	}
}

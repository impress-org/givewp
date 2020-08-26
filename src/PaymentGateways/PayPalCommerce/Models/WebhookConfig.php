<?php

namespace Give\PaymentGateways\PayPalCommerce\Models;

class WebhookConfig {
	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $returnUrl;

	/**
	 * @var string[]
	 */
	public $events;

	/**
	 * WebhookConfig constructor.
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

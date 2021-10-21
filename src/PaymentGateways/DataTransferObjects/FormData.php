<?php

namespace Give\PaymentGateways\DataTransferObjects;

/**
 * Class FormData
 * @unreleased
 */
class FormData {
	/**
	 * @var mixed
	 */
	public $price;
	/**
	 * @var mixed
	 */
	public $giveFormTitle;
	/**
	 * @var int
	 */
	public $giveFormId;
	/**
	 * @var mixed|string
	 */
	public $givePriceId;
	/**
	 * @var mixed
	 */
	public $date;
	/**
	 * @var mixed
	 */
	public $userEmail;
	/**
	 * @var mixed
	 */
	public $purchaseKey;
	/**
	 * @var string
	 */
	public $currency;
	/**
	 * @var mixed
	 */
	public $userInfo;
	/**
	 * @var string
	 */
	public $status;
	/**
	 * @var mixed
	 */
	public $gateway;
	/**
	 * @var mixed
	 */
	public $gatewayNonce;

	/**
	 * Convert data from request into DTO
	 *
	 * @unreleased
	 *
	 * @return self
	 */
	public static function fromRequest( array $request ) {
		$self = new static();

		$self->price = $request['price'];
		$self->giveFormTitle = $request['post_data']['give-form-title'];
		$self->giveFormId = (int) $request['post_data']['give-form-id'];
		$self->givePriceId = isset( $request['post_data']['give-price-id'] ) ? $request['post_data']['give-price-id'] : '';
		$self->date = $request['date'];
		$self->userEmail = $request['user_email'];
		$self->purchaseKey = $request['purchase_key'];
		$self->currency = give_get_currency( $request['post_data']['give-form-id'], $request );
		$self->userInfo = $request['user_info'];
		$self->status = 'pending';
		$self->gateway = $request['post_data']['give-gateway'];
		$self->gatewayNonce = $request['gateway_nonce'];

		return $self;
	}

	/**
	 * This is used to expose data for use with give_insert_payment
	 *
	 * @return array
	 */
	public function toPaymentArray() {
		return [
			'price' => $this->price,
			'give_form_title' => $this->giveFormTitle,
			'give_form_id' => $this->giveFormId,
			'give_price_id' => $this->givePriceId,
			'date' => $this->date,
			'user_email' => $this->userEmail,
			'purchase_key' => $this->purchaseKey,
			'currency' => $this->currency,
			'user_info' => $this->userInfo,
			'status' => $this->status,
		];
	}
}

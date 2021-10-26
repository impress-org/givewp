<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Give\ValueObjects\CardInfo;

/**
 * Class FormData
 * @unreleased
 */
class FormData {
	/**
	 * @var float
	 */
	public $price;
	/**
	 * @var string
	 */
	public $formTitle;
	/**
	 * @var string
	 */
	public $date;
	/**
	 * @var string
	 */
	public $userEmail;
	/**
	 * @var string
	 */
	public $purchaseKey;
	/**
	 * @var string
	 */
	public $currency;
	/**
	 * @var array
	 */
	public $userInfo;
	/**
	 * @var string
	 */
	public $status;
	/**
	 * @var string
	 */
	public $gateway;
	/**
	 * @var string
	 */
	public $gatewayNonce;
	/**
	 * @var array
	 */
	public $postData;
	/**
	 * @var CardInfo
	 */
	public $cardInfo;
	/**
	 * @var string
	 */
	public $honeypot;
	/**
	 * @var int
	 */
	public $formId;
	/**
	 * @var string
	 */
	public $priceId;
	/**
	 * @var string
	 */
	public $formIdPrefix;
	/**
	 * @var string
	 */
	public $currentUrl;
	/**
	 * @var string
	 */
	public $formMinimum;
	/**
	 * @var string
	 */
	public $formMaximum;
	/**
	 * @var string
	 */
	public $formHash;
	/**
	 * @var string
	 */
	public $recurringLoggedInOnly;
	/**
	 * @var string
	 */
	public $loggedInOnly;
	/**
	 * @var string
	 */
	public $recurringDonationDetails;
	/**
	 * @var string
	 */
	public $amount;
	/**
	 * @var string
	 */
	public $stripePaymentMethod;
	/**
	 * @var string
	 */
	public $firstName;
	/**
	 * @var string
	 */
	public $lastName;
	/**
	 * @var string
	 */
	public $userId;
	/**
	 * @var string
	 */
	public $email;
	/**
	 * @var string
	 */
	public $action;

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
		$self->date = $request['date'];
		$self->userEmail = $request['user_email'];
		$self->purchaseKey = $request['purchase_key'];
		$self->currency = give_get_currency( $request['post_data']['give-form-id'], $request );
		$self->userInfo = $request['user_info'];
		$self->postData = $request['post_data'];
		$self->honeypot = $request['post_data']['give-honeypot'];
		$self->formTitle = $request['post_data']['give-form-title'];
		$self->formId = (int) $request['post_data']['give-form-id'];
		$self->priceId = isset( $request['post_data']['give-price-id'] ) ? $request['post_data']['give-price-id'] : '';
		$self->formIdPrefix = $request['post_data']['give-form-id-prefix'];
		$self->currentUrl = $request['post_data']['give-current-url'];
		$self->formMinimum = $request['post_data']['give-form-minimum'];
		$self->formMaximum = $request['post_data']['give-form-maximum'];
		$self->formHash = $request['post_data']['give-form-hash'];
		$self->loggedInOnly = $request['post_data']['give-logged-in-only'];
		$self->amount = $request['post_data']['give-amount'];
		$self->gateway = $request['post_data']['give-gateway'];
		$self->firstName = $request['post_data']['give_first'];
		$self->lastName = $request['post_data']['give_last'];
		$self->userId = $request['post_data']['give-user-id'];
		$self->email = $request['post_data']['give_email'];
		$self->action = $request['post_data']['give_action'];
		$self->status = 'pending';
		$self->gatewayNonce = $request['gateway_nonce'];
		$self->cardInfo = CardInfo::fromArray( [
			'name' => $request['card_info']['card_name'],
			'cvc' => $request['card_info']['card_cvc'],
			'expMonth' => $request['card_info']['card_exp_month'],
			'expYear' => $request['card_info']['card_exp_year'],
			'number' => $request['card_info']['card_number'],
			'address' => $request['card_info']['card_address'],
			'address2' => $request['card_info']['card_address_2'],
			'city' => $request['card_info']['card_city'],
			'state' => $request['card_info']['card_state'],
			'country' => $request['card_info']['card_country'],
			'zip' => $request['card_info']['card_zip'],
		] );

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
			'give_form_title' => $this->formTitle,
			'give_form_id' => $this->formId,
			'give_price_id' => $this->priceId,
			'date' => $this->date,
			'user_email' => $this->userEmail,
			'purchase_key' => $this->purchaseKey,
			'currency' => $this->currency,
			'user_info' => $this->userInfo,
			'status' => $this->status,
		];
	}
}

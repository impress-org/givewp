<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Give\ValueObjects\Address;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;

/**
 * Class FormData
 * @since 2.18.0
 */
class FormData
{
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
    public $paymentGateway;
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
    public $loggedInOnly;
    /**
     * @var string
     */
    public $amount;
    /**
     * @var string
     */
    public $userId;
    /**
     * @var Address
     */
    public $billingAddress;
    /**
     * @var DonorInfo
     */
    public $donorInfo;
    /**
     * This property is only for internal use. It will be removed in the future.
     * We will use this property to gracefully deprecate action and filter which exist in existing donation flow.
     *
     * @deprecated
     * @var array
     */
    public $legacyDonationData;

    /**
     * Convert data from request into DTO
     *
     * @since 2.18.0
     *
     * @return self
     */
    public static function fromRequest(array $request)
    {
        $self = new static();

        $self->legacyDonationData = $request;
        $self->price = $request['price'];
        $self->date = $request['date'];
        $self->purchaseKey = $request['purchase_key'];
        $self->currency = give_get_currency($request['post_data']['give-form-id'], $request);
        $self->userInfo = $request['user_info'];
        $self->postData = $request['post_data'];
        $self->formTitle = $request['post_data']['give-form-title'];
        $self->formId = (int)$request['post_data']['give-form-id'];
        $self->priceId = isset($request['post_data']['give-price-id']) ? $request['post_data']['give-price-id'] : '';
        $self->formIdPrefix = $request['post_data']['give-form-id-prefix'];
        $self->currentUrl = $request['post_data']['give-current-url'];
        $self->formMinimum = $request['post_data']['give-form-minimum'];
        $self->formMaximum = $request['post_data']['give-form-maximum'];
        $self->formHash = $request['post_data']['give-form-hash'];
        $self->loggedInOnly = $request['post_data']['give-logged-in-only'];
        $self->amount = $request['post_data']['give-amount'];
        $self->paymentGateway = $request['post_data']['give-gateway'];
        $self->gatewayNonce = $request['gateway_nonce'];
        $self->donorInfo = DonorInfo::fromArray([
            'wpUserId' => $request['user_info']['id'],
            'firstName' => $request['user_info']['first_name'],
            'lastName' => $request['user_info']['last_name'],
            'email' => $request['user_info']['email'],
            'honorific' => !empty($request['user_info']['title']) ? $request['user_info']['title'] : '',
            'address' => $request['user_info']['address']
        ]);
        $self->cardInfo = CardInfo::fromArray([
            'name' => $request['card_info']['card_name'],
            'cvc' => $request['card_info']['card_cvc'],
            'expMonth' => $request['card_info']['card_exp_month'],
            'expYear' => $request['card_info']['card_exp_year'],
            'number' => $request['card_info']['card_number'],
        ]);
        $self->billingAddress = Address::fromArray([
            'line1' => $request['card_info']['card_address'],
            'line2' => $request['card_info']['card_address_2'],
            'city' => $request['card_info']['card_city'],
            'state' => $request['card_info']['card_state'],
            'country' => $request['card_info']['card_country'],
            'postalCode' => $request['card_info']['card_zip'],
        ]);

        return $self;
    }

    /**
     *
     * @return GiveInsertPaymentData
     */
    public function toGiveInsertPaymentData()
    {
        return GiveInsertPaymentData::fromArray([
            'price' => $this->price,
            'formTitle' => $this->formTitle,
            'formId' => $this->formId,
            'priceId' => $this->priceId,
            'date' => $this->date,
            'donorEmail' => $this->donorInfo->email,
            'purchaseKey' => $this->purchaseKey,
            'currency' => $this->currency,
            'userInfo' => $this->userInfo,
            'paymentGateway' => $this->paymentGateway
        ]);
    }

    /**
     * @param  int  $donationId
     * @return GatewayPaymentData
     */
    public function toGatewayPaymentData($donationId)
    {
        return GatewayPaymentData::fromArray([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'date' => $this->date,
            'price' => $this->price,
            'priceId' => $this->priceId,
            'gatewayId' => $this->paymentGateway,
            'donationId' => $donationId,
            'purchaseKey' => $this->purchaseKey,
            'donorInfo' => $this->donorInfo,
            'cardInfo' => $this->cardInfo,
            'billingAddress' => $this->billingAddress,
        ]);
    }
}

<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Give\Donations\Properties\BillingAddress;

/**
 * Class GiveInsertPaymentData
 *
 * This is used to expose data for use with give_insert_payment
 *
 * @since 2.18.0
 */
final class GiveInsertPaymentData
{
    /**
     * @var float
     */
    public $price;
    /**
     * @var string
     */
    public $priceId;
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
     * @var string
     */
    public $formTitle;
    /**
     * @var int
     */
    public $formId;
    /**
     * @var array
     */
    public $userInfo;
    /**
     * @var string
     */
    public $donorEmail;
    /**
     * @var string
     */
    public $paymentGateway;
    /**
     * @var int
     */
    public $donorId;

    /**
     * Convert data from array into DTO
     *
     * @since 2.18.0
     *
     * @return self
     */
    public static function fromArray(array $array)
    {
        $self = new static();

        $self->donorId = $array['donorId'];
        $self->price = $array['price'];
        $self->priceId = $array['priceId'];
        $self->formTitle = $array['formTitle'];
        $self->formId = $array['formId'];
        $self->currency = $array['currency'];
        $self->date = $array['date'];
        $self->purchaseKey = $array['purchaseKey'];
        $self->donorEmail = $array['donorEmail'];
        $self->userInfo = $array['userInfo'];
        $self->paymentGateway = $array['paymentGateway'];

        return $self;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'price' => $this->price,
            'give_form_title' => $this->formTitle,
            'give_form_id' => $this->formId,
            'give_price_id' => $this->priceId,
            'date' => $this->date,
            'user_email' => $this->donorEmail,
            'purchase_key' => $this->purchaseKey,
            'currency' => $this->currency,
            'user_info' => [
                'id' => $this->userInfo['id'],
                'title' => $this->userInfo['title'],
                'email' => $this->userInfo['email'],
                'first_name' => $this->userInfo['firstName'],
                'last_name' => $this->userInfo['lastName'],
                'donor_id' => $this->donorId,
                'address' => $this->getLegacyBillingAddress(),
            ],
            'status' => 'pending',
        ];
    }

    /**
     * Should return donor billing address for donation.
     *
     * Check legacy code give_get_donation_form_user:1212
     *
     * @unlreased
     *
     * @return array|bool
     */
    private function getLegacyBillingAddress()
    {
        /* @var BillingAddress $donorDonationBillingAddress */
        $donorDonationBillingAddress = $this->userInfo['address'];
        $address = [
            'line1' => $donorDonationBillingAddress->address1,
            'line2' => $donorDonationBillingAddress->address2,
            'city' => $donorDonationBillingAddress->city,
            'state' => $donorDonationBillingAddress->state,
            'zip' => $donorDonationBillingAddress->zip,
            'country' => $donorDonationBillingAddress->country,
        ];

        if (! $donorDonationBillingAddress->country) {
            $address = false;
        }

        return $address;
    }
}

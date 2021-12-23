<?php

namespace Give\PaymentGateways\DataTransferObjects;

use Give\PaymentGateways\GatewayPaymentRepository;
use Give\ValueObjects\Address;
use Give\ValueObjects\CardInfo;
use Give\ValueObjects\DonorInfo;

/**
 * Class GatewayPaymentData
 * @unreleased
 */
class GatewayPaymentData
{
    /**
     * @var string
     */
    public $gatewayId;
    /**
     * @var string
     */
    public $donationId;
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
     * @var DonorInfo
     */
    public $donorInfo;
    /**
     * @var CardInfo|null
     */
    public $cardInfo;
    /**
     * @var string
     */
    public $amount;
    /**
     * @var Address|null
     */
    public $billingAddress;
    /**
     * @var string
     */
    public $redirectUrl;
    /**
     * @var string
     */
    public $donationTitle;

    /**
     * Convert data from array into DTO
     *
     * @unreleased
     *
     * @return self
     */
    public static function fromArray(array $array)
    {
        $self = new static();

        $self->price = $array['price'];
        $self->priceId = $array['priceId'];
        $self->currency = $array['currency'];
        $self->amount = $array['amount'];
        $self->date = $array['date'];
        $self->gatewayId = $array['gatewayId'];
        $self->donationId = $array['donationId'];
        $self->purchaseKey = $array['purchaseKey'];
        $self->donorInfo = $array['donorInfo'];
        $self->cardInfo = $array['cardInfo'];
        $self->billingAddress = $array['billingAddress'];
        $self->redirectUrl = give_get_success_page_uri();

        $self->setDonationTitle();

        return $self;
    }

    /**
     * @unlreased
     *
     * @param int|null $titleLength
     *
     * @return string
     */
    public function getDonationTitle($titleLength = null)
    {
        // Cut the length
        if ( ! $titleLength) {
            return $this->donationTitle;
        }

        return substr($this->donationTitle, 0, $titleLength);
    }

    /**
     * Set donationTitle property.
     *
     * @unlreased
     */
    private function setDonationTitle()
    {
        $this->donationTitle = give( GatewayPaymentRepository::class)->getDonationTitle( $this->donationId );
    }
}

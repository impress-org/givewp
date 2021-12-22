<?php

namespace Give\PaymentGateways\DataTransferObjects;

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
     * @var int
     */
    public $formId;
    /**
     * @var string
     */
    public $donationTitle;
    /**
     * @var string
     */
    public $formTitle;

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
        $self->formId = $array['formId'];
        $self->formTitle = $array['formTitle'];
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
        $this->donationTitle = $this->formTitle;
        $price_id = $this->priceId;

        // Verify has variable prices.
        if (give_has_variable_prices($this->formId)) {
            $item_price_level_text = give_get_price_option_name($this->formId, $price_id, 0, false);

            /**
             * Output donation level text if:
             *
             * 1. It's not a custom amount
             * 2. The level field has actual text and isn't the amount (which is already displayed on the receipt).
             */
            if ('custom' !== $price_id && ! empty($item_price_level_text)) {
                // Matches a donation level - append level text.
                $this->donationTitle .= " - $item_price_level_text";
            }
        }

        // TODO: discuss backward compatibility about removed filter
    }
}

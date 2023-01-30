<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Support\ValueObjects\Money;
use Give\NextGen\DonationForm\Actions\GenerateDonationConfirmationReceiptUrl;
use Give\NextGen\DonationForm\Actions\GenerateDonationConfirmationReceiptViewRouteUrl;
use Give\NextGen\DonationForm\Models\DonationForm;

class DonateControllerData
{
    /**
     * @var float
     */
    public $amount;
    /**
     * @var string
     */
    public $gatewayId;
    /**
     * @var string
     */
    public $currency;
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
    public $email;
    /**
     * @var int
     */
    public $wpUserId;
    /**
     * @var int
     */
    public $formId;
    /**
     * @var string
     */
    public $formTitle;
    /**
     * @var string|null
     */
    public $company;
    /**
     * @var string|null
     */
    public $honorific;
    /**
     * @var string
     */
    public $originUrl;
    /**
     * @var string|null
     */
    public $embedId;

    /**
     * @var bool
     */
    public $isEmbed;

    /**
     * @since 0.1.0
     */
    public function toDonation(int $donorId): Donation
    {
        $form = $this->getDonationForm();

        return new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $this->gatewayId,
            'amount' => Money::fromDecimal($this->amount, $this->currency),
            'donorId' => $donorId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'formId' => $this->formId,
            'formTitle' => $form->title,
            'company' => $this->company,
            'type' => DonationType::SINGLE()
        ]);
    }

    /**
     * @since 0.1.0
     */
    public function getSuccessUrl(Donation $donation): string
    {
        return $this->isEmbed ?
            $this->getDonationConfirmationReceiptUrl($donation) :
            $this->getDonationConfirmationReceiptViewRouteUrl($donation);
    }

    /**
     * @since 0.1.0
     *
     * TODO: add params to route for flash message
     */
    public function getCancelUrl(): string
    {
        return $this->originUrl;
    }

    /**
     * @since 0.1.0
     */
    public function getDonationConfirmationReceiptViewRouteUrl(Donation $donation): string
    {
        return (new GenerateDonationConfirmationReceiptViewRouteUrl())($donation->purchaseKey);
    }

    /**
     * @since 0.1.0
     */
    public function getDonationConfirmationReceiptUrl(Donation $donation): string
    {
        return (new GenerateDonationConfirmationReceiptUrl())($donation, $this->originUrl, $this->embedId);
    }

    /**
     * @since 0.1.0
     */
    public function getDonationForm(): DonationForm
    {
        return DonationForm::find($this->formId);
    }

    /**
     * This is a hard-coded way of filtering through our dynamic properties
     * and only returning custom fields.
     *
     * TODO: figure out a less static way of doing this
     *
     * @since 0.1.0
     */
    public function getCustomFields(): array
    {
        $properties = get_object_vars($this);

        return array_filter($properties, static function ($param) {
            return !in_array(
                $param,
                array_merge(
                    Donation::propertyKeys(),
                    [
                        'currency',
                        'wpUserId',
                        'honorific',
                        'originUrl',
                        'isEmbed',
                        'embedId',
                    ]
                ),
                true
            );
        }, ARRAY_FILTER_USE_KEY);
    }
}

<?php

namespace Give\NextGen\DonationForm\DataTransferObjects;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Support\ValueObjects\Money;
use Give\NextGen\DonationForm\Actions\GenerateDonationConfirmationReceiptUrl;
use Give\NextGen\DonationForm\Actions\GenerateDonationConfirmationReceiptViewRouteUrl;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

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
     * @var DonationType
     */
    public $donationType;
    /**
     * @var SubscriptionPeriod|null
     */
    public $subscriptionPeriod;
    /**
     * @var int|null
     */
    public $subscriptionFrequency;
    /**
     * @var int|null
     */
    public $subscriptionInstallments;

    /**
     * @since 0.1.0
     */
    public function toDonation(int $donorId): Donation
    {
        $form = $this->getDonationForm();

        return new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $this->gatewayId,
            'amount' => $this->amount(),
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
     * @unreleased
     */
    public function toInitialSubscriptionDonation(int $donorId, int $subscriptionId): Donation
    {
        $form = $this->getDonationForm();

        return new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $this->gatewayId,
            'amount' => $this->amount(),
            'donorId' => $donorId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'formId' => $this->formId,
            'formTitle' => $form->title,
            'company' => $this->company,
            'type' => DonationType::SUBSCRIPTION(),
            'subscriptionId' => $subscriptionId,
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
                    Subscription::propertyKeys(),
                    [
                        'currency',
                        'wpUserId',
                        'honorific',
                        'originUrl',
                        'isEmbed',
                        'embedId',
                        'donationType',
                        'subscriptionPeriod',
                        'subscriptionFrequency',
                        'subscriptionInstallments'
                    ]
                ),
                true
            );
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function toSubscription(int $donorId): Subscription
    {
        return new Subscription([
            'amount' => $this->amount(),
            'period' => $this->subscriptionPeriod,
            'frequency' => (int)$this->subscriptionFrequency,
            'donorId' => $donorId,
            'installments' => (int)$this->subscriptionInstallments,
            'status' => SubscriptionStatus::PENDING(),
            'mode' => give_is_test_mode() ? SubscriptionMode::TEST() : SubscriptionMode::LIVE(),
            'donationFormId' => $this->formId,
        ]);
    }

    /**
     * @return Money
     */
    public function amount(): Money
    {
        return Money::fromDecimal($this->amount, $this->currency);
    }
}

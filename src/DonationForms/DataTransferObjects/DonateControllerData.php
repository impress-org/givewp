<?php

namespace Give\DonationForms\DataTransferObjects;

use Exception;
use Give\DonationForms\Actions\GenerateDonationConfirmationReceiptUrl;
use Give\DonationForms\Actions\GenerateDonationConfirmationReceiptViewRouteUrl;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\Support\ValueObjects\Money;
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
     * @var bool
     */
    public $anonymous;
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
     * @var string
     */
    public $phone;
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
     * @var string
     */
    public $country;
    /**
     * @var string
     */
    public $address1;
    /**
     * @var string
     */
    public $address2;
    /**
     * @var string
     */
    public $city;
    /**
     * @var string
     */
    public $state;
    /**
     * @var string
     */
    public $zip;
    /**
     * @var string
     */
    public $comment;

    /**
     * @since 3.9.0 Added phone property
     * @since 3.2.0 added honorific property
     * @since 3.0.0
     */
    public function toDonation(int $donorId): Donation
    {
        $form = $this->getDonationForm();

        return new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $this->gatewayId,
            'amount' => $this->amount(),
            'anonymous' => $this->anonymous,
            'donorId' => $donorId,
            'honorific' => $this->honorific,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'formId' => $this->formId,
            'formTitle' => $form->title,
            'company' => $this->company,
            'comment' => $this->comment,
            'type' => DonationType::SINGLE(),
            'billingAddress' => $this->getBillingAddress(),
        ]);
    }

    /**
     * @since 3.9.0 Added phone property
     * @since 3.0.0
     */
    public function toInitialSubscriptionDonation(int $donorId, int $subscriptionId): Donation
    {
        $form = $this->getDonationForm();

        return new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $this->gatewayId,
            'amount' => $this->amount(),
            'anonymous' => $this->anonymous,
            'donorId' => $donorId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'formId' => $this->formId,
            'formTitle' => $form->title,
            'company' => $this->company,
            'comment' => $this->comment,
            'type' => DonationType::SUBSCRIPTION(),
            'subscriptionId' => $subscriptionId,
            'billingAddress' => $this->getBillingAddress(),
        ]);
    }

    /**
     * @since 3.16.0 Added "givewp_donation_confirmation_page_redirect_enabled" filter
     * @since 3.0.0
     */
    public function getSuccessUrl(Donation $donation): string
    {
        $form = $this->getDonationForm();

        if (apply_filters('givewp_donation_confirmation_page_redirect_enabled', $form->settings->enableReceiptConfirmationPage, $donation->formId)) {
            return $this->getDonationConfirmationPageFromSettings($donation);
        }

        return $this->isEmbed ?
            $this->getDonationConfirmationReceiptUrl($donation) :
            $this->getDonationConfirmationReceiptViewRouteUrl($donation);
    }

    /**
     * @since 3.0.0
     *
     * TODO: add params to route for flash message
     */
    public function getCancelUrl(): string
    {
        return $this->originUrl;
    }

    /**
     * @since 3.0.0
     */
    public function getDonationConfirmationReceiptViewRouteUrl(Donation $donation): string
    {
        return (new GenerateDonationConfirmationReceiptViewRouteUrl())($donation->purchaseKey);
    }

    /**
     * @since 3.0.0
     */
    public function getDonationConfirmationReceiptUrl(Donation $donation): string
    {
        return (new GenerateDonationConfirmationReceiptUrl())($donation, $this->originUrl, $this->embedId);
    }

    /**
     * @since 3.16.0
     */
    public function getDonationConfirmationPageFromSettings(Donation $donation): string
    {
        $settings = give_get_settings();

        $page = isset($settings['success_page'])
            ? get_permalink(absint($settings['success_page']))
            : get_bloginfo('url');

        $page = apply_filters('givewp_donation_confirmation_page_redirect_permalink', $page, $donation->formId);

        return esc_url_raw(add_query_arg(['receipt-id' => $donation->purchaseKey], $page));
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
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
                        'subscriptionInstallments',
                        'country',
                        'address1',
                        'address2',
                        'city',
                        'state',
                        'zip',
                    ]
                ),
                true
            );
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @since 3.0.0
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

    /**
     * @since 3.0.0
     */
    public function getGateway(): PaymentGateway
    {
        return give(PaymentGatewayRegister::class)->getPaymentGateway($this->gatewayId);
    }

    /**
     * @since 3.0.0
     */
    public function getBillingAddress(): BillingAddress
    {
        return BillingAddress::fromArray([
            'country' => $this->country,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
        ]);
    }

    /**
     * @since 3.0.0
     */
    public function has(string $name): bool
    {
        return isset($this->{$name});
    }

    /**
     * @since 3.0.0
     *
     * @return mixed|null
     */
    public function get(string $name)
    {
        return property_exists($this, $name) ? $this->{$name} : null;
    }
}

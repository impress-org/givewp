<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Exception;
use Give\DonationForms\DataTransferObjects\DonateFormRouteData;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.0.0
 */
class DonateFormDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldTransformToDonationModel()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

        $data = (object)[
            'gatewayId' => TestGateway::id(),
            'amount' => 50,
            'currency' => 'USD',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'bill@murray.com',
            'formTitle' => $form->title,
            'formId' => $form->id,
            'company' => null,
            'honorific' => null,
            'originUrl' => 'https://givewp.com',
            'embedId' => '123',
            'isEmbed' => true,
            'donationType' => DonationType::SINGLE()->getValue(),
            'subscriptionFrequency' => null,
            'subscriptionPeriod' => null,
            'subscriptionInstallments' => null,
        ];

        $donor = Donor::factory()->create();

        $donation = new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $data->gatewayId,
            'amount' => Money::fromDecimal($data->amount, $data->currency),
            'donorId' => $donor->id,
            'firstName' => $data->firstName,
            'lastName' => $data->lastName,
            'email' => $data->email,
            'formId' => $data->formId,
            'formTitle' => $data->formTitle,
            'company' => $data->company,
            'type' => DonationType::SINGLE()
        ]);

        $formData = DonateFormRouteData::fromRequest((array)$data);

        $data = $formData->validated();

        $this->assertEquals($donation->getAttributes(), $data->toDonation($donor->id)->getAttributes());
    }

    /**
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldTransformToSubscriptionModel()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

        $data = (object)[
            'gatewayId' => TestGateway::id(),
            'amount' => 50,
            'currency' => 'USD',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'bill@murray.com',
            'formTitle' => $form->title,
            'formId' => $form->id,
            'company' => null,
            'honorific' => null,
            'originUrl' => 'https://givewp.com',
            'embedId' => '123',
            'isEmbed' => true,
            'donationType' => DonationType::SINGLE()->getValue(),
            'subscriptionFrequency' => 1,
            'subscriptionPeriod' => SubscriptionPeriod::MONTH(),
            'subscriptionInstallments' => null,
        ];

        $donor = Donor::factory()->create();

        $donation = new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $data->gatewayId,
            'amount' => Money::fromDecimal($data->amount, $data->currency),
            'donorId' => $donor->id,
            'firstName' => $data->firstName,
            'lastName' => $data->lastName,
            'email' => $data->email,
            'formId' => $data->formId,
            'formTitle' => $data->formTitle,
            'company' => $data->company,
            'type' => DonationType::SINGLE()
        ]);

        $subscription = new Subscription([
            'amount' => $donation->amount,
            'period' => $data->subscriptionPeriod,
            'frequency' => (int)$data->subscriptionFrequency,
            'donorId' => $donor->id,
            'installments' => (int)$data->subscriptionInstallments,
            'status' => SubscriptionStatus::PENDING(),
            'mode' => give_is_test_mode() ? SubscriptionMode::TEST() : SubscriptionMode::LIVE(),
            'donationFormId' => $data->formId,
        ]);

        $formData = DonateFormRouteData::fromRequest((array)$data);

        $data = $formData->validated();
        $subscription = $data->toSubscription($donor->id);

        $this->assertEquals($subscription->getAttributes(), $subscription->getAttributes());
    }

}

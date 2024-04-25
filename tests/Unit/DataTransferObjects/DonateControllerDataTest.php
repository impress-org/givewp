<?php

namespace TestsNextGen\Unit\DataTransferObjects;

use Exception;
use Give\DonationForms\DataTransferObjects\DonateControllerData;
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

/**
 * @since 3.0.0
 */
class DonateControllerDataTest extends TestCase
{

    /**
     * @since 3.9.0 Add phone support
     * @since 3.2.0 added honorific property
     * @since 3.0.0
     */
    public function testToDonationShouldReturnDonationModel()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        $data = new DonateControllerData();

        $data->gatewayId = TestGateway::id();
        $data->amount = 100;
        $data->currency = "USD";
        $data->firstName = "Bill";
        $data->lastName = "Murray";
        $data->email = "billmurray@givewp.com";
        $data->phone = '+120155501234';
        $data->formId = $donationForm->id;
        $data->formTitle = $donationForm->title;
        $data->company = null;
        $data->wpUserId = null;
        $data->honorific = "Mr";
        $data->originUrl = "https://givewp.com";
        $data->embedId = '123';
        $data->isEmbed = true;
        $data->country = 'Country';
        $data->address1 = 'Address 1';
        $data->address2 = 'Address 2';
        $data->city = 'City';
        $data->state = 'State';
        $data->zip = 'Zip';

        $donation = new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $data->gatewayId,
            'amount' => Money::fromDecimal($data->amount, $data->currency),
            'donorId' => $donor->id,
            'honorific' => $data->honorific,
            'firstName' => $data->firstName,
            'lastName' => $data->lastName,
            'email' => $data->email,
            'phone' => $data->phone,
            'formId' => $data->formId,
            'formTitle' => $data->formTitle,
            'company' => $data->company,
            'type' => DonationType::SINGLE(),
            'billingAddress' => $data->getBillingAddress(),
        ]);

        $this->assertEquals(
            $data->toDonation($donor->id),
            $donation
        );
    }

    /**
     * @since 3.0.0
     * @throws Exception
     */
    public function testToSubscriptionShouldReturnSubscriptionModel()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        $data = new DonateControllerData();

        $data->gatewayId = TestGateway::id();
        $data->amount = 100;
        $data->currency = "USD";
        $data->firstName = "Bill";
        $data->lastName = "Murray";
        $data->email = "billmurray@givewp.com";
        $data->formId = $donationForm->id;
        $data->formTitle = $donationForm->title;
        $data->company = null;
        $data->wpUserId = null;
        $data->honorific = "Mr";
        $data->originUrl = "https://givewp.com";
        $data->embedId = '123';
        $data->isEmbed = true;
        $data->subscriptionPeriod = SubscriptionPeriod::MONTH();
        $data->subscriptionFrequency = 1;
        $data->subscriptionInstallments = 12;
        $data->country = 'Country';
        $data->address1 = 'Address 1';
        $data->address2 = 'Address 2';
        $data->city = 'City';
        $data->state = 'State';
        $data->zip = 'Zip';

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
            'type' => DonationType::SINGLE(),
            'billingAddress' => $data->getBillingAddress(),
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

        $this->assertEquals(
            $data->toSubscription($donor->id),
            $subscription
        );
    }

    /**
     * @since 3.0.0
     * @throws Exception
     */
    public function testToInitialSubscriptionDonationShouldReturnDonationModel()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        $data = new DonateControllerData();

        $data->gatewayId = TestGateway::id();
        $data->amount = 100;
        $data->currency = "USD";
        $data->firstName = "Bill";
        $data->lastName = "Murray";
        $data->email = "billmurray@givewp.com";
        $data->formId = $donationForm->id;
        $data->formTitle = $donationForm->title;
        $data->company = null;
        $data->wpUserId = null;
        $data->honorific = "Mr";
        $data->originUrl = "https://givewp.com";
        $data->embedId = '123';
        $data->isEmbed = true;
        $data->subscriptionPeriod = SubscriptionPeriod::MONTH();
        $data->subscriptionFrequency = 1;
        $data->subscriptionInstallments = 12;
        $data->country = 'Country';
        $data->address1 = 'Address 1';
        $data->address2 = 'Address 2';
        $data->city = 'City';
        $data->state = 'State';
        $data->zip = 'Zip';

        $subscription = Subscription::create([
            'amount' => Money::fromDecimal($data->amount, $data->currency),
            'period' => $data->subscriptionPeriod,
            'frequency' => (int)$data->subscriptionFrequency,
            'donorId' => $donor->id,
            'installments' => (int)$data->subscriptionInstallments,
            'status' => SubscriptionStatus::PENDING(),
            'mode' => give_is_test_mode() ? SubscriptionMode::TEST() : SubscriptionMode::LIVE(),
            'donationFormId' => $data->formId,
        ]);

        $donation = new Donation([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => $data->gatewayId,
            'amount' => $subscription->amount,
            'donorId' => $donor->id,
            'firstName' => $data->firstName,
            'lastName' => $data->lastName,
            'email' => $data->email,
            'formId' => $data->formId,
            'formTitle' => $data->formTitle,
            'company' => $data->company,
            'type' => DonationType::SUBSCRIPTION(),
            'subscriptionId' => $subscription->id,
            'billingAddress' => $data->getBillingAddress(),
        ]);

        $this->assertEquals(
            $data->toInitialSubscriptionDonation($donor->id, $subscription->id),
            $donation
        );
    }

    /**
     * @since 3.0.0
     */
    public function testGetCustomFieldsShouldReturnCustomFieldsArray()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        $data = new DonateControllerData();

        $data->gatewayId = TestGateway::id();
        $data->amount = 100;
        $data->currency = "USD";
        $data->firstName = "Bill";
        $data->lastName = "Murray";
        $data->email = "billmurray@givewp.com";
        $data->formId = $donationForm->id;
        $data->formTitle = $donationForm->title;
        $data->company = null;
        $data->wpUserId = null;
        $data->honorific = "Mr";
        $data->originUrl = "https://givewp.com";
        $data->embedId = "123";
        $data->isEmbed = true;
        $data->customFieldString = 'customFieldString';
        $data->customFieldInteger = 2;
        $data->customFieldBoolean = false;
        $data->country = 'Country';
        $data->address1 = 'Address 1';
        $data->address2 = 'Address 2';
        $data->city = 'City';
        $data->state = 'State';
        $data->zip = 'Zip';

        $this->assertSame(
            [
                'customFieldString' => 'customFieldString',
                'customFieldInteger' => 2,
                'customFieldBoolean' => false,
            ],
            $data->getCustomFields()
        );
    }
}

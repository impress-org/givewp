<?php

namespace TestsNextGen\Unit\DataTransferObjects;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\NextGen\DonationForm\DataTransferObjects\DonateControllerData;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use GiveTests\TestCase;

/**
 * @unreleased
 */
class DonateControllerDataTest extends TestCase
{

    /**
     * @unreleased
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
        $data->formId = $donationForm->id;
        $data->formTitle = $donationForm->title;
        $data->company = null;
        $data->wpUserId = null;
        $data->honorific = "Mr";

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

        $this->assertEquals(
            $data->toDonation($donor->id),
            $donation
        );
    }

    /**
     * @unreleased
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
        $data->customFieldString = 'customFieldString';
        $data->customFieldInteger = 2;
        $data->customFieldBoolean = false;

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

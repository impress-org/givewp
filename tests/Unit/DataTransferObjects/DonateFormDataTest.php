<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\NextGen\DonationForm\DataTransferObjects\DonateFormRouteData;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class DonateFormDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShouldTransformToDonationModel()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

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

}

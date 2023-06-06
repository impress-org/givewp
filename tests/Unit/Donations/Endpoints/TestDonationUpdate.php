<?php

namespace Unit\Donations\Endpoints;

use DateTime;
use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\Faker;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use WP_REST_Response;
use Yoast\PHPUnitPolyfills\Polyfills\AssertStringContains;

class TestDonationUpdate extends RestApiTestCase
{
    use AssertStringContains;
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    use LegacyDonationFormAdapter;
    use Faker;

    /**
     * Test that a valid status update request returns a successful response.
     *
     * @unreleased
     *
     * @dataProvider mockDonationStatusProvider
     *
     * @param string $mockDonationStatus
     *
     * @throws Exception
     */
    public function testValidRequestWithStatus(string $mockDonationStatus)
    {
        $donation = Donation::factory()->create();
        $donationId = $donation->id;

        $response = $this->handleRequest($donationId, ['status' => $mockDonationStatus]);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['updatedFields']);

        $donation = give()->donations->getById($donationId);
        $this->assertEquals($mockDonationStatus, $donation->status->getValue());
    }

    /**
     * Test that a valid payment information update request returns a successful response.
     *
     * @unreleased
     *
     * @throws Exception
     */
    public function testValidRequestWithPaymentInformation()
    {
        $donation = Donation::factory()->create();
        $donationId = $donation->id;
        $donationForm = $this->createSimpleDonationForm();
        $donationFormId = $this->createSimpleDonationForm()->id;

        $paymentInformation = [
            'amount' => $this->faker()->randomFloat(2, 1, 100),
            'feeAmountRecovered' => $this->faker()->randomFloat(2, 1, 10),
            'formId' => $donationFormId,
            'createdAt' => $this->faker()->dateTimeBetween('-1 week', 'now')
                ->format(Temporal::TIMESTAMP),
        ];

        $response = $this->handleRequest($donationId, $paymentInformation);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertCount(4, $data['updatedFields']);

        $donation = give()->donations->getById($donationId);

        foreach ($paymentInformation as $key => $value) {
            if (is_a($donation->{$key}, Money::class)) {
                $this->assertEquals($value, $donation->{$key}->formatToDecimal());
                continue;
            }

            if (is_a($donation->{$key}, DateTime::class)) {
                $this->assertEquals($value, $donation->{$key}->format(Temporal::TIMESTAMP));
                continue;
            }

            if ($key === 'formId') {
                $this->assertEquals($donationForm->title, $donation->formTitle);
            }
            $this->assertEquals($value, $donation->{$key});
        }
    }

    /**
     * Test that a valid donor details update request returns a successful response.
     *
     * @unreleased
     *
     * @throws Exception
     */
    public function testValidRequestWithDonorDetails()
    {
        $donation = Donation::factory()->create();
        $donationId = $donation->id;
        $donor = Donor::factory()->create();
        $donorId = $donor->id;

        $donorDetails = [
            'donorId' => $donorId,
        ];

        $response = $this->handleRequest($donationId, $donorDetails);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['updatedFields']);

        $donation = give()->donations->getById($donationId);

        $donorDetails = array_merge(
            $donorDetails,
            [
                'firstName' => $donor->firstName,
                'lastName' => $donor->lastName,
                'email' => $donor->email,
            ]
        );

        foreach ($donorDetails as $key => $value) {
            $this->assertEquals($value, $donation->{$key});
        }
    }

    /**
     * Test that a valid billing address details update request returns a successful response.
     *
     * @unreleased
     *
     * @throws Exception
     */
    public function testValidRequestWithBillingAddressDetails()
    {
        $donation = Donation::factory()->create();
        $donationId = $donation->id;

        $billingAddressDetails = [
            'country' => $this->faker()->countryCode,
            'address1' => $this->faker()->buildingNumber . ' ' . $this->faker()->streetName . ' ' . $this->faker()->streetSuffix,
            'address2' => $this->faker()->secondaryAddress,
            'city' => $this->faker()->city,
            'state' => $this->faker()->state,
            'zip' => $this->faker()->postcode,
        ];

        $response = $this->handleRequest($donationId, $billingAddressDetails);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertCount(6, $data['updatedFields']);

        $donation = give()->donations->getById($donationId);

        foreach ($billingAddressDetails as $key => $value) {
            $this->assertEquals($value, $donation->billingAddress->{$key});
        }
    }

    /**
     * Test that a valid billing address details update request returns a successful response.
     *
     * @unreleased
     *
     * @throws Exception
     */
    public function testValidRequestWithComment()
    {
        $donation = Donation::factory()->create();
        $donationId = $donation->id;

        $comment = $this->faker()->text(144);

        $response = $this->handleRequest($donationId, ['comment' => $comment]);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['updatedFields']);

        $donation = give()->donations->getById($donationId);

        $this->assertEquals($comment, $donation->comment);
    }

    /**
     * Test that an invalid donation ID returns an error.
     *
     * @unreleased
     */
    public function testInvalidDonationId()
    {
        $donation = Donation::factory()->create();
        $donationId = $donation->id;
        $donation->delete();

        $response = $this->handleRequest($donationId);

        $this->assertErrorResponse('rest_invalid_param', $response, 400);

        $errorData = $response->as_error()->get_error_data('rest_invalid_param');
        if (isset($errorData['details'])) {
            $this->assertEquals('donation_not_found', $errorData['details']['id']['code']);
        }
    }

    /**
     * Test that an invalid status value returns a 400 error.
     *
     * @unreleased
     */
    public function testInvalidStatusValue()
    {
        $donation = Donation::factory()->create();
        $donation_id = $donation->id;
        $invalid_status = 'invalid';

        $response = $this->handleRequest($donation_id, ['status' => $invalid_status]);

        $this->assertErrorResponse('rest_invalid_param', $response, 400);

        $errorData = $response->as_error()->get_error_data('rest_invalid_param');
        if (isset($errorData['details'])) {
            $this->assertEquals('rest_not_in_enum', $errorData['details']['status']['code']);
        }
    }

    /**
     * Test that an invalid amount format returns an error.
     *
     * @unreleased
     */
    public function testInvalidAmount()
    {
        $donationId = Donation::factory()->create()->id;

        $response = $this->handleRequest($donationId, ['amount' => '1,23']);

        $this->assertErrorResponse('rest_invalid_param', $response, 400);

        $errorData = $response->as_error()->get_error_data('rest_invalid_param');
        if (isset($errorData['details'])) {
            $this->assertEquals('rest_invalid_type', $errorData['details']['amount']['code']);
        }
    }

    /**
     * Test that an invalid fee amount recovered format returns an error.
     *
     * @unreleased
     */
    public function testInvalidFeeAmountRecovered()
    {
        $donationId = Donation::factory()->create()->id;

        $response = $this->handleRequest($donationId, ['feeAmountRecovered' => '1,23']);

        $this->assertErrorResponse('rest_invalid_param', $response, 400);

        $errorData = $response->as_error()->get_error_data('rest_invalid_param');
        if (isset($errorData['details'])) {
            $this->assertEquals('rest_invalid_type', $errorData['details']['feeAmountRecovered']['code']);
        }
    }

    /**
     * Test that an invalid donation form ID returns an error.
     *
     * @unreleased
     */
    public function testInvalidDonationFormId()
    {
        $donationId = Donation::factory()->create()->id;

        $response = $this->handleRequest($donationId, ['formId' => PHP_INT_MAX]);

        $this->assertErrorResponse('rest_invalid_param', $response, 400);

        $errorData = $response->as_error()->get_error_data('rest_invalid_param');
        if (isset($errorData['details'])) {
            $this->assertEquals('form_not_found', $errorData['details']['formId']['code']);
        }
    }

    /**
     * Test that an invalid date format returns an error.
     *
     * @unreleased
     */
    public function testInvalidCreatedAt()
    {
        $donationId = Donation::factory()->create()->id;

        $response = $this->handleRequest($donationId, ['createdAt' => $this->faker()->date('d/m/Y')]);

        $this->assertErrorResponse('rest_invalid_param', $response, 400);

        $errorData = $response->as_error()->get_error_data('rest_invalid_param');
        if (isset($errorData['details'])) {
            $this->assertEquals('invalid_date', $errorData['details']['createdAt']['code']);
        }
    }

    /**
     * Test that an invalid donor ID returns an error.
     *
     * @unreleased
     */
    public function testInvalidDonorId()
    {
        $donationId = Donation::factory()->create()->id;

        $response = $this->handleRequest($donationId, ['donorId' => PHP_INT_MAX]);

        $this->assertErrorResponse('rest_invalid_param', $response, 400);

        $errorData = $response->as_error()->get_error_data('rest_invalid_param');
        if (isset($errorData['details'])) {
            $this->assertEquals('donor_not_found', $errorData['details']['donorId']['code']);
        }
    }

    /**
     * Test that an unauthorized request returns a 403 error.
     *
     * @unreleased
     */
    public function testUnauthorizedRequest()
    {
        $donationId = Donation::factory()->create()->id;

        $response = $this->handleRequest($donationId, [], false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * Handle the request common to all tests.
     *
     * @unreleased
     *
     * @param int   $donation_id
     * @param array $attributes
     * @param bool  $authenticatedAsAdmin
     *
     * @return WP_REST_Response
     */
    private function handleRequest(
        int $donation_id,
        array $attributes = [],
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'PATCH',
            "/give-api/v2/admin/donation/{$donation_id}",
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );
        $request->set_body_params($attributes);

        return $this->dispatchRequest($request);
    }

    /**
     * Create a mock array of donation statuses.
     *
     * @unreleased
     *
     * @return array
     */
    public function mockDonationStatusProvider(): array
    {
        $statuses = [];
        foreach (DonationStatus::toArray() as $status) {
            if ($status === DonationStatus::RENEWAL) {
                continue;
            }
            $statuses[] = [$status];
        }

        return $statuses;
    }
}

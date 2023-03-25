<?php

namespace Unit\Donations\Endpoints;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Response;
use Yoast\PHPUnitPolyfills\Polyfills\AssertStringContains;

class TestDonationDetails extends RestApiTestCase
{
    use AssertStringContains;
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that a valid request returns a successful response.
     *
     * @unreleased
     *
     * @dataProvider mockDonationStatusProvider
     *
     * @param string $mockDonationStatus
     *
     * @throws Exception
     */
    public function testValidRequest(string $mockDonationStatus) {
        $donation = Donation::factory()->create();
        $donation_id = $donation->id;

        $response = $this->handleRequest($donation_id, $mockDonationStatus);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);

        $donation = give()->donations->getById($donation_id);
        $this->assertEquals($mockDonationStatus, $donation->status->getValue());
    }

    /**
     * Test that an invalid donation ID returns a 400 error.
     *
     * @unreleased
     */
    public function testInvalidDonationId() {
        $response = $this->handleRequest(0, 'processing');

        $this->assertErrorResponse('rest_invalid_param', $response, 400);
    }

    /**
     * Test that a non-existent donation ID returns a 404 error.
     *
     * @unreleased
     */
    public function testNonExistentDonationId() {
        $response = $this->handleRequest(PHP_INT_MAX, 'processing');

        $this->assertErrorResponse('donation_not_found', $response, 404);
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

        $response = $this->handleRequest($donation_id, $invalid_status);

        if (is_a($response, WP_REST_Response::class)) {
            $response = $response->as_error();
        }

        $this->assertErrorResponse('rest_invalid_param', $response, 400);

        $data = $response->get_error_data();
        $this->assertArrayHasKey('status', $data['params']);
        $this->assertStringContainsString('status is not one of', $data['params']['status']);
    }

    /**
     * Test that an unauthorized request returns a 403 error.
     *
     * @unreleased
     */
    public function testUnauthorizedRequest() {
        $response = $this->handleRequest(1, 'processing', false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * Handle the request common to all tests.
     *
     * @unreleased
     *
     * @param int $donation_id
     * @param string $status
     * @param bool $authenticatedAsAdmin
     *
     * @return WP_REST_Response
     */
    private function handleRequest(
        int $donation_id,
        string $status,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'POST',
            "/give-api/v2/admin/donation/{$donation_id}",
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );
        $request->set_body_params([
            'status' => $status,
        ]);

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

<?php

namespace Unit\Donations\Endpoints;

use Give\Donations\Models\Donation;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\Faker;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Response;

class TestDonationCommentCRUD extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    use Faker;

    /**
     * Test updating an existing donation comment.
     *
     * @unreleased
     */
    public function testUpdateComment()
    {
        $donation = Donation::factory()->create();
        $donationId = $donation->id;
        $originalContent = $donation->comment;
        $updatedContent = $this->faker()->text;

        $response = $this->handleRequest($donationId, ['content' => $updatedContent]);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertTrue($data['success']);

        $updatedDonation = Donation::find($donationId);
        $this->assertEquals($updatedContent, $updatedDonation->comment);
        $this->assertNotEquals($originalContent, $updatedDonation->comment);
    }

    /**
     * Test deleting a donation comment.
     *
     * @unreleased
     */
    public function testDeleteComment()
    {
        $donation = Donation::factory()->create();
        $donationId = $donation->id;

        $response = $this->handleRequest($donationId, [], 'DELETE');

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals(200, $response->get_status());

        $data = $response->get_data();
        $this->assertTrue($data['success']);
        $this->assertEmpty($donation->comment);
    }

    /**
     * Test that an unauthorized request returns a 403 error.
     *
     * @unreleased
     */
    public function testUnauthorizedRequest()
    {
        $donationId = Donation::factory()->create()->id;

        $response = $this->handleRequest($donationId, [], 'DELETE', false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * Handle the request common to all tests.
     *
     * @unreleased
     *
     * @param int    $donation_id
     * @param array  $attributes
     * @param string $method
     * @param bool   $authenticatedAsAdmin
     *
     * @return WP_REST_Response
     */
    private function handleRequest(
        int $donation_id,
        array $attributes = [],
        string $method = 'POST',
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            $method,
            "/give-api/v2/admin/donation/{$donation_id}/comment",
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );
        $request->set_body_params($attributes);

        return $this->dispatchRequest($request);
    }
}

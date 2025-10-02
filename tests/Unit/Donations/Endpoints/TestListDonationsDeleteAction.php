<?php

namespace Give\Tests\Unit\Donations\Endpoints;

use Give\Campaigns\Models\Campaign;
use Give\Donations\Endpoints\DonationActions;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 4.10.0
 */
class TestListDonationsDeleteAction extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.10.0
     */
    public function setUp(): void
    {
        parent::setUp();

        // Set up user with proper permissions
        $this->setUpAdminUser();

        Donation::query()->delete();
    }

    /**
     * @since 4.10.0
     */
    private function setUpAdminUser(): void
    {
        $user = wp_get_current_user();
        $user->add_cap('delete_give_payments');
        $user->add_cap('edit_give_payments');
        $user->add_cap('view_give_payments');
    }

    /**
     * @since 4.10.0
     */
    public function testShouldPermanentlyDeleteDonationWithForceTrue()
    {
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);

        $donationId = $donation->id;

        // Verify donation exists
        $this->assertNotNull(Donation::find($donationId));

        $mockRequest = $this->getMockRequest('delete');
        $mockRequest->set_param('ids', (string)$donationId);
        $mockRequest->set_param('force', true);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());

        // Debug: Let's see what we're actually getting
        $this->assertTrue(
            in_array($donationId, $response->data['successes']) || in_array((string)$donationId, $response->data['successes']),
            'Expected donation ID ' . $donationId . ' in successes but got: ' . json_encode($response->data)
        );
        $this->assertEmpty($response->data['errors'], 'Expected no errors but got: ' . json_encode($response->data['errors']));

        // Verify donation is permanently deleted
        $this->assertNull(Donation::find($donationId));
    }

    /**
     * @since 4.10.0
     */
    public function testShouldMoveDonationToTrashWithForceFalse()
    {
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);

        $donationId = $donation->id;

        // Verify donation exists and is not trashed
        $this->assertNotNull(Donation::find($donationId));
        $this->assertNotEquals('trash', $donation->status);

        $mockRequest = $this->getMockRequest('delete');
        $mockRequest->set_param('ids', (string)$donationId);
        $mockRequest->set_param('force', false);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        // Check if the response is successful (200)
        $this->assertEquals(200, $response->get_status());

        // Verify donation is moved to trash (soft delete)
        $trashedDonation = Donation::find($donationId);
        $this->assertNotNull($trashedDonation);
        $this->assertEquals('trash', $trashedDonation->status);
    }

    /**
     * @since 4.10.0
     */
    public function testShouldMoveDonationToTrashByDefaultWhenForceNotSpecified()
    {
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);

        $donationId = $donation->id;

        // Verify donation exists and is not trashed
        $this->assertNotNull(Donation::find($donationId));
        $this->assertNotEquals('trash', $donation->status);

        $mockRequest = $this->getMockRequest('delete');
        $mockRequest->set_param('ids', (string)$donationId);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        // Check if the response is successful (200)
        $this->assertEquals(200, $response->get_status());

        // Verify donation is moved to trash (soft delete)
        $trashedDonation = Donation::find($donationId);
        $this->assertNotNull($trashedDonation);
        $this->assertEquals('trash', $trashedDonation->status);
    }

    /**
     * @since 4.10.0
     */
    public function testShouldPermanentlyDeleteMultipleDonationsWithForceTrue()
    {
        $campaign = Campaign::factory()->create();
        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);
        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);
        $donation3 = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);

        $donationIds = [$donation1->id, $donation2->id, $donation3->id];

        // Verify donations exist
        foreach ($donationIds as $id) {
            $this->assertNotNull(Donation::find($id));
        }

        $mockRequest = $this->getMockRequest('delete');
        $mockRequest->set_param('ids', implode(',', $donationIds));
        $mockRequest->set_param('force', true);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(3, $response->data['successes']);
        $this->assertEmpty($response->data['errors']);

        // Verify all donations are permanently deleted
        foreach ($donationIds as $id) {
            $this->assertNull(Donation::find($id));
        }
    }

    /**
     * @since 4.10.0
     */
    public function testShouldMoveMultipleDonationsToTrashWithForceFalse()
    {
        $campaign = Campaign::factory()->create();
        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);
        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);
        $donation3 = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);

        $donationIds = [$donation1->id, $donation2->id, $donation3->id];

        // Verify donations exist and are not trashed
        foreach ($donationIds as $id) {
            $donation = Donation::find($id);
            $this->assertNotNull($donation);
            $this->assertNotEquals('trash', $donation->status);
        }

        $mockRequest = $this->getMockRequest('delete');
        $mockRequest->set_param('ids', implode(',', $donationIds));
        $mockRequest->set_param('force', false);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(3, $response->data['successes']);
        $this->assertEmpty($response->data['errors']);

        // Verify all donations are moved to trash
        foreach ($donationIds as $id) {
            $trashedDonation = Donation::find($id);
            $this->assertNotNull($trashedDonation);
            $this->assertEquals('trash', $trashedDonation->status);
        }
    }

    /**
     * @since 4.10.0
     */
    public function testShouldReturnErrorWhenDonationNotFound()
    {
        $nonExistentId = 99999;

        // Verify donation doesn't exist
        $this->assertNull(Donation::find($nonExistentId));

        $mockRequest = $this->getMockRequest('delete');
        $mockRequest->set_param('ids', (string)$nonExistentId);
        $mockRequest->set_param('force', true);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(404, $response->get_status());
        $this->assertEquals('Donation not found', $response->data['message']);
    }

    /**
     * @since 4.10.0
     */
    public function testShouldReturnErrorWhenDonationNotFoundForTrash()
    {
        $nonExistentId = 99999;

        // Verify donation doesn't exist
        $this->assertNull(Donation::find($nonExistentId));

        $mockRequest = $this->getMockRequest('delete');
        $mockRequest->set_param('ids', (string)$nonExistentId);
        $mockRequest->set_param('force', false);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(404, $response->get_status());
        $this->assertEquals('Donation not found', $response->data['message']);
    }

    /**
     * @since 4.10.0
     */
    public function testShouldHandleMixedValidAndInvalidDonationIds()
    {
        $campaign = Campaign::factory()->create();
        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);
        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);

        $validIds = [$donation1->id, $donation2->id];
        $invalidId = 99999;
        $allIds = array_merge($validIds, [$invalidId]);

        $mockRequest = $this->getMockRequest('delete');
        $mockRequest->set_param('ids', implode(',', $allIds));
        $mockRequest->set_param('force', true);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        // Should return 404 for the first invalid donation found
        $this->assertEquals(404, $response->get_status());
        $this->assertEquals('Donation not found', $response->data['message']);
    }

    /**
     * @since 4.10.0
     */
    public function getMockRequest(string $action = 'delete'): WP_REST_Request
    {
        $request = new WP_REST_Request(
            WP_REST_Server::DELETABLE,
            "/give-api/v2/admin/donations/{$action}"
        );

        // Set the action parameter
        $request->set_param('action', $action);

        return $request;
    }
}

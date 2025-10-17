<?php

namespace Give\Tests\Unit\Donations\Endpoints;

use Give\Campaigns\Models\Campaign;
use Give\Donations\Endpoints\DonationActions;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class TestListDonationsActions extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();

        // Set up user with proper permissions
        $this->setUpAdminUser();

        Donation::query()->delete();
    }

    /**
     * @unreleased
     */
    private function setUpAdminUser(): void
    {
        $user = wp_get_current_user();
        $user->add_cap('delete_give_payments');
        $user->add_cap('edit_give_payments');
        $user->add_cap('view_give_payments');
    }

    // ===================
    // DELETE ACTION TESTS
    // ===================

    /**
     * @unreleased
     */
    public function testShouldPermanentlyDeleteDonation()
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

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());

        $this->assertTrue(
            in_array($donationId, $response->data['successes']) || in_array((string)$donationId, $response->data['successes']),
            'Expected donation ID ' . $donationId . ' in successes but got: ' . json_encode($response->data)
        );
        $this->assertEmpty($response->data['errors'], 'Expected no errors but got: ' . json_encode($response->data['errors']));

        // Verify donation is permanently deleted
        $this->assertNull(Donation::find($donationId));
    }

    /**
     * @unreleased
     */
    public function testShouldPermanentlyDeleteMultipleDonations()
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
     * @unreleased
     */
    public function testShouldReturnErrorWhenDonationNotFoundForDelete()
    {
        $nonExistentId = 99999;

        // Verify donation doesn't exist
        $this->assertNull(Donation::find($nonExistentId));

        $mockRequest = $this->getMockRequest('delete');
        $mockRequest->set_param('ids', (string)$nonExistentId);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertTrue(
            in_array($nonExistentId, $response->data['errors']) || in_array((string)$nonExistentId, $response->data['errors'])
        );
        $this->assertEmpty($response->data['successes']);
    }

    /**
     * @unreleased
     */
    public function testShouldHandleMixedValidAndInvalidDonationIdsForDelete()
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

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(2, $response->data['successes']);
        $this->assertCount(1, $response->data['errors']);
        $this->assertTrue(
            in_array($invalidId, $response->data['errors']) || in_array((string)$invalidId, $response->data['errors'])
        );
    }

    // ==================
    // TRASH ACTION TESTS
    // ==================

    /**
     * @unreleased
     */
    public function testShouldMoveDonationToTrash()
    {
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->create([
            'campaignId' => $campaign->id
        ]);

        $donationId = $donation->id;

        // Verify donation exists and is not trashed
        $this->assertNotNull(Donation::find($donationId));
        $this->assertNotEquals('trash', $donation->status->getValue());

        $mockRequest = $this->getMockRequest('trash');
        $mockRequest->set_param('ids', (string)$donationId);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertTrue(
            in_array($donationId, $response->data['successes']) || in_array((string)$donationId, $response->data['successes'])
        );
        $this->assertEmpty($response->data['errors']);

        // Verify donation is moved to trash
        $trashedDonation = Donation::find($donationId);
        $this->assertNotNull($trashedDonation);
        $this->assertEquals('trash', $trashedDonation->status->getValue());
    }

    /**
     * @unreleased
     */
    public function testShouldMoveMultipleDonationsToTrash()
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
            $this->assertNotEquals('trash', $donation->status->getValue());
        }

        $mockRequest = $this->getMockRequest('trash');
        $mockRequest->set_param('ids', implode(',', $donationIds));

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(3, $response->data['successes']);
        $this->assertEmpty($response->data['errors']);

        // Verify all donations are moved to trash
        foreach ($donationIds as $id) {
            $trashedDonation = Donation::find($id);
            $this->assertNotNull($trashedDonation);
            $this->assertEquals('trash', $trashedDonation->status->getValue());
        }
    }

    /**
     * @unreleased
     */
    public function testShouldReturnErrorWhenDonationNotFoundForTrash()
    {
        $nonExistentId = 99999;

        // Verify donation doesn't exist
        $this->assertNull(Donation::find($nonExistentId));

        $mockRequest = $this->getMockRequest('trash');
        $mockRequest->set_param('ids', (string)$nonExistentId);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertTrue(
            in_array($nonExistentId, $response->data['errors']) || in_array((string)$nonExistentId, $response->data['errors'])
        );
        $this->assertEmpty($response->data['successes']);
    }

    /**
     * @unreleased
     */
    public function testShouldHandleMixedValidAndInvalidDonationIdsForTrash()
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

        $mockRequest = $this->getMockRequest('trash');
        $mockRequest->set_param('ids', implode(',', $allIds));

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(2, $response->data['successes']);
        $this->assertCount(1, $response->data['errors']);
        $this->assertTrue(
            in_array($invalidId, $response->data['errors']) || in_array((string)$invalidId, $response->data['errors'])
        );
    }

    // =====================
    // UNTRASH ACTION TESTS
    // =====================

    /**
     * @unreleased
     */
    public function testShouldRestoreDonationFromTrash()
    {
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::TRASH()
        ]);

        $donationId = $donation->id;

        // Verify donation exists and is trashed
        $this->assertNotNull(Donation::find($donationId));
        $this->assertEquals('trash', $donation->status->getValue());

        $mockRequest = $this->getMockRequest('untrash');
        $mockRequest->set_param('ids', (string)$donationId);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertTrue(
            in_array($donationId, $response->data['successes']) || in_array((string)$donationId, $response->data['successes'])
        );
        $this->assertEmpty($response->data['errors']);

        // Verify donation is restored from trash
        $restoredDonation = Donation::find($donationId);
        $this->assertNotNull($restoredDonation);
        $this->assertNotEquals('trash', $restoredDonation->status->getValue());
    }

    /**
     * @unreleased
     */
    public function testShouldRestoreMultipleDonationsFromTrash()
    {
        $campaign = Campaign::factory()->create();
        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::TRASH()
        ]);
        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::TRASH()
        ]);
        $donation3 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::TRASH()
        ]);

        $donationIds = [$donation1->id, $donation2->id, $donation3->id];

        // Verify donations exist and are trashed
        foreach ($donationIds as $id) {
            $donation = Donation::find($id);
            $this->assertNotNull($donation);
            $this->assertEquals('trash', $donation->status->getValue());
        }

        $mockRequest = $this->getMockRequest('untrash');
        $mockRequest->set_param('ids', implode(',', $donationIds));

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(3, $response->data['successes']);
        $this->assertEmpty($response->data['errors']);

        // Verify all donations are restored from trash
        foreach ($donationIds as $id) {
            $restoredDonation = Donation::find($id);
            $this->assertNotNull($restoredDonation);
            $this->assertNotEquals('trash', $restoredDonation->status->getValue());
        }
    }

    /**
     * @unreleased
     */
    public function testShouldReturnErrorWhenDonationNotFoundForUntrash()
    {
        $nonExistentId = 99999;

        // Verify donation doesn't exist
        $this->assertNull(Donation::find($nonExistentId));

        $mockRequest = $this->getMockRequest('untrash');
        $mockRequest->set_param('ids', (string)$nonExistentId);

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertTrue(
            in_array($nonExistentId, $response->data['errors']) || in_array((string)$nonExistentId, $response->data['errors'])
        );
        $this->assertEmpty($response->data['successes']);
    }

    /**
     * @unreleased
     */
    public function testShouldHandleMixedValidAndInvalidDonationIdsForUntrash()
    {
        $campaign = Campaign::factory()->create();
        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::TRASH()
        ]);
        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'status' => DonationStatus::TRASH()
        ]);

        $validIds = [$donation1->id, $donation2->id];
        $invalidId = 99999;
        $allIds = array_merge($validIds, [$invalidId]);

        $mockRequest = $this->getMockRequest('untrash');
        $mockRequest->set_param('ids', implode(',', $allIds));

        $donationActions = give(DonationActions::class);
        $response = $donationActions->handleRequest($mockRequest);

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(2, $response->data['successes']);
        $this->assertCount(1, $response->data['errors']);
        $this->assertTrue(
            in_array($invalidId, $response->data['errors']) || in_array((string)$invalidId, $response->data['errors'])
        );
    }

    /**
     * @unreleased
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

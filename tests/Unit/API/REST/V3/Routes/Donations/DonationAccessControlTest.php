<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * Tests for SVUL-74: Upfront view-permission guard in DonationController::validationForGetMethods().
 *
 * Covers Fix 3:
 * - All donation GET requests now require the view_give_payments capability.
 * - Unauthenticated users receive 401; authenticated non-admins receive 403.
 * - Admins (with view_give_payments) receive 200.
 *
 * @unreleased
 */
final class DonationAccessControlTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    private function collectionRoute(): string
    {
        return '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE;
    }

    private function itemRoute(int $donationId): string
    {
        return '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donationId;
    }

    // --- GET /donations (collection) ---

    /**
     * @unreleased
     */
    public function testUnauthenticatedUserCannotGetDonationsCollection(): void
    {
        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->collectionRoute())
        );

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testAuthenticatedNonAdminCannotGetDonationsCollection(): void
    {
        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->collectionRoute(), [], 'subscriber')
        );

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * @unreleased
     */
    public function testAdminCanGetDonationsCollection(): void
    {
        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->collectionRoute(), [], 'administrator')
        );

        $this->assertEquals(200, $response->get_status());
    }

    // --- GET /donations/{id} (single item) ---

    /**
     * @unreleased
     */
    public function testUnauthenticatedUserCannotGetSingleDonation(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->itemRoute($donation->id))
        );

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testAuthenticatedNonAdminCannotGetSingleDonation(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->itemRoute($donation->id), [], 'subscriber')
        );

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * @unreleased
     */
    public function testAdminCanGetSingleDonation(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->itemRoute($donation->id), [], 'administrator')
        );

        $this->assertEquals(200, $response->get_status());
    }
}

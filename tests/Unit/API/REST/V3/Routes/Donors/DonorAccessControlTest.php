<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donors;

use Faker\Factory;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donors\Models\Donor;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Tests for SVUL-74: Upfront isAdmin/isOwner guard in DonorPermissions::validationForGetMethods().
 *
 * Covers Fix 4:
 * - All donor GET requests now require either admin (view_give_donors) or donor ownership.
 * - Unauthenticated users receive 401; authenticated non-admin non-owners receive 403.
 * - Admins and the donor's linked WordPress user receive 200.
 *
 * @unreleased
 */
final class DonorAccessControlTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    private function collectionRoute(): string
    {
        return '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE;
    }

    private function itemRoute(int $donorId): string
    {
        return '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donorId;
    }

    // --- GET /donors (collection) ---

    /**
     * @unreleased
     */
    public function testUnauthenticatedUserCannotGetDonorsCollection(): void
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
    public function testAuthenticatedNonAdminCannotGetDonorsCollection(): void
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
    public function testAdminCanGetDonorsCollection(): void
    {
        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->collectionRoute(), [], 'administrator')
        );

        $this->assertEquals(200, $response->get_status());
    }

    // --- GET /donors/{id} (single item) ---

    /**
     * @unreleased
     */
    public function testUnauthenticatedUserCannotGetSingleDonor(): void
    {
        $donor = Donor::factory()->create();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->itemRoute($donor->id))
        );

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testAdminCanGetSingleDonor(): void
    {
        $donor = Donor::factory()->create();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->itemRoute($donor->id), [], 'administrator')
        );

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * A non-admin subscriber who is not the donor owner is rejected.
     *
     * @unreleased
     */
    public function testAuthenticatedNonAdminCannotGetAnotherUsersDonor(): void
    {
        $faker = Factory::create();

        $ownerUser = $this->factory()->user->create([
            'role'       => 'subscriber',
            'user_login' => $faker->unique()->userName(),
            'user_email' => $faker->unique()->safeEmail(),
        ]);

        $donor = Donor::factory()->create(['userId' => $ownerUser]);

        // Make request as a different subscriber (not the owner)
        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->itemRoute($donor->id), [], 'subscriber')
        );

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * A subscriber who owns the donor record can read their own data.
     *
     * @unreleased
     */
    public function testDonorOwnerCanGetOwnDonorRecord(): void
    {
        $faker = Factory::create();

        $userId = $this->factory()->user->create([
            'role'       => 'subscriber',
            'user_login' => $faker->unique()->userName(),
            'user_email' => $faker->unique()->safeEmail(),
        ]);

        $donor = Donor::factory()->create(['userId' => $userId]);

        wp_set_current_user($userId);
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $this->itemRoute($donor->id));

        $this->assertEquals(200, $this->dispatchRequest($request)->get_status());
    }

    /**
     * A subscriber who is logged in but does not own the requested donor record is rejected.
     *
     * @unreleased
     */
    public function testNonOwnerSubscriberCannotGetAnotherDonorRecord(): void
    {
        $faker = Factory::create();

        $ownerUserId = $this->factory()->user->create([
            'role'       => 'subscriber',
            'user_login' => $faker->unique()->userName(),
            'user_email' => $faker->unique()->safeEmail(),
        ]);

        $otherUserId = $this->factory()->user->create([
            'role'       => 'subscriber',
            'user_login' => $faker->unique()->userName(),
            'user_email' => $faker->unique()->safeEmail(),
        ]);

        $donor = Donor::factory()->create(['userId' => $ownerUserId]);

        wp_set_current_user($otherUserId);
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $this->itemRoute($donor->id));

        $response = $this->dispatchRequest($request);

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }
}

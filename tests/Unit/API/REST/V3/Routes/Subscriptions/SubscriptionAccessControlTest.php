<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Subscriptions;

use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * Tests for SVUL-74: Upfront admin-only guard in SubscriptionPermissions::validationForGetMethods().
 *
 * Covers Fix 5:
 * - All subscription GET requests now require the view_give_payments capability.
 * - Unauthenticated users receive 401; authenticated non-admins receive 403.
 * - Admins receive 200.
 *
 * @since TBD
 */
final class SubscriptionAccessControlTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    private function collectionRoute(): string
    {
        return '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
    }

    private function itemRoute(int $subscriptionId): string
    {
        return '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscriptionId;
    }

    // --- GET /subscriptions (collection) ---

    /**
     * @since TBD
     */
    public function testUnauthenticatedUserCannotGetSubscriptionsCollection(): void
    {
        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->collectionRoute())
        );

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @since TBD
     */
    public function testAuthenticatedNonAdminCannotGetSubscriptionsCollection(): void
    {
        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->collectionRoute(), [], 'subscriber')
        );

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * @since TBD
     */
    public function testAdminCanGetSubscriptionsCollection(): void
    {
        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->collectionRoute(), [], 'administrator')
        );

        $this->assertEquals(200, $response->get_status());
    }

    // --- GET /subscriptions/{id} (single item) ---

    /**
     * @since TBD
     */
    public function testUnauthenticatedUserCannotGetSingleSubscription(): void
    {
        $subscription = $this->createSubscription();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->itemRoute($subscription->id))
        );

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @since TBD
     */
    public function testAuthenticatedNonAdminCannotGetSingleSubscription(): void
    {
        $subscription = $this->createSubscription();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->itemRoute($subscription->id), [], 'subscriber')
        );

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * @since TBD
     */
    public function testAdminCanGetSingleSubscription(): void
    {
        $subscription = $this->createSubscription();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->itemRoute($subscription->id), [], 'administrator')
        );

        $this->assertEquals(200, $response->get_status());
    }

    private function createSubscription(string $mode = 'live', string $status = 'active', int $amount = 10000): Subscription
    {
        $donor = Donor::factory()->create();

        return Subscription::factory()->createWithDonation([
            'gatewayId'    => TestGateway::id(),
            'amount'       => new Money($amount, 'USD'),
            'status'       => new SubscriptionStatus($status),
            'period'       => SubscriptionPeriod::MONTH(),
            'frequency'    => 1,
            'installments' => 0,
            'mode'         => new SubscriptionMode($mode),
            'donorId'      => $donor->id,
        ], [
            'anonymous' => false,
            'donorId'   => $donor->id,
        ]);
    }
}

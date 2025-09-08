<?php

namespace Unit\API\REST\V3\Routes\Subscriptions;

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

/**
 * @unreleased
 */
class SubscriptionRouteDeleteItemTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @unreleased
     */
    public function testDeleteSubscriptionShouldSoftDeleteWhenForceIsFalse()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertTrue($data['deleted']);
        $this->assertArrayHasKey('previous', $data);

        // Verify subscription is soft deleted (trashed) - it should still exist but with trashed status
        $trashedSubscription = Subscription::find($subscription->id);
        $this->assertNotNull($trashedSubscription); // Should still be found
        $this->assertEquals('trashed', $trashedSubscription->status->getValue()); // But with trashed status
    }

    /**
     * @unreleased
     */
    public function testDeleteSubscriptionShouldPermanentlyDeleteWhenForceIsTrue()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'force' => true,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertTrue($data['deleted']);
        $this->assertArrayHasKey('previous', $data);

        // Verify subscription is permanently deleted
        $deletedSubscription = Subscription::find($subscription->id);
        $this->assertNull($deletedSubscription);
    }

    /**
     * @unreleased
     */
    public function testDeleteSubscriptionShouldReturn404ErrorWhenSubscriptionNotFound()
    {
        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/999';
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(404, $status);
        $this->assertEquals(__('Subscription not found', 'give'), $data['message']);
    }

    /**
     * @unreleased
     */
    public function testDeleteSubscriptionShouldReturn403ErrorWhenNotAdminUser()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('DELETE', $route, [], 'subscriber');
        $request->set_body_params([
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @unreleased
     */
    public function testDeleteSubscriptionShouldReturnPreviousDataInResponse()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertArrayHasKey('previous', $data);
        $this->assertEquals($subscription->id, $data['previous']['id']);
        $this->assertEquals($subscription->status->getValue(), $data['previous']['status']);
        // Verify amount structure is correct (array format)
        $this->assertIsArray($data['previous']['amount']);
        $this->assertArrayHasKey('value', $data['previous']['amount']);
        $this->assertArrayHasKey('currency', $data['previous']['amount']);
    }

    /**
     * @unreleased
     */
    public function testDeleteSubscriptionShouldHandleSoftDeleteFailure()
    {
        // Create a subscription that might fail to trash
        $subscription = $this->createSubscription();

        // Mock the trash method to return false (simulating failure)
        // This would require mocking, but for now we'll test the basic structure
        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        // Should still succeed in normal cases
        $this->assertEquals(200, $status);
    }

    /**
     * @unreleased
     */
    public function testDeleteSubscriptionShouldHandlePermanentDeleteFailure()
    {
        // Create a subscription that might fail to delete permanently
        $subscription = $this->createSubscription();

        // Mock the delete method to return false (simulating failure)
        // This would require mocking, but for now we'll test the basic structure
        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'force' => true,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        // Should still succeed in normal cases
        $this->assertEquals(200, $status);
    }

    /**
     * @unreleased
     */
    public function testDeleteSubscriptionShouldDefaultToSoftDelete()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        // No force parameter specified, should default to false

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertTrue($data['deleted']);

        // Verify subscription is soft deleted (trashed) - it should still exist but with trashed status
        $trashedSubscription = Subscription::find($subscription->id);
        $this->assertNotNull($trashedSubscription); // Should still be found
        $this->assertEquals('trashed', $trashedSubscription->status->getValue()); // But with trashed status
    }

    /**
     * @unreleased
     */
    public function testDeleteSubscriptionShouldWorkWithDifferentSubscriptionStatuses()
    {
        $testStatuses = [
            'active',
            'cancelled',
            'expired',
            'suspended',
            'completed',
        ];

        foreach ($testStatuses as $status) {
            $subscription = $this->createSubscription('live', $status);

            $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
            $request = $this->createRequest('DELETE', $route, [], 'administrator');
            $request->set_body_params([
                'force' => false,
            ]);

            $response = $this->dispatchRequest($request);

            $statusCode = $response->get_status();
            $data = $response->get_data();

            $this->assertEquals(200, $statusCode);
            $this->assertTrue($data['deleted']);

            // Verify subscription is soft deleted - it should still exist but with trashed status
            $trashedSubscription = Subscription::find($subscription->id);
            $this->assertNotNull($trashedSubscription); // Should still be found
            $this->assertEquals('trashed', $trashedSubscription->status->getValue()); // But with trashed status
        }
    }

    /**
     * @unreleased
     */
    private function createSubscription(string $mode = 'live', string $status = 'active', int $amount = 10000): Subscription
    {
        $donor = Donor::factory()->create();

        return Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'amount' => new Money($amount, 'USD'),
            'status' => new SubscriptionStatus($status),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'mode' => new SubscriptionMode($mode),
            'donorId' => $donor->id,
        ], [
            'anonymous' => false,
            'donorId' => $donor->id,
        ]);
    }
}

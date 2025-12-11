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
 * @since 4.8.0
 */
class SubscriptionRouteDeleteItemsTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldSoftDeleteMultipleSubscriptions()
    {
        $subscription1 = $this->createSubscription();
        $subscription2 = $this->createSubscription();
        $subscription3 = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'ids' => [$subscription1->id, $subscription2->id, $subscription3->id],
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertArrayHasKey('deleted', $data);
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('total_requested', $data);
        $this->assertArrayHasKey('total_deleted', $data);
        $this->assertArrayHasKey('total_errors', $data);

        $this->assertEquals(3, $data['total_requested']);
        $this->assertEquals(3, $data['total_deleted']);
        $this->assertEquals(0, $data['total_errors']);

        // Verify all subscriptions are soft deleted (status changed to trashed)
        $this->assertEquals('trashed', Subscription::find($subscription1->id)->status->getValue());
        $this->assertEquals('trashed', Subscription::find($subscription2->id)->status->getValue());
        $this->assertEquals('trashed', Subscription::find($subscription3->id)->status->getValue());
    }

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldPermanentlyDeleteMultipleSubscriptions()
    {
        $subscription1 = $this->createSubscription();
        $subscription2 = $this->createSubscription();
        $subscription3 = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'ids' => [$subscription1->id, $subscription2->id, $subscription3->id],
            'force' => true,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals(3, $data['total_requested']);
        $this->assertEquals(3, $data['total_deleted']);
        $this->assertEquals(0, $data['total_errors']);

        // Verify all subscriptions are permanently deleted
        $this->assertNull(Subscription::find($subscription1->id));
        $this->assertNull(Subscription::find($subscription2->id));
        $this->assertNull(Subscription::find($subscription3->id));
    }

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldHandleMixedValidAndInvalidIds()
    {
        $subscription1 = $this->createSubscription();
        $subscription2 = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'ids' => [$subscription1->id, 999, $subscription2->id, 888],
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals(4, $data['total_requested']);
        $this->assertEquals(2, $data['total_deleted']);
        $this->assertEquals(2, $data['total_errors']);

        // Verify valid subscriptions are soft deleted (status changed to trashed)
        $this->assertEquals('trashed', Subscription::find($subscription1->id)->status->getValue());
        $this->assertEquals('trashed', Subscription::find($subscription2->id)->status->getValue());

        // Verify error messages for invalid IDs
        $this->assertCount(2, $data['errors']);
        foreach ($data['errors'] as $error) {
            $this->assertArrayHasKey('id', $error);
            $this->assertArrayHasKey('message', $error);
            $this->assertEquals(__('Subscription not found', 'give'), $error['message']);
        }
    }

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldReturn403ErrorWhenNotAdminUser()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'subscriber');
        $request->set_body_params([
            'ids' => [$subscription->id],
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldReturnPreviousDataForDeletedItems()
    {
        $subscription1 = $this->createSubscription();
        $subscription2 = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'ids' => [$subscription1->id, $subscription2->id],
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertCount(2, $data['deleted']);

        foreach ($data['deleted'] as $deletedItem) {
            $this->assertArrayHasKey('id', $deletedItem);
            $this->assertArrayHasKey('previous', $deletedItem);
            $this->assertArrayHasKey('id', $deletedItem['previous']);
            $this->assertArrayHasKey('status', $deletedItem['previous']);
            $this->assertArrayHasKey('amount', $deletedItem['previous']);

            // Verify amount structure is correct (array format)
            $this->assertIsArray($deletedItem['previous']['amount']);
            $this->assertArrayHasKey('value', $deletedItem['previous']['amount']);
            $this->assertArrayHasKey('currency', $deletedItem['previous']['amount']);
        }
    }

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldHandleEmptyIdsArray()
    {
        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'ids' => [],
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals(0, $data['total_requested']);
        $this->assertEquals(0, $data['total_deleted']);
        $this->assertEquals(0, $data['total_errors']);
    }

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldHandleAllInvalidIds()
    {
        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'ids' => [999, 888, 777],
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals(3, $data['total_requested']);
        $this->assertEquals(0, $data['total_deleted']);
        $this->assertEquals(3, $data['total_errors']);
        $this->assertCount(3, $data['errors']);
    }

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldWorkWithDifferentSubscriptionStatuses()
    {
        $subscription1 = $this->createSubscription('live', 'active');
        $subscription2 = $this->createSubscription('live', 'cancelled');
        $subscription3 = $this->createSubscription('live', 'expired');

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'ids' => [$subscription1->id, $subscription2->id, $subscription3->id],
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals(3, $data['total_requested']);
        $this->assertEquals(3, $data['total_deleted']);
        $this->assertEquals(0, $data['total_errors']);

        // Verify all subscriptions are soft deleted (status changed to trashed)
        $this->assertEquals('trashed', Subscription::find($subscription1->id)->status->getValue());
        $this->assertEquals('trashed', Subscription::find($subscription2->id)->status->getValue());
        $this->assertEquals('trashed', Subscription::find($subscription3->id)->status->getValue());
    }

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldHandleLargeNumberOfSubscriptions()
    {
        $subscriptions = [];
        for ($i = 0; $i < 10; $i++) {
            $subscriptions[] = $this->createSubscription();
        }

        $ids = array_map(function ($subscription) {
            return $subscription->id;
        }, $subscriptions);

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'ids' => $ids,
            'force' => false,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals(10, $data['total_requested']);
        $this->assertEquals(10, $data['total_deleted']);
        $this->assertEquals(0, $data['total_errors']);

        // Verify all subscriptions are soft deleted (status changed to trashed)
        foreach ($subscriptions as $subscription) {
            $this->assertEquals('trashed', Subscription::find($subscription->id)->status->getValue());
        }
    }

    /**
     * @since 4.8.0
     */
    public function testDeleteItemsShouldDefaultToSoftDelete()
    {
        $subscription1 = $this->createSubscription();
        $subscription2 = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_body_params([
            'ids' => [$subscription1->id, $subscription2->id],
            // No force parameter specified, should default to false
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals(2, $data['total_deleted']);

        // Verify subscriptions are soft deleted (status changed to trashed)
        $this->assertEquals('trashed', Subscription::find($subscription1->id)->status->getValue());
        $this->assertEquals('trashed', Subscription::find($subscription2->id)->status->getValue());
    }

    /**
     * @since 4.8.0
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

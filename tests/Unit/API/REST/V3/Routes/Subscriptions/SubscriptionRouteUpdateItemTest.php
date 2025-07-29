<?php

namespace Unit\API\REST\V3\Routes\Subscriptions;

use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;

/**
 * @unreleased
 */
class SubscriptionRouteUpdateItemTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldUpdateModelProperties()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'status' => 'cancelled',
            'frequency' => 2,
            'installments' => 12,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals('cancelled', $data['status']);
        $this->assertEquals(2, $data['frequency']);
        $this->assertEquals(12, $data['installments']);
    }

    /**
     * @unreleased
     */
    /*public function testUpdateSubscriptionShouldNotUpdateNonEditableFields()
    {
        $subscription = $this->createSubscription();
        $originalId = $subscription->id;
        $originalCreatedAt = $subscription->createdAt;
        $originalMode = $subscription->mode->getValue();
        $originalGatewayId = $subscription->gatewayId;
        $originalGatewaySubscriptionId = $subscription->gatewaySubscriptionId;

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'id' => 999,
            'createdAt' => '2024-01-01',
            'mode' => 'test',
            'gatewayId' => 'test_gateway',
            'gatewaySubscriptionId' => 'test_subscription_id',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($originalId, $data['id']);
        $this->assertEquals($originalCreatedAt, $data['createdAt']);
        $this->assertEquals($originalMode, $data['mode']);
        $this->assertEquals($originalGatewayId, $data['gatewayId']);
        $this->assertEquals($originalGatewaySubscriptionId, $data['gatewaySubscriptionId']);
    }*/

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldReturn404ErrorWhenSubscriptionNotFound()
    {
        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/999';
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'status' => 'cancelled',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldReturn403ErrorWhenNotAdminUser()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('PUT', $route, [], 'subscriber');
        $request->set_body_params([
            'status' => 'cancelled',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldPersistStatusChanges()
    {
        $subscription = $this->createSubscription();

        $testStatuses = [
            'active',
            'cancelled',
            'expired',
            'suspended',
            'completed',
        ];

        foreach ($testStatuses as $status) {
            $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
            $request = $this->createRequest('PUT', $route, [], 'administrator');
            $request->set_body_params([
                'status' => $status,
            ]);

            $response = $this->dispatchRequest($request);
            $data = $response->get_data();

            $this->assertEquals(200, $response->get_status());
            $this->assertEquals($status, $data['status']);

            // Verify persistence in database
            $updatedSubscription = Subscription::find($subscription->id);
            $this->assertEquals($status, $updatedSubscription->status->getValue());
        }
    }

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldPersistFrequencyChanges()
    {
        $subscription = $this->createSubscription();

        $testFrequencies = [
            1,   // monthly
            2,   // bi-monthly
            3,   // quarterly
            6,   // semi-annually
            12,  // annually
        ];

        foreach ($testFrequencies as $frequency) {
            $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
            $request = $this->createRequest('PUT', $route, [], 'administrator');
            $request->set_body_params([
                'frequency' => $frequency,
            ]);

            $response = $this->dispatchRequest($request);
            $data = $response->get_data();

            $this->assertEquals(200, $response->get_status());
            $this->assertEquals($frequency, $data['frequency']);

            // Verify persistence in database
            $updatedSubscription = Subscription::find($subscription->id);
            $this->assertEquals($frequency, $updatedSubscription->frequency);
        }
    }

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldPersistInstallmentsChanges()
    {
        $subscription = $this->createSubscription();

        $testInstallments = [
            0,   // unlimited
            1,   // one-time
            6,   // 6 payments
            12,  // 12 payments
            24,  // 24 payments
        ];

        foreach ($testInstallments as $installments) {
            $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
            $request = $this->createRequest('PUT', $route, [], 'administrator');
            $request->set_body_params([
                'installments' => $installments,
            ]);

            $response = $this->dispatchRequest($request);
            $data = $response->get_data();

            $this->assertEquals(200, $response->get_status());
            $this->assertEquals($installments, $data['installments']);

            // Verify persistence in database
            $updatedSubscription = Subscription::find($subscription->id);
            $this->assertEquals($installments, $updatedSubscription->installments);
        }
    }

    /**
     * @unreleased
     */
    /*public function testUpdateSubscriptionShouldHandleInvalidStatusValues()
    {
        $subscription = $this->createSubscription();
        $originalStatus = $subscription->status->getValue();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'status' => 'invalid_status',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        // Should not change the status if invalid
        $this->assertEquals($originalStatus, $data['status']);
    }*/

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldHandleMultipleFieldUpdates()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'status' => 'cancelled',
            'frequency' => 3,
            'installments' => 6,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals('cancelled', $data['status']);
        $this->assertEquals(3, $data['frequency']);
        $this->assertEquals(6, $data['installments']);

        // Verify all changes persisted in database
        $updatedSubscription = Subscription::find($subscription->id);
        $this->assertEquals('cancelled', $updatedSubscription->status->getValue());
        $this->assertEquals(3, $updatedSubscription->frequency);
        $this->assertEquals(6, $updatedSubscription->installments);
    }

    /**
     * @unreleased
     */
    private function createSubscription(string $mode = 'live', string $status = 'active', int $amount = 10000): Subscription
    {
        $donor = \Give\Donors\Models\Donor::factory()->create();

        return Subscription::factory()->createWithDonation([
            'gatewayId' => \Give\PaymentGateways\Gateways\TestGateway\TestGateway::id(),
            'amount' => new Money($amount, 'USD'),
            'status' => new SubscriptionStatus($status),
            'period' => \Give\Subscriptions\ValueObjects\SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'mode' => new \Give\Subscriptions\ValueObjects\SubscriptionMode($mode),
            'donorId' => $donor->id,
        ], [
            'anonymous' => false,
            'donorId' => $donor->id,
        ]);
    }
}
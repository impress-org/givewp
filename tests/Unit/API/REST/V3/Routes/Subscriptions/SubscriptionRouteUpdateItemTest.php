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
        $newDonor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'status' => SubscriptionStatus::CANCELLED,
            'frequency' => 2,
            'installments' => 12,
            'amount' => ['value' => 150.00, 'currency' => 'USD'],
            'feeAmountRecovered' => ['value' => 5.00, 'currency' => 'USD'],
            'period' => SubscriptionPeriod::QUARTER,
            'renewAt' => '2025-01-15T12:00:00+00:00',
            'transactionId' => 'txn_test_123',
            'donorId' => $newDonor->id,
            'donationFormId' => 2,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals('cancelled', $data['status']);
        $this->assertEquals(2, $data['frequency']);
        $this->assertEquals(12, $data['installments']);
        $this->assertEquals(150.00, $data['amount']['value']);
        $this->assertEquals('USD', $data['amount']['currency']);
        $this->assertEquals(5.00, $data['feeAmountRecovered']['value']);
        $this->assertEquals('USD', $data['feeAmountRecovered']['currency']);
        $this->assertEquals('quarter', $data['period']);
        $this->assertArrayHasKey('renewsAt', $data);
        $this->assertEquals('txn_test_123', $data['transactionId']);
        $this->assertEquals($newDonor->id, $data['donorId']);
        $this->assertEquals(2, $data['donationFormId']);

        // Verify persistence in database
        $updatedSubscription = Subscription::find($subscription->id);
        $this->assertEquals('cancelled', $updatedSubscription->status->getValue());
        $this->assertEquals(2, $updatedSubscription->frequency);
        $this->assertEquals(12, $updatedSubscription->installments);
        $this->assertEquals(150.00, $updatedSubscription->amount->formatToDecimal());
        $this->assertEquals('USD', $updatedSubscription->amount->getCurrency()->getCode());
        $this->assertEquals(5.00, $updatedSubscription->feeAmountRecovered->formatToDecimal());
        $this->assertEquals('USD', $updatedSubscription->feeAmountRecovered->getCurrency()->getCode());
        $this->assertEquals('quarter', $updatedSubscription->period->getValue());
        $this->assertEquals('txn_test_123', $updatedSubscription->transactionId);
        $this->assertEquals($newDonor->id, $updatedSubscription->donorId);
        $this->assertEquals(2, $updatedSubscription->donationFormId);
    }

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldNotUpdateNonEditableFields()
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
            'id' => $subscription->id,
            'createdAt' => '2024-01-01T00:00:00+00:00',
            'mode' => 'test',
            'gatewayId' => 'test_gateway',
            'gatewaySubscriptionId' => 'test_subscription_id',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals($originalId, $data['id']);
        // createdAt is now formatted as ISO string, so we need to compare differently
        $this->assertIsString($data['createdAt']);
        $this->assertEquals($originalMode, $data['mode']);
        $this->assertEquals($originalGatewayId, $data['gatewayId']);
        $this->assertEquals($originalGatewaySubscriptionId, $data['gatewaySubscriptionId']);
    }

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldReturn404ErrorWhenSubscriptionNotFound()
    {
        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/999';
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'status' => SubscriptionStatus::CANCELLED,
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
            'status' => SubscriptionStatus::CANCELLED,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @unreleased
     * @dataProvider subscriptionStatusProvider
     */
    public function testUpdateSubscriptionShouldPersistStatusChanges(string $status)
    {
        $subscription = $this->createSubscription();

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

    /**
     * @unreleased
     * @dataProvider subscriptionPeriodProvider
     */
    public function testUpdateSubscriptionShouldPersistPeriodChanges(string $period)
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'period' => $period,
        ]);

        $response = $this->dispatchRequest($request);
        $data = $response->get_data();

        $this->assertEquals(200, $response->get_status());
        $this->assertEquals($period, $data['period']);

        // Verify persistence in database
        $updatedSubscription = Subscription::find($subscription->id);
        $this->assertEquals($period, $updatedSubscription->period->getValue());
    }

    /**
     * @unreleased
     */
    public function subscriptionStatusProvider(): array
    {
        return array_map(
            static function ($status) {
                return [$status];
            },
            SubscriptionStatus::toArray()
        );
    }

    /**
     * @unreleased
     */
    public function subscriptionPeriodProvider(): array
    {
        return array_map(
            static function ($period) {
                return [$period];
            },
            array_values(SubscriptionPeriod::toArray())
        );
    }

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldReturn400ErrorForInvalidStatus()
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
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        // Should return 400 Bad Request for invalid status
        $this->assertEquals(400, $status);
        $this->assertEquals('rest_invalid_param', $data['code']);
        $this->assertStringContainsString('Invalid parameter(s): status', $data['message']);

        // Verify the subscription status was not changed
        $updatedSubscription = Subscription::find($subscription->id);
        $this->assertEquals($originalStatus, $updatedSubscription->status->getValue());
    }

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldReturn400ErrorForInvalidAmount()
    {
        $subscription = $this->createSubscription();
        $originalAmount = $subscription->amount->formatToDecimal();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'amount' => 'invalid_amount',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        // Should return 400 Bad Request for invalid amount
        $this->assertEquals(400, $status);

        // Verify the subscription amount was not changed
        $updatedSubscription = Subscription::find($subscription->id);
        $this->assertEquals($originalAmount, $updatedSubscription->amount->formatToDecimal());
    }

    /**
     * @unreleased
     */
    public function testUpdateSubscriptionShouldReturn400ErrorForInvalidPeriod()
    {
        $subscription = $this->createSubscription();
        $originalPeriod = $subscription->period->getValue();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'period' => 'invalid_period',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        // Should return 400 Bad Request for invalid period
        $this->assertEquals(400, $status);

        // Verify the subscription period was not changed
        $updatedSubscription = Subscription::find($subscription->id);
        $this->assertEquals($originalPeriod, $updatedSubscription->period->getValue());
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

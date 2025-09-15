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
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;

/**
 * @since 4.8.0
 */
class SubscriptionRouteCreateItemTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldCreateModelWithValidData()
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'status' => SubscriptionStatus::ACTIVE,
            'period' => SubscriptionPeriod::MONTH,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
            'installments' => 12,
            'transactionId' => 'txn_test_123',
            'feeAmountRecovered' => ['amount' => 5.00, 'currency' => 'USD'],
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(201, $status);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($donor->id, $data['donorId']);
        $this->assertEquals(1, $data['donationFormId']);
        $this->assertEquals(100.00, $data['amount']->formatToDecimal());
        $this->assertEquals('USD', $data['amount']->getCurrency()->getCode());
        $this->assertEquals('active', $data['status']->getValue());
        $this->assertEquals('month', $data['period']->getValue());
        $this->assertEquals(1, $data['frequency']);
        $this->assertEquals(TestGateway::id(), $data['gatewayId']);
        $this->assertEquals(12, $data['installments']);
        $this->assertEquals('txn_test_123', $data['transactionId']);
        $this->assertEquals(5.00, $data['feeAmountRecovered']->formatToDecimal());
        $this->assertEquals('USD', $data['feeAmountRecovered']->getCurrency()->getCode());

        // Verify persistence in database
        $createdSubscription = Subscription::find($data['id']);
        $this->assertNotNull($createdSubscription);
        $this->assertEquals($donor->id, $createdSubscription->donorId);
        $this->assertEquals(1, $createdSubscription->donationFormId);
        $this->assertEquals(100.00, $createdSubscription->amount->formatToDecimal());
        $this->assertEquals('USD', $createdSubscription->amount->getCurrency()->getCode());
        $this->assertEquals('active', $createdSubscription->status->getValue());
        $this->assertEquals('month', $createdSubscription->period->getValue());
        $this->assertEquals(1, $createdSubscription->frequency);
        $this->assertEquals(12, $createdSubscription->installments);
        $this->assertEquals('txn_test_123', $createdSubscription->transactionId);
        $this->assertEquals(5.00, $createdSubscription->feeAmountRecovered->formatToDecimal());
        $this->assertEquals('USD', $createdSubscription->feeAmountRecovered->getCurrency()->getCode());
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldReturn400ErrorWhenRequiredFieldsMissing()
    {
        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => 1,
            // Missing required fields: donationFormId, amount, status, period, frequency, gatewayId
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Missing parameter(s): donationFormId, amount, status, period, frequency, gatewayId', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldReturn403ErrorWhenNotAdminUser()
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'subscriber');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'status' => SubscriptionStatus::ACTIVE,
            'period' => SubscriptionPeriod::MONTH,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @since 4.8.0
     * @dataProvider subscriptionStatusProvider
     */
    public function testCreateSubscriptionShouldCreateWithValidStatus(string $status)
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'status' => $status,
            'period' => SubscriptionPeriod::MONTH,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
        ]);

        $response = $this->dispatchRequest($request);
        $data = $response->get_data();

        $this->assertEquals(201, $response->get_status());
        $this->assertEquals($status, $data['status']->getValue());

        // Verify persistence in database
        $createdSubscription = Subscription::find($data['id']);
        $this->assertEquals($status, $createdSubscription->status->getValue());
    }

    /**
     * @since 4.8.0
     * @dataProvider subscriptionPeriodProvider
     */
    public function testCreateSubscriptionShouldCreateWithValidPeriod(string $period)
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'status' => SubscriptionStatus::ACTIVE,
            'period' => $period,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
        ]);

        $response = $this->dispatchRequest($request);
        $data = $response->get_data();

        $this->assertEquals(201, $response->get_status());
        $this->assertEquals($period, $data['period']->getValue());

        // Verify persistence in database
        $createdSubscription = Subscription::find($data['id']);
        $this->assertEquals($period, $createdSubscription->period->getValue());
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldReturn400ErrorForInvalidStatus()
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'status' => 'invalid_status',
            'period' => SubscriptionPeriod::MONTH,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Invalid parameter(s): status', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldReturn400ErrorForInvalidAmount()
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => 'invalid_amount',
            'status' => SubscriptionStatus::ACTIVE,
            'period' => SubscriptionPeriod::MONTH,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Invalid parameter(s): amount', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldReturn400ErrorForInvalidPeriod()
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'status' => SubscriptionStatus::ACTIVE,
            'period' => 'invalid_period',
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Invalid parameter(s): period', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldSetDefaultValues()
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'status' => SubscriptionStatus::ACTIVE,
            'period' => SubscriptionPeriod::MONTH,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
            // Not providing installments, createdAt - should use defaults
        ]);

        $response = $this->dispatchRequest($request);
        $data = $response->get_data();

        $this->assertEquals(201, $response->get_status());
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('createdAt', $data);

        // Verify persistence in database
        $createdSubscription = Subscription::find($data['id']);
        $this->assertNotNull($createdSubscription);
        $this->assertNotNull($createdSubscription->createdAt);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldHandleMoneyObjects()
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 150.50, 'currency' => 'USD'],
            'status' => SubscriptionStatus::ACTIVE,
            'period' => SubscriptionPeriod::MONTH,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
            'feeAmountRecovered' => ['amount' => 7.25, 'currency' => 'USD'],
        ]);

        $response = $this->dispatchRequest($request);
        $data = $response->get_data();

        $this->assertEquals(201, $response->get_status());
        $this->assertEquals(150.50, $data['amount']->formatToDecimal());
        $this->assertEquals('USD', $data['amount']->getCurrency()->getCode());
        $this->assertEquals(7.25, $data['feeAmountRecovered']->formatToDecimal());
        $this->assertEquals('USD', $data['feeAmountRecovered']->getCurrency()->getCode());

        // Verify persistence in database
        $createdSubscription = Subscription::find($data['id']);
        $this->assertEquals(150.50, $createdSubscription->amount->formatToDecimal());
        $this->assertEquals('USD', $createdSubscription->amount->getCurrency()->getCode());
        $this->assertEquals(7.25, $createdSubscription->feeAmountRecovered->formatToDecimal());
        $this->assertEquals('USD', $createdSubscription->feeAmountRecovered->getCurrency()->getCode());
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldHandleDateTimeObjects()
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'status' => SubscriptionStatus::ACTIVE,
            'period' => SubscriptionPeriod::MONTH,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
            'renewsAt' => [
                'date' => '2025-01-15T12:00:00.000000',
                'timezone' => 'America/New_York',
                'timezone_type' => 3,
            ],
        ]);

        $response = $this->dispatchRequest($request);
        $data = $response->get_data();

        $this->assertEquals(201, $response->get_status());
        $this->assertArrayHasKey('renewsAt', $data);

        // Verify persistence in database
        $createdSubscription = Subscription::find($data['id']);
        $this->assertNotNull($createdSubscription->renewsAt);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionShouldReturn500ErrorForDatabaseFailure()
    {
        $donor = Donor::factory()->create();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $donor->id,
            'donationFormId' => 1,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'status' => SubscriptionStatus::ACTIVE,
            'period' => SubscriptionPeriod::MONTH,
            'frequency' => 1,
            'gatewayId' => TestGateway::id(),
            // Add invalid data that would cause database failure
            'renewsAt' => 'invalid_date_format', // Invalid date format
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Invalid parameter(s): renewsAt', $data['message']);
    }

    /**
     * @since 4.8.0
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
     * @since 4.8.0
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
}

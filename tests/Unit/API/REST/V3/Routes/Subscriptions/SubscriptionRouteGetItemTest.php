<?php

namespace Unit\API\REST\V3\Routes\Subscriptions;

use Exception;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use WP_REST_Server;
use Give\Donors\Models\Donor;

/**
 * @unreleased
 */
class SubscriptionRouteGetItemTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @unreleased
     */
    public function testGetSubscriptionShouldReturnAllViewModelProperties()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);

        // Verify DateTime object structure for createdAt and renewsAt
        $this->assertIsArray($data['createdAt']);
        $this->assertArrayHasKey('date', $data['createdAt']);
        $this->assertArrayHasKey('timezone', $data['createdAt']);
        $this->assertArrayHasKey('timezone_type', $data['createdAt']);

        $this->assertIsArray($data['renewsAt']);
        $this->assertArrayHasKey('date', $data['renewsAt']);
        $this->assertArrayHasKey('timezone', $data['renewsAt']);
        $this->assertArrayHasKey('timezone_type', $data['renewsAt']);

        $donor = $subscription->donor()->get();

        $this->assertEquals([
            'id' => $subscription->id,
            'donorId' => $subscription->donorId,
            'donationFormId' => $subscription->donationFormId,
            'amount' => $subscription->amount->toArray(),
            'feeAmountRecovered' => $subscription->feeAmountRecovered->toArray(),
            'status' => $subscription->status->getValue(),
            'period' => $subscription->period->getValue(),
            'frequency' => $subscription->frequency,
            'installments' => $subscription->installments,
            'transactionId' => '',
            'gatewayId' => TestGateway::id(),
            'gatewaySubscriptionId' => $subscription->gatewaySubscriptionId,
            'mode' => 'live',
            'createdAt' => $data['createdAt'],
            'renewsAt' => $data['renewsAt'],
            'firstName' => $donor ? $donor->firstName : '',
            'lastName' => $donor ? $donor->lastName : '',
            'gateway' => array_merge(
                $subscription->gateway()->toArray(),
                [
                    'subscriptionUrl' => $subscription->gateway()->gatewayDashboardSubscriptionUrl($subscription),
                ]
            )
        ], $data);
    }

    /**
     * @unreleased
     */
    public function testGetSubscriptionShouldReturnSelfLink()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _links data
        $data = $this->responseToData($response, true);

        $this->assertEquals(200, $status);
        $this->assertEquals($subscription->id, $data['id']);
        $this->assertArrayHasKey('_links', $data);
        $this->assertArrayHasKey('self', $data['_links']);
    }

    /**
     * @unreleased
     */
    public function testGetSubscriptionShouldReturnDonorLink()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _links data
        $data = $this->responseToData($response, true);

        $this->assertEquals(200, $status);
        $this->assertEquals($subscription->id, $data['id']);
        $this->assertArrayHasKey('_links', $data);
        $this->assertArrayHasKey('givewp:donor', $data['_links']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionShouldNotIncludeSensitiveData()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveProperties = [
            'transactionId',
            'gatewaySubscriptionId',
        ];

        $this->assertEquals(200, $status);

        foreach ($sensitiveProperties as $property) {
            $this->assertEmpty($data[$property]);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionShouldIncludeSensitiveData()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveProperties = [
            'transactionId',
            'gatewaySubscriptionId',
        ];

        $this->assertEquals(200, $status);
        $this->assertNotEmpty(array_intersect_key($data, array_flip($sensitiveProperties)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'subscriber');
        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionShouldReturn404ErrorWhenSubscriptionNotFound()
    {
        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/999';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionShouldRedactAnonymousDonor()
    {
        $subscription = $this->createSubscriptionWithAnonymousDonor();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonors' => 'redact',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(0, $data['donorId']);

        $anonymousDataRedacted = [
            'firstName',
            'lastName',
        ];

        foreach ($anonymousDataRedacted as $property) {
            $this->assertEquals(__('anonymous', 'give'), $data[$property]);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception
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

    /**
     * @unreleased
     *
     * @throws Exception
     */
    private function createSubscriptionWithAnonymousDonor(string $mode = 'live', string $status = 'active', int $amount = 10000): Subscription
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
            'anonymous' => true,
            'donorId' => $donor->id,
        ]);
    }
}
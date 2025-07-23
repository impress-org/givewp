<?php

namespace Unit\API\REST\V3\Routes\Subscriptions;

use Exception;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class SubscriptionRouteGetCollectionTest extends RestApiTestCase
{
    use RefreshDatabase;
    /**
     * @unreleased
     */
    public function testGetSubscriptionsShouldReturnAllModelProperties()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetSubscriptionsShouldReturnAllModelProperties',
                'user_pass' => 'testGetSubscriptionsShouldReturnAllModelProperties',
                'user_email' => 'testGetSubscriptionsShouldReturnAllModelProperties@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);        

        $subscription = $this->createSubscriptionWithMode('live');        

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [                
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        // Remove additional property add by the prepare_response_for_collection() method
        unset($data[0]['_links']);

        // TODO: show shape of DateTime objects
        $createdAtJson = json_encode($data[0]['createdAt']);

        $this->assertEquals(200, $status);
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
            'createdAt' => json_decode($createdAtJson, true),
            'renewsAt' => $data[0]['renewsAt'], 
            'gateway' => [
                'id' => 'manual',
                'label' => 'Test Donation',
                'subscriptionUrl' => '',
                'name' => 'Test Donation', 
            ],
        ], $data[0]);
    }

    /**
     * @unreleased
     */
    public function testGetSubscriptionsShouldReturnSelfLink()
    {        
        $subscription = $this->createSubscriptionWithMode('live');

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);        

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _links data
        $data = $this->responseToData($response, true);

        $this->assertEquals(200, $status);
        $this->assertEquals($subscription->id, $data[0]['id']);
        $this->assertArrayHasKey('_links', $data[0]);
        $this->assertArrayHasKey('self', $data[0]['_links']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsShouldNotIncludeSensitiveData()
    {
        $this->createSubscriptionWithMode('live');

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);        

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveProperties = [
            'transactionId',
            'gatewaySubscriptionId',
        ];

        $this->assertEquals(200, $status);

        foreach ($sensitiveProperties as $property) {
            $this->assertEmpty($data[0][$property]);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsShouldIncludeSensitiveData()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetSubscriptionsShouldIncludeSensitiveData',
                'user_pass' => 'testGetSubscriptionsShouldIncludeSensitiveData',
                'user_email' => 'testGetSubscriptionsShouldIncludeSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        $this->createSubscriptionWithMode('live');

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
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
        $this->assertNotEmpty(array_intersect_key($data[0], array_flip($sensitiveProperties)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testGetSubscriptionsShouldReturn403ErrorSensitiveData',
                'user_pass' => 'testGetSubscriptionsShouldReturn403ErrorSensitiveData',
                'user_email' => 'testGetSubscriptionsShouldReturn403ErrorSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        $this->createSubscriptionWithMode('live');

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
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
    public function testGetSubscriptionsWithPagination()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));
        
        $subscription1 = $this->createSubscriptionWithMode('live');
        
        $subscription2 = $this->createSubscriptionWithMode('live');

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [                                
                'page' => 1,
                'per_page' => 1,
                'direction' => 'ASC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $headers = $response->get_headers();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($subscription1->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);

        $request->set_query_params(
            [                                
                'page' => 2,
                'per_page' => 1,
                'direction' => 'ASC',
            ]
        );
        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $headers = $response->get_headers();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($subscription2->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsByDonorId()
    {
        Subscription::query()->delete();        

        $subscription1 = $this->$this->createSubscriptionWithMode('live');
        $subscription2 = $this->$this->createSubscriptionWithMode('live');
        $donor1 = $subscription1->donor;
        $donor2 = $subscription2->donor;

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'donorId' => $donor1->id,
                'direction' => 'ASC',
            ]
        );
        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($subscription1->id, $data[0]['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsByStatus()
    {
        Subscription::query()->delete();

        $subscription1 = $this->$this->createSubscriptionWithMode('live');
        $subscription2 = $this->$this->createSubscriptionWithMode('live');
        

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'status' => ['active'],
                'direction' => 'ASC',
            ]
        );
        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(2, count($data));
        $this->assertEquals($subscription1->id, $data[0]['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsByMode()
    {
        Subscription::query()->delete();

        $subscription1 = $this->createSubscriptionWithMode('test');
        $subscription2 = $this->createSubscriptionWithMode('live');

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'mode' => 'test',
                'direction' => 'ASC',
            ]
        );
        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($subscription1->id, $data[0]['id']);
    }

    /**
     * @unreleased
     *
     * @dataProvider sortableColumnsDataProvider
     *
     * @throws Exception
     */
    public function testGetSubscriptionsSortedByColumns($sortableColumn)
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => $sortableColumn . 'testGetSubscriptionsSortedByColumns',
                'user_pass' => $sortableColumn . 'testGetSubscriptionsSortedByColumns',
                'user_email' => $sortableColumn . 'testGetSubscriptionsSortedByColumns@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $subscription1 = $this->createSubscription1();
        $subscription2 = $this->createSubscription2();
        $subscription3 = $this->createSubscription3();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        /**
         * Ascendant Direction
         */
        $request->set_query_params(
            [
                'includeSensitiveData' => true,
                'page' => 1,
                'per_page' => 30,
                'sort' => $sortableColumn,
                'direction' => 'ASC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(3, count($data));
        $this->assertEquals($subscription1->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($subscription2->{$sortableColumn}, $data[1][$sortableColumn]);
        $this->assertEquals($subscription3->{$sortableColumn}, $data[2][$sortableColumn]);

        /**
         * Descendant Direction
         */
        $request->set_query_params(
            [
                'includeSensitiveData' => true,
                'page' => 1,
                'per_page' => 3,
                'sort' => $sortableColumn,
                'direction' => 'DESC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(3, count($data));
        $this->assertEquals($subscription3->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($subscription2->{$sortableColumn}, $data[1][$sortableColumn]);
        $this->assertEquals($subscription1->{$sortableColumn}, $data[2][$sortableColumn]);
    }

    /**
     * @unreleased
     */
    public function sortableColumnsDataProvider(): array
    {
        return [
            ['id'],
            ['createdAt'],
            ['status'],
            ['amount'],
            ['donorId'],
        ];
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    private function createSubscriptionForDonor(int $donorId): Subscription
    {
        return Subscription::factory()->create([
            'donorId' => $donorId,
            'status' => SubscriptionStatus::ACTIVE(),
            'mode' => new SubscriptionMode('live'),
        ]);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    private function createSubscriptionWithStatus(SubscriptionStatus $status): Subscription
    {
        return Subscription::factory()->create([
            'status' => $status,
            'mode' => new SubscriptionMode('live'),
        ]);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    private function createSubscriptionWithMode(string $mode): Subscription
    {
        return Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(10000, 'USD'),
            'status' => SubscriptionStatus::ACTIVE(),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'transactionId' => 'test-transaction-123',
            'mode' => new SubscriptionMode($mode),
        ]);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createSubscription1(): Subscription
    {
        /** @var Donor $donor1 */
        $donor1 = Donor::factory()->create();

        return Subscription::factory()->create([
            'donorId' => $donor1->id,
            'status' => SubscriptionStatus::ACTIVE(),
            'amount' => new Money(100, 'USD'),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'mode' => new SubscriptionMode('live'),
            'gatewayId' => TestGateway::id(),   
        ]);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createSubscription2(): Subscription
    {
        /** @var Donor $donor2 */
        $donor2 = Donor::factory()->create();

        return Subscription::factory()->create([
            'donorId' => $donor2->id,
            'status' => SubscriptionStatus::ACTIVE(),
            'amount' => new Money(200, 'USD'),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'mode' => new SubscriptionMode('live'),
            'gatewayId' => TestGateway::id(),   
        ]);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createSubscription3(): Subscription
    {
        /** @var Donor $donor3 */
        $donor3 = Donor::factory()->create();

        return Subscription::factory()->create([
            'donorId' => $donor3->id,
            'status' => SubscriptionStatus::ACTIVE(),
            'amount' => new Money(300, 'USD'),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'mode' => new SubscriptionMode('live'),
            'gatewayId' => TestGateway::id(),   
        ]);
    }
}

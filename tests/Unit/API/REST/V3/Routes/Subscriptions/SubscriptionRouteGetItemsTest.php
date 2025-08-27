<?php

namespace Unit\API\REST\V3\Routes\Subscriptions;

use DateTime;
use Exception;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Campaigns\Models\Campaign;
use Give\Framework\Database\DB;
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
class SubscriptionRouteGetItemsTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    
    /**
     * @unreleased
     */
    public function testGetSubscriptionsShouldReturnAllViewModelProperties()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $subscription = $this->createSubscription();        

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
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

        // Remove additional property add by the prepare_response_for_collection() method
        unset($data[0]['_links']);

        $this->assertEquals(200, $status);

        // Verify DateTime object structure for createdAt and renewsAt
        $this->assertIsArray($data[0]['createdAt']);
        $this->assertArrayHasKey('date', $data[0]['createdAt']);
        $this->assertArrayHasKey('timezone', $data[0]['createdAt']);
        $this->assertArrayHasKey('timezone_type', $data[0]['createdAt']);

        $this->assertIsArray($data[0]['renewsAt']);
        $this->assertArrayHasKey('date', $data[0]['renewsAt']);
        $this->assertArrayHasKey('timezone', $data[0]['renewsAt']);
        $this->assertArrayHasKey('timezone_type', $data[0]['renewsAt']);

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
            'createdAt' => $data[0]['createdAt'],
            'renewsAt' => $data[0]['renewsAt'],
            'firstName' => $donor ? $donor->firstName : '',
            'lastName' => $donor ? $donor->lastName : '',
            'gateway' => array_merge(
                $subscription->gateway()->toArray(),
                [
                    'subscriptionUrl' => $subscription->gateway()->gatewayDashboardSubscriptionUrl($subscription),
                    'canSync' => $subscription->gateway()->canSyncSubscriptionWithPaymentGateway(),
                ]
            ),
            'projectedAnnualRevenue' => $subscription->projectedAnnualRevenue()->toArray(),

        ], $data[0]);
    }

    /**
     * @unreleased
     */
    public function testGetSubscriptionsShouldReturnSelfLink()
    {        
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);        

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
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
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
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
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
        $this->assertNotEmpty(array_intersect_key($data[0], array_flip($sensitiveProperties)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
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
    public function testGetSubscriptionsShouldNotIncludeAnonymousDonors()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $subscription1 = $this->createSubscription('live', 'active', 100);
        $subscription2 = $this->createSubscriptionWithAnonymousDonor('live', 'active', 200);

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
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
    public function testGetSubscriptionsShouldIncludeAnonymousDonors()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $subscription1 = $this->createSubscription('live', 'active', 100);
        $subscription2 = $this->createSubscriptionWithAnonymousDonor('live', 'active', 200);

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(
            [
                'anonymousDonors' => 'include',
                'direction' => 'ASC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(2, count($data));
        $this->assertEquals($subscription1->id, $data[0]['id']);
        $this->assertEquals($subscription2->id, $data[1]['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsShouldReturn403ErrorWhenNotAdminUserIncludeAnonymousDonors()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $this->createSubscription('live', 'active', 100);
        $this->createSubscriptionWithAnonymousDonor('live', 'active', 200);

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'subscriber');
        $request->set_query_params(
            [
                'anonymousDonors' => 'include',
                'direction' => 'ASC',
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
    public function testGetSubscriptionsShouldRedactAnonymousDonors()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $subscription1 = $this->createSubscription('live', 'active', 100);
        $subscription2 = $this->createSubscriptionWithAnonymousDonor('live', 'active', 200);        

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonors' => 'redact',
                'direction' => 'ASC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(2, count($data));
        $this->assertEquals($subscription1->id, $data[0]['id']);
        $this->assertEquals(0, $data[1]['donorId']);

        $anonymousDataRedacted = [            
            'firstName',
            'lastName',
        ];

        foreach ($anonymousDataRedacted as $property) {
            $this->assertEquals(__('anonymous', 'give'), $data[1][$property]);
        }
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsWithPagination()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));
        
        $subscription1 = $this->createSubscription();
        
        $subscription2 = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

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
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $subscription1 = $this->createSubscription();
        $subscription2 = $this->createSubscription();
        $donor1 = $subscription1->donor;
        $donor2 = $subscription2->donor;

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
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
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $subscription1 = $this->createSubscription('live', SubscriptionStatus::ACTIVE);
        $subscription2 = $this->createSubscription('live', SubscriptionStatus::CANCELLED);

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
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
        $this->assertEquals(1, count($data));
        $this->assertEquals($subscription1->id, $data[0]['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetSubscriptionsByMode()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $subscription1 = $this->createSubscription('test');
        $subscription2 = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
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
     * @throws Exception
     */
    public function testGetSubscriptionsByCampaignId()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        /** @var Campaign $campaign */
        $campaign1 = Campaign::factory()->create();

        /** @var Campaign $campaign */
        $campaign2 = Campaign::factory()->create();

        $subscription1 = $this->createSubscriptionWithCampaignId($campaign1->id);
        $subscription2 = $this->createSubscriptionWithCampaignId($campaign2->id);

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'campaignId' => $campaign1->id,
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
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        $subscription1 = $this->createSubscriptionSort1();
        $subscription2 = $this->createSubscriptionSort2();
        $subscription3 = $this->createSubscriptionSort3();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

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
        
        // For firstName and lastName, get the value from the donor
        if (in_array($sortableColumn, ['firstName', 'lastName'], true)) {
            $expected1 = $subscription1->donor()->get()->{$sortableColumn};
            $expected2 = $subscription2->donor()->get()->{$sortableColumn};
            $expected3 = $subscription3->donor()->get()->{$sortableColumn};
        } else {
            $expected1 = $subscription1->{$sortableColumn};
            $expected2 = $subscription2->{$sortableColumn};
            $expected3 = $subscription3->{$sortableColumn};
        }
        
        $this->assertEquals($expected1, $data[0][$sortableColumn]);
        $this->assertEquals($expected2, $data[1][$sortableColumn]);
        $this->assertEquals($expected3, $data[2][$sortableColumn]);

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
        
        // For firstName and lastName, get the value from the donor
        if (in_array($sortableColumn, ['firstName', 'lastName'], true)) {
            $expected3 = $subscription3->donor()->get()->{$sortableColumn};
            $expected2 = $subscription2->donor()->get()->{$sortableColumn};
            $expected1 = $subscription1->donor()->get()->{$sortableColumn};
        } else {
            $expected3 = $subscription3->{$sortableColumn};
            $expected2 = $subscription2->{$sortableColumn};
            $expected1 = $subscription1->{$sortableColumn};
        }
        
        $this->assertEquals($expected3, $data[0][$sortableColumn]);
        $this->assertEquals($expected2, $data[1][$sortableColumn]);
        $this->assertEquals($expected1, $data[2][$sortableColumn]);
    }

    /**
     * @unreleased
     */
    public function sortableColumnsDataProvider(): array
    {
        return [
            ['id'],
            ['createdAt'],
            ['renewsAt'],
            ['status'],
            ['amount'],
            ['feeAmountRecovered'],
            ['donorId'],
            ['firstName'],
            ['lastName'],
        ];
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
    private function createSubscriptionSort1()
    {
        $subscription1 = $this->createSubscription('live', 'active');
        $subscription1->amount = new Money(100, 'USD');
        $subscription1->feeAmountRecovered = new Money(10, 'USD');
        $subscription1->renewsAt = new DateTime('2025-01-01');
        
        // Set donor names directly
        $donor = $subscription1->donor()->get();
        $donor->firstName = 'A';
        $donor->lastName = 'A';
        $donor->save();
        
        $subscription1->save();                

        return $subscription1;
    }

    /**
     * @unreleased
     * 
     * @throws Exception 
     */
    private function createSubscriptionSort2()
    {        
        $subscription2 = $this->createSubscription('live', 'active');
        $subscription2->amount = new Money(200, 'USD');
        $subscription2->feeAmountRecovered = new Money(20, 'USD');
        $subscription2->renewsAt = new DateTime('2025-01-02');
        
        // Set donor names directly
        $donor = $subscription2->donor()->get();
        $donor->firstName = 'B';
        $donor->lastName = 'B';
        $donor->save();
        
        $subscription2->save();                

        return $subscription2;
    }

    /**
     * @unreleased     
     * 
     * @throws Exception 
     */
    private function createSubscriptionSort3()
    {        
        $subscription3 = $this->createSubscription('live', 'active');
        $subscription3->amount = new Money(300, 'USD');
        $subscription3->feeAmountRecovered = new Money(30, 'USD');
        $subscription3->renewsAt = new DateTime('2025-01-03');
        
        // Set donor names directly
        $donor = $subscription3->donor()->get();
        $donor->firstName = 'C';
        $donor->lastName = 'C';
        $donor->save();
        
        $subscription3->save();                

        return $subscription3;
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

    /**
     * @unreleased
     *
     * @throws Exception
     */
    private function createSubscriptionWithCampaignId(int $campaignId): Subscription
    {
        $donor = Donor::factory()->create();

        return Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(100, 'USD'),
            'status' => new SubscriptionStatus('active'),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'mode' => new SubscriptionMode('live'),
            'donorId' => $donor->id,
        ], [
            'campaignId' => $campaignId,
            'anonymous' => false,
            'donorId' => $donor->id,
        ]);
    }
}

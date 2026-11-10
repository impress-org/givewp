<?php

namespace Give\Tests\Unit\Subscriptions\Endpoints;

use DateTime;
use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Framework\Database\DB;
use Give\Subscriptions\Endpoints\ListSubscriptions;
use Give\Subscriptions\ListTable\SubscriptionsListTable;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class TestListSubscriptions extends TestCase
{
    use RefreshDatabase;

    /**
     * Start each test without subscriptions or donations left by earlier tests.
     *
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        Donation::query()->delete();
        DB::query('DELETE FROM ' . DB::prefix('give_subscriptions'));
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameSize()
    {
        $subscriptions = Subscription::factory()->count(5)->create();

        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('status', [SubscriptionStatus::PENDING]);

        $listSubscriptions = new ListSubscriptions();

        $response = $listSubscriptions->handleRequest($mockRequest);

        $this->assertSameSize($subscriptions, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameData()
    {
        $subscriptions = Subscription::factory()->count(5)->create();
        $sortDirection = ['asc', 'desc'][round(rand(0, 1))];
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('sortColumn', 'id');
        $mockRequest->set_param('sortDirection', $sortDirection);
        $mockRequest->set_param('status', [SubscriptionStatus::PENDING]);

        $expectedItems = $this->getMockColumns($subscriptions,$sortDirection);

        $listSubscriptions = new ListSubscriptions();

        $response = $listSubscriptions->handleRequest($mockRequest);

        foreach ( $response->data['items'] as $row => $item ) {
            foreach ( $item as $key => &$value ) {
                if ( 'subscriptionInformation' === $key ) {
                    $value = preg_replace('/(https?:\/\/)(\d\.)(gravatar.com)/', "$1$3", $value);
                    $expectedItems[$row][$key] = preg_replace('/(https?:\/\/)(\d\.)(gravatar.com)/', "$1$3", $expectedItems[$row][$key]);
                }

                $this->assertEquals($expectedItems[$row][$key], $value);
            }
        }
    }

    /**
     * @since TBD
     */
    public function testFiltersByTwoCampaigns()
    {
        $campaignA = Campaign::factory()->create();
        $campaignB = Campaign::factory()->create();
        $campaignC = Campaign::factory()->create();
        $a = $this->createSubscriptionInCampaign($campaignA->id);
        $b = $this->createSubscriptionInCampaign($campaignB->id);
        $this->createSubscriptionInCampaign($campaignC->id);

        $response = $this->listSubscriptions(['campaignId' => [$campaignA->id, $campaignB->id]]);

        $this->assertSameIds([$a->id, $b->id], $response);
        $this->assertSame(2, $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testSingleCampaignIdKeepsItsResult()
    {
        $campaignA = Campaign::factory()->create();
        $campaignB = Campaign::factory()->create();
        $a = $this->createSubscriptionInCampaign($campaignA->id);
        $this->createSubscriptionInCampaign($campaignB->id);

        $response = $this->listSubscriptions(['campaignId' => $campaignA->id]);

        $this->assertSameIds([$a->id], $response);
        $this->assertSame(1, $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testFiltersByCreatedDateRange()
    {
        $inside = Subscription::factory()->create(['createdAt' => new DateTime('2026-09-15 23:30:00', wp_timezone())]);
        Subscription::factory()->create(['createdAt' => new DateTime('2026-09-16 00:00:00', wp_timezone())]);
        Subscription::factory()->create(['createdAt' => new DateTime('2026-08-31 23:59:59', wp_timezone())]);

        $response = $this->listSubscriptions([
            'start' => '2026-09-01T00:00:00',
            'end' => '2026-09-15T23:59:59',
        ]);

        $this->assertSameIds([$inside->id], $response);
        $this->assertSame(1, $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testFiftyCampaignIdsAreAccepted()
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));

        $request = new WP_REST_Request(WP_REST_Server::READABLE, '/give-api/v2/admin/subscriptions');
        $request->set_query_params(['campaignId' => implode(',', range(1001, 1050))]);

        $this->assertSame(200, rest_do_request($request)->get_status());
    }

    /**
     * @since TBD
     *
     * @dataProvider invalidParamsProvider
     */
    public function testRejectsInvalidCampaignIds(array $params)
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));

        $request = new WP_REST_Request(WP_REST_Server::READABLE, '/give-api/v2/admin/subscriptions');
        $request->set_query_params($params);
        $response = rest_do_request($request);

        $this->assertSame(400, $response->get_status());
        $this->assertSame('rest_invalid_param', $response->get_data()['code']);
    }

    /**
     * @since TBD
     */
    public function invalidParamsProvider(): array
    {
        return [
            'non-numeric campaign' => [['campaignId' => 'abc']],
            'non-numeric campaign in a list' => [['campaignId' => '1,abc']],
            'fifty-one campaigns' => [['campaignId' => implode(',', range(1001, 1051))]],
        ];
    }

    /**
     * @since TBD
     */
    private function createSubscriptionInCampaign(int $campaignId): Subscription
    {
        return Subscription::factory()->createWithDonation(
            [],
            ['campaignId' => $campaignId, 'mode' => DonationMode::TEST()]
        );
    }

    /**
     * @since TBD
     */
    private function listSubscriptions(array $params): WP_REST_Response
    {
        $request = $this->getMockRequest();
        $request->set_param('page', 1);
        $request->set_param('perPage', 30);
        $request->set_param('locale', 'en-US');
        $request->set_param('testMode', true);

        foreach ($params as $key => $value) {
            $request->set_param($key, $value);
        }

        return (new ListSubscriptions())->handleRequest($request);
    }

    /**
     * @since TBD
     */
    private function assertSameIds(array $expected, WP_REST_Response $response)
    {
        $this->assertEqualsCanonicalizing(
            $expected,
            array_map('intval', array_column($response->data['items'], 'id'))
        );
    }

    /**
     *
     * @since 2.25.0
     */
    public function getMockRequest(): WP_REST_Request
    {
        return new WP_REST_Request(
            WP_REST_Server::READABLE,
            '/wp/v2/admin/subscriptions'
        );
    }

    /**
     * @since TBD Include the raw values of each column.
     *
     * @param string $sortDirection
     *
     * @return array
     */
    public function getMockColumns(array $subscriptions, string $sortDirection = 'desc'): array
    {
        $listTable = new SubscriptionsListTable();
        $columns = $listTable->getColumns();

        $expectedItems = [];
        foreach ( $subscriptions as $subscription ) {
            $expectedItem = [];
            $expectedRawValues = [];
            foreach ( $columns as $column ) {
                $expectedItem[$column::getId()] = $column->getCellValue($subscription, 'en-US');
                $rawValue = $column->getRawValue($subscription);

                if ($rawValue !== null) {
                    $expectedRawValues[$column::getId()] = $rawValue;
                }
            }

            if ($expectedRawValues) {
                $expectedItem['rawValues'] = $expectedRawValues;
            }
            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}


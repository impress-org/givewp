<?php

namespace Give\Tests\Unit\Donors\Endpoints;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donors\Endpoints\ListDonors;
use Give\Donors\ListTable\DonorsListTable;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class TestListDonors extends TestCase
{
    use RefreshDatabase;

    /**
     * Start each test without donors, donations or subscriptions left by earlier tests.
     *
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        Donation::query()->delete();
        DB::query('DELETE FROM ' . DB::prefix('give_subscriptions'));
        DB::query('DELETE FROM ' . DB::prefix('give_donors'));
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameSize()
    {
        $donors = Donor::factory()->count(5)->create();

        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');

        $listDonors = new ListDonors();

        $response = $listDonors->handleRequest($mockRequest);

        $this->assertSameSize($donors, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameData()
    {
        $donors = Donor::factory()->count(5)->create();
        $sortDirection = ['asc', 'desc'][round(rand(0, 1))];
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('sortColumn', 'id');
        $mockRequest->set_param('sortDirection', $sortDirection);

        $expectedItems = $this->getMockColumns($donors,$sortDirection);

        $listDonors = new ListDonors();

        $response = $listDonors->handleRequest($mockRequest);

        foreach ( $response->data['items'] as $row => $item ) {
            foreach ( $item as $key => &$value ) {
                if ( 'donorInformation' === $key ) {
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
        $donorA = $this->createDonorWithDonation($campaignA->id);
        $donorB = $this->createDonorWithDonation($campaignB->id);
        $this->createDonorWithDonation($campaignC->id);

        $response = $this->listDonors(['campaignId' => [$campaignA->id, $campaignB->id]]);

        $this->assertSameIds([$donorA->id, $donorB->id], $response);
        $this->assertSame(2, $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testSingleCampaignKeepsItsResult()
    {
        $campaignA = Campaign::factory()->create();
        $campaignB = Campaign::factory()->create();
        $donorA = $this->createDonorWithDonation($campaignA->id);
        $this->createDonorWithDonation($campaignB->id);

        $response = $this->listDonors(['campaignId' => $campaignA->id]);

        $this->assertSameIds([$donorA->id], $response);
        $this->assertSame(1, $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testCampaignIdZeroMeansAll()
    {
        $campaign = Campaign::factory()->create();
        $this->createDonorWithDonation($campaign->id);
        Donor::factory()->create();
        // The factories create extra donors of their own, so compare with what is stored.
        $allIds = array_map(static function (Donor $donor) {
            return $donor->id;
        }, Donor::query()->getAll());

        $response = $this->listDonors(['campaignId' => [0]]);

        $this->assertSameIds($allIds, $response);
        $this->assertSame(count($allIds), $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testFiltersSubscribers()
    {
        $subscriber = Donor::factory()->create();
        Subscription::factory()->create(['donorId' => $subscriber->id]);
        Donor::factory()->create();

        $response = $this->listDonors(['donorType' => 'subscriber']);

        $this->assertSameIds([$subscriber->id], $response);
        $this->assertSame(1, $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testFiltersOneTimeDonors()
    {
        $subscriber = Donor::factory()->create();
        Subscription::factory()->create(['donorId' => $subscriber->id]);
        Donor::factory()->create();
        // The subscription factory creates an extra donor of its own, so compare with what is stored.
        $expected = array_values(array_filter(array_map(static function (Donor $donor) {
            return $donor->id;
        }, Donor::query()->getAll()), static function (int $id) use ($subscriber) {
            return $id !== $subscriber->id;
        }));

        $response = $this->listDonors(['donorType' => 'one-time']);

        $this->assertGreaterThan(1, count($expected));
        $this->assertSameIds($expected, $response);
        $this->assertSame(count($expected), $response->data['totalItems']);
    }

    /**
     * The name and email LIKEs used to be ungrouped, so an email match skipped the campaign filter.
     *
     * @since TBD
     */
    public function testSearchByEmailRespectsCampaignFilter()
    {
        $campaign = Campaign::factory()->create();
        $inside = $this->createDonorWithDonation($campaign->id, ['email' => 'match-inside@example.com']);
        Donor::factory()->create(['email' => 'match-outside@example.com']);

        $response = $this->listDonors(['search' => 'match-', 'campaignId' => [$campaign->id]]);

        $this->assertSameIds([$inside->id], $response);
        $this->assertSame(1, $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testSearchWithoutFiltersKeepsItsResult()
    {
        $byName = Donor::factory()->create(['name' => 'Zelda Quux', 'email' => 'a1@example.com']);
        $byEmail = Donor::factory()->create(['name' => 'Bob Smith', 'email' => 'zelda-fan@example.com']);
        Donor::factory()->create(['name' => 'Carl Jones', 'email' => 'c1@example.com']);

        $response = $this->listDonors(['search' => 'zelda']);

        $this->assertSameIds([$byName->id, $byEmail->id], $response);
        $this->assertSame(2, $response->data['totalItems']);
    }

    /**
     * @since TBD
     */
    public function testFiftyCampaignIdsAreAccepted()
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));

        $request = new WP_REST_Request(WP_REST_Server::READABLE, '/give-api/v2/admin/donors');
        $request->set_query_params(['campaignId' => implode(',', range(1001, 1050))]);

        $this->assertSame(200, rest_do_request($request)->get_status());
    }

    /**
     * @since TBD
     *
     * @dataProvider invalidParamsProvider
     */
    public function testRejectsInvalidValues(array $params)
    {
        wp_set_current_user($this->factory()->user->create(['role' => 'administrator']));

        $request = new WP_REST_Request(WP_REST_Server::READABLE, '/give-api/v2/admin/donors');
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
            'unknown donor type' => [['donorType' => 'x']],
        ];
    }

    /**
     * @since TBD
     */
    private function createDonorWithDonation(int $campaignId, array $donorAttributes = []): Donor
    {
        $donor = Donor::factory()->create($donorAttributes);
        Donation::factory()->create(['donorId' => $donor->id, 'campaignId' => $campaignId]);

        return $donor;
    }

    /**
     * @since TBD
     */
    private function listDonors(array $params): WP_REST_Response
    {
        $request = $this->getMockRequest();
        $request->set_param('page', 1);
        $request->set_param('perPage', 30);
        $request->set_param('locale', 'en-US');

        foreach ($params as $key => $value) {
            $request->set_param($key, $value);
        }

        return (new ListDonors())->handleRequest($request);
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
            '/wp/v2/admin/donors'
        );
    }

    /**
     * @param string $sortDirection
     *
     * @return array
     */
    public function getMockColumns(array $donors, string $sortDirection = 'desc'): array
    {
        $listTable = new DonorsListTable();
        $columns = $listTable->getColumns();

        $expectedItems = [];
        foreach ( $donors as $donor ) {
            $expectedItem = [];
            foreach ( $columns as $column ) {
                $expectedItem[$column::getId()] = $column->getCellValue($donor, 'en-US');
            }
            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}


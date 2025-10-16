<?php

namespace Give\Tests\Unit\Donations\Endpoints;

use DateTime;
use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Endpoints\ListDonations;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class TestListDonations extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.10.0
     */
    public function setUp(): void
    {
        parent::setUp();

        Donation::query()->delete();

        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameSize()
    {
        $campaign = Campaign::factory()->create();
        $donations = Donation::factory()->count(5)->create([
            'campaignId' => $campaign->id
        ]);

        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('status', [DonationStatus::PENDING]);

        $listDonations = give(ListDonations::class);

        $response = $listDonations->handleRequest($mockRequest);

        $this->assertSameSize($donations, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameData()
    {
        $campaign = Campaign::factory()->create();
        $donations = Donation::factory()->count(5)->create([
            'campaignId' => $campaign->id
        ]);
        $sortDirection = ['asc', 'desc'][round(rand(0, 1))];
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('sortColumn', 'id');
        $mockRequest->set_param('sortDirection', $sortDirection);
        $mockRequest->set_param('testMode', true);

        $expectedItems = $this->getMockColumns($donations, $sortDirection);

        $listDonations = give(ListDonations::class);

        $response = $listDonations->handleRequest($mockRequest);

        $this->assertSame($expectedItems, $response->data['items']);
    }

    /**
     * @since 4.0.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldFilterInvalidDateArgument_missingMonthDay()
    {
        $listDonation = give(ListDonations::class);
        $key = 'start';
        $value = '2020';

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param($key, $value);

        $response = $listDonation->validateDate($value, $mockRequest, $key);
        $this->assertFalse($response);
    }

    /**
     * @since 4.0.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldFilterInvalidDateArgument_InvalidChar()
    {
        $listDonation = give(ListDonations::class);
        $key = 'start';
        $value = '2020-mar-02';

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param($key, $value);

        $response = $listDonation->validateDate($value, $mockRequest, $key);
        $this->assertFalse($response);
    }


    /**
     * @since 4.10.0
     */
    public function testShouldFilterDonationsByDateRange()
    {
        $campaign = Campaign::factory()->create();

        // Create donations with specific dates
        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-01-15 10:00:00')
        ]);
        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-02-15 10:00:00')
        ]);
        $donation3 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-03-15 10:00:00')
        ]);

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('status', [DonationStatus::PENDING]);
        $mockRequest->set_param('start', '2023-02-01');
        $mockRequest->set_param('end', '2023-02-28');

        $listDonations = give(ListDonations::class);
        $response = $listDonations->handleRequest($mockRequest);

        // Should only return donation2 (created in February 2023)
        $this->assertEquals(1, $response->data['totalItems']);
        $this->assertCount(1, $response->data['items']);
    }

    /**
     * @since 4.10.0
     */
    public function testShouldFilterDonationsByStartDateOnly()
    {
        $campaign = Campaign::factory()->create();

        // Create donations with specific dates
        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-01-15 10:00:00')
        ]);
        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-02-15 10:00:00')
        ]);
        $donation3 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-03-15 10:00:00')
        ]);

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('status', [DonationStatus::PENDING]);
        $mockRequest->set_param('start', '2023-02-01');

        $listDonations = give(ListDonations::class);
        $response = $listDonations->handleRequest($mockRequest);

        // Should return donation2 and donation3 (created on or after February 1, 2023)
        $this->assertEquals(2, $response->data['totalItems']);
        $this->assertCount(2, $response->data['items']);
    }

    /**
     * @since 4.10.0
     */
    public function testShouldFilterDonationsByEndDateOnly()
    {
        $campaign = Campaign::factory()->create();

        // Create donations with specific dates
        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-01-15 10:00:00')
        ]);
        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-02-15 10:00:00')
        ]);
        $donation3 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-03-15 10:00:00')
        ]);

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('status', [DonationStatus::PENDING]);
        $mockRequest->set_param('end', '2023-02-28');

        $listDonations = give(ListDonations::class);
        $response = $listDonations->handleRequest($mockRequest);

        // Should return donation1 and donation2 (created on or before February 28, 2023)
        $this->assertEquals(2, $response->data['totalItems']);
        $this->assertCount(2, $response->data['items']);
    }

    /**
     * @since 4.10.0
     */
    public function testShouldFilterDonationsByExactDateRange()
    {
        $campaign = Campaign::factory()->create();

        // Create donations with specific dates
        $donation1 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-02-01 09:00:00')
        ]);
        $donation2 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-02-15 10:00:00')
        ]);
        $donation3 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-02-28 00:00:00')
        ]);
        $donation4 = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-03-01 00:00:00')
        ]);

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('status', [DonationStatus::PENDING]);
        $mockRequest->set_param('start', '2023-02-01');
        $mockRequest->set_param('end', '2023-02-28');

        $listDonations = give(ListDonations::class);
        $response = $listDonations->handleRequest($mockRequest);

        // Should return all 3 donations
        $this->assertEquals(3, $response->data['totalItems']);
        $this->assertCount(3, $response->data['items']);
    }

    /**
     * @since 4.10.0
     */
    public function testShouldReturnEmptyResultsForDateRangeWithNoDonations()
    {
        $campaign = Campaign::factory()->create();

        // Create donations outside the date range
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-01-15 10:00:00')
        ]);
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-03-15 10:00:00')
        ]);

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('status', [DonationStatus::PENDING]);
        $mockRequest->set_param('start', '2023-02-01');
        $mockRequest->set_param('end', '2023-02-28');

        $listDonations = give(ListDonations::class);
        $response = $listDonations->handleRequest($mockRequest);

        // Should return no donations
        $this->assertEquals(0, $response->data['totalItems']);
        $this->assertCount(0, $response->data['items']);
    }

    /**
     * @since 4.10.0
     */
    public function testShouldFilterDonationsByDateWithMixedDonationTypes()
    {
        $campaign = Campaign::factory()->create();

        // Create one-time donations
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-02-15 10:00:00')
        ]);
        Donation::factory()->create([
            'campaignId' => $campaign->id,
            'createdAt' => new DateTime('2023-03-15 10:00:00')
        ]);

        // Create subscription donations
        $this->createSubscription($campaign->id, new DateTime('2023-02-20 10:00:00'));
        $this->createSubscription($campaign->id, new DateTime('2023-03-20 10:00:00'));

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'en-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('status', [DonationStatus::COMPLETE, DonationStatus::PENDING]);
        $mockRequest->set_param('start', '2023-02-01');
        $mockRequest->set_param('end', '2023-02-28');

        $listDonations = give(ListDonations::class);
        $response = $listDonations->handleRequest($mockRequest);

        // Should return 2 donations (1 one-time + 1 subscription) created in February
        $this->assertEquals(2, $response->data['totalItems']);
        $this->assertCount(2, $response->data['items']);
    }


    /**
     * @since 4.10.0
     */
    private function createSubscription(int $campaignId, DateTime $donationDate = null): Subscription
    {
        $donationData = [
            'campaignId' => $campaignId
        ];

        if ($donationDate !== null) {
            $donationData['createdAt'] = $donationDate;
        }

        return Subscription::factory()->createWithDonation([], $donationData);
    }

    /**
     *
     * @since 2.25.0
     */
    public function getMockRequest(): WP_REST_Request
    {
        return new WP_REST_Request(
            WP_REST_Server::READABLE,
            '/wp/v2/admin/donations'
        );
    }

    /**
     * @param string $sortDirection
     *
     * @return array
     */
    public function getMockColumns(array $donations, string $sortDirection = 'desc'): array
    {
        $listTable = new DonationsListTable();
        $columns = $listTable->getColumns();

        $expectedItems = [];
        foreach ($donations as $donation) {
            $expectedItem = [];
            foreach ($columns as $column) {
                $expectedItem[$column::getId()] = $column->getCellValue($donation, 'en-US');
            }
            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}

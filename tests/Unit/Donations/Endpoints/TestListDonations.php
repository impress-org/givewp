<?php

namespace Give\Tests\Unit\Donations\Endpoints;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Endpoints\ListDonations;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class TestListDonations extends TestCase
{
    use RefreshDatabase;

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
        $mockRequest->set_param('testMode', give_is_test_mode());
        $mockRequest->set_param('status', 'active');

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
        $mockRequest->set_param('testMode', give_is_test_mode());

        $expectedItems = $this->getMockColumns($donations,$sortDirection);

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
        foreach ( $donations as $donation ) {
            $expectedItem = [];
            foreach ( $columns as $column ) {
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


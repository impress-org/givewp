<?php

namespace Give\Tests\Unit\Donations\Endpoints;

use Exception;
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
        $donations = Donation::factory()->count(5)->create();

        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');
        $mockRequest->set_param('testMode', give_is_test_mode());

        $listDonations = give(ListDonations::class);
        $expectedItems = $this->getMockColumns($donations);

        $response = $listDonations->handleRequest($mockRequest);

        $this->assertSame($expectedItems, $response->data['items']);
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnListWithSameData()
    {
        $donations = Donation::factory()->count(5)->create();
        $sortDirection = ['asc', 'desc'][round(rand(0, 1))];
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');
        $mockRequest->set_param('sortColumn', 'id');
        $mockRequest->set_param('sortDirection', $sortDirection);
        $mockRequest->set_param('testMode', give_is_test_mode());

        $expectedItems = $this->getMockColumns($donations, $sortDirection);

        $listDonations = give(ListDonations::class);

        $response = $listDonations->handleRequest($mockRequest);

        $this->assertSame($expectedItems, $response->data['items']);
    }

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnFilteredListByDonorId()
    {
        $donations = Donation::factory()->count(5)->create();
        $donorId = $donations[0]->donorId;

        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');
        $mockRequest->set_param('donor', (string)$donorId);
        $mockRequest->set_param('testMode', give_is_test_mode());

        $expectedItems = $this->getMockColumns(
            array_filter($donations, function ($donation) use ($donorId) {
                return $donation->donorId === $donorId;
            })
        );

        $listDonations = give(ListDonations::class);

        $response = $listDonations->handleRequest($mockRequest);

        $this->assertSame($expectedItems, $response->data['items']);
    }

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShouldAllowAddingFilters()
    {
        $donations = Donation::factory()->count(5)->create();

        $expectedItems = array_slice($donations, 0, 2);
        foreach ($expectedItems as $item) {
            give_update_payment_meta($item->id, 'my_key', 'on');
        }
        $expectedItems = $this->getMockColumns($expectedItems);

        add_filter('give_list-donation_api_args', function ($args) {
            $args['my_param'] = [
                'type' => 'string',
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field',
            ];
            return $args;
        });

        add_filter('give_list-donation_where_conditions', function ($value, $endpoint) {
            list($query, $dependencies) = $value;

            $paramValue = $endpoint->request->get_param('my_param');
            if (!empty($paramValue)) {
                $query->attachMeta(
                    'give_donationmeta',
                    'ID',
                    'donation_id',
                    ['my_key', 'myKey']
                );
                $query->where('give_donationmeta_attach_meta_myKey.meta_value', $paramValue);
            }

            return [$query, $dependencies];
        }, 10, 2);

        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');
        $mockRequest->set_param('testMode', give_is_test_mode());
        $mockRequest->set_param('my_param', 'on');

        $listDonations = give(ListDonations::class);

        $response = $listDonations->handleRequest($mockRequest);

        $this->assertSame($expectedItems, $response->data['items']);
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
                $expectedItem[$column::getId()] = $column->getCellValue($donation);
            }
            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}


<?php

namespace Give\Tests\Unit\Donors\Endpoints;

use Exception;
use Give\Donors\Endpoints\ListDonors;
use Give\Donors\ListTable\DonorsListTable;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class TestListDonors extends TestCase
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
        $donors = Donor::factory()->count(5)->create();

        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');

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
        $mockRequest->set_param('locale', 'us-US');
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
                $expectedItem[$column::getId()] = $column->getCellValue($donor);
            }
            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}


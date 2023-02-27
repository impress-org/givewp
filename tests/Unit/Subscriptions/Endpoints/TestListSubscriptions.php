<?php

namespace Give\Tests\Unit\Subscriptions\Endpoints;

use Exception;
use Give\Subscriptions\Endpoints\ListSubscriptions;
use Give\Subscriptions\ListTable\SubscriptionsListTable;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class TestListSubscriptions extends TestCase
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
        $subscriptions = Subscription::factory()->count(5)->create();

        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');
        $mockRequest->set_param('testMode', true);

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
        $mockRequest->set_param('locale', 'us-US');
        $mockRequest->set_param('testMode', true);
        $mockRequest->set_param('sortColumn', 'id');
        $mockRequest->set_param('sortDirection', $sortDirection);

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
            foreach ( $columns as $column ) {
                $expectedItem[$column::getId()] = $column->getCellValue($subscription);
            }
            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}


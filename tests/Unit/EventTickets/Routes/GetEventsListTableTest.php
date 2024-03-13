<?php

namespace Unit\EventTickets\Routes;

use Exception;
use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Routes\GetEventsListTable;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class GetEventsListTableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function testShouldReturnListWithSameSize(): void
    {
        $events = Event::factory()->count(5)->create();

        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');

        $listEvents = give(GetEventsListTable::class);

        $response = $listEvents->handleRequest($mockRequest);

        $this->assertSameSize($events, $response->data['items']);
    }

    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function testShouldReturnListWithSameData(): void
    {
        $events = Event::factory()->count(5)->create();
        $sortDirection = ['asc', 'desc'][round(rand(0, 1))];
        $mockRequest = $this->getMockRequest();
        // set_params
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', 30);
        $mockRequest->set_param('locale', 'us-US');
        $mockRequest->set_param('sortColumn', 'id');
        $mockRequest->set_param('sortDirection', $sortDirection);

        $expectedItems = $this->getMockColumns($events, $sortDirection);

        $listEvents = give(GetEventsListTable::class);

        $response = $listEvents->handleRequest($mockRequest);

        $this->assertSame($expectedItems, $response->data['items']);
    }

    /**
     * @since 3.6.0
     */
    public function getMockRequest(): WP_REST_Request
    {
        return new WP_REST_Request(
            WP_REST_Server::READABLE,
            '/wp/v2/admin/event-tickets'
        );
    }

    /**
     * @since 3.6.0
     */
    public function getMockColumns(array $events, string $sortDirection = 'desc'): array
    {
        $listTable = new EventTicketsListTable();
        $columns = $listTable->getColumns();

        $expectedItems = [];
        foreach ($events as $event) {
            $expectedItem = [];
            foreach ($columns as $column) {
                $expectedItem[$column::getId()] = $column->getCellValue($event);
            }
            $expectedItems[] = $expectedItem;
        }

        if ($sortDirection === 'desc') {
            $expectedItems = array_reverse($expectedItems);
        }

        return $expectedItems;
    }
}


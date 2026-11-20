<?php

namespace Unit\EventTickets\Routes;

use Exception;
use Give\Donations\Models\Donation;
use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Support\ValueObjects\Money;
use Give\EventTickets\Routes\GetEventsListTable;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class GetEventsListTableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        give_update_option('enable_event_tickets', 'enabled');
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        give_delete_option('enable_event_tickets');

        parent::tearDown();
    }

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
        $mockRequest->set_param('locale', 'en-US');

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
        $mockRequest->set_param('locale', 'en-US');
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
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldReturnTheSameSalesAsTheSingleEventQueries(): void
    {
        $events = [$this->createEventWithSales(), $this->createEventWithSales()];

        $response = give(GetEventsListTable::class)->handleRequest($this->getPageRequest());

        $this->assertSame($this->getMockColumns($events, 'asc'), $response->data['items']);
    }

    /**
     * More events on the page must not add queries, because the sales are loaded once for the page.
     *
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldLoadTheSalesOnceForThePage(): void
    {
        global $wpdb;

        $this->createEventWithSales();
        $this->createEventWithSales();

        $before = $wpdb->num_queries;
        $twoEvents = give(GetEventsListTable::class)->handleRequest($this->getPageRequest());
        $queriesForTwoEvents = $wpdb->num_queries - $before;

        $this->createEventWithSales();
        $this->createEventWithSales();

        $before = $wpdb->num_queries;
        $fourEvents = give(GetEventsListTable::class)->handleRequest($this->getPageRequest());
        $queriesForFourEvents = $wpdb->num_queries - $before;

        $this->assertCount(2, $twoEvents->data['items']);
        $this->assertCount(4, $fourEvents->data['items']);
        $this->assertSame($queriesForTwoEvents, $queriesForFourEvents);
    }

    /**
     * @since TBD
     */
    private function getPageRequest(): WP_REST_Request
    {
        $request = $this->getMockRequest();
        $request->set_param('page', 1);
        $request->set_param('perPage', 30);
        $request->set_param('locale', 'en-US');
        $request->set_param('sortColumn', 'id');
        $request->set_param('sortDirection', 'asc');

        return $request;
    }

    /**
     * An event with one ticket type of 5 seats and 2 tickets sold at $15.00.
     *
     * @since TBD
     */
    private function createEventWithSales(): Event
    {
        $event = Event::factory()->create();
        // The ticket factories create events of their own, so the models are created directly.
        $ticketType = EventTicketType::create([
            'eventId' => $event->id,
            'title' => 'General admission',
            'description' => '',
            'price' => new Money(1500, 'USD'),
            'capacity' => 5,
        ]);

        for ($i = 0; $i < 2; $i++) {
            EventTicket::create([
                'eventId' => $event->id,
                'ticketTypeId' => $ticketType->id,
                'donationId' => Donation::factory()->create()->id,
                'amount' => new Money(1500, 'USD'),
            ]);
        }

        return $event;
    }

    /**
     * @since TBD Add the raw column values to the expected items.
     * @since 3.6.0
     */
    public function getMockColumns(array $events, string $sortDirection = 'desc'): array
    {
        $listTable = new EventTicketsListTable();
        $columns = $listTable->getColumns();

        $expectedItems = [];
        foreach ($events as $event) {
            $expectedItem = [];
            $expectedRawValues = [];
            foreach ($columns as $column) {
                $expectedItem[$column::getId()] = $column->getCellValue($event, 'en-US');
                $rawValue = $column->getRawValue($event);

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


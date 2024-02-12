<?php

namespace Give\Tests\Unit\EventTickets\Routes;

use Exception;
use Give\Donations\Endpoints\ListDonations;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\Models\Donation;
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
     * @unreleased
     */
    protected function getMockRequest(): WP_REST_Request
    {
        return new WP_REST_Request(
            WP_REST_Server::READABLE,
            '/give-api/v2/event-tickets/events/list-table'
        );
    }

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnAllWhenLessThanPerPage()
    {
        $perPage = 5;
        $events = Event::factory()->count($perPage - 1)->create();

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', $perPage);

        $response = (new GetEventsListTable)->handleRequest($mockRequest);

        $this->assertSameSize($events, $response->data['items']);
    }

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testShouldReturnLimitedByPerPage()
    {
        $perPage = 5;
        $events = Event::factory()->count($perPage + 1)->create();

        $mockRequest = $this->getMockRequest();
        $mockRequest->set_param('page', 1);
        $mockRequest->set_param('perPage', $perPage);

        $response = (new GetEventsListTable)->handleRequest($mockRequest);

        $this->assertEquals($perPage, count($response->data['items']));
    }
}

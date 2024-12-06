<?php

namespace Unit\EventTickets\Routes;

use Exception;
use Give\EventTickets\Models\Event;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Response;

class CreateEventTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that a valid request successfully creates a new Event.
     *
     * @unreleased
     * @throws Exception
     */
    public function testEventCreationSuccess()
    {
        $data = [
            'title' => 'New Title',
            'description' => 'New Description',
            'startDateTime' => '2024-01-01 00:00:00',
            'endDateTime' => '2024-01-01 23:59:59',
        ];

        $response = $this->handleRequest($data);
        $event = Event::find($response->get_data()['id']);

        $this->assertEquals(201, $response->get_status());
        $this->assertEquals($data['title'], $event->title);
        $this->assertEquals($data['description'], $event->description);
        $this->assertEquals(Temporal::toDateTime($data['startDateTime']), $event->startDateTime);
        $this->assertEquals(Temporal::toDateTime($data['endDateTime']), $event->endDateTime);
    }

    /**
     * Test that creating an event with missing required fields returns an error.
     *
     * @unreleased
     * @throws Exception
     */
    public function testEventCreationWithMissingRequiredFields()
    {
        $response = $this->handleRequest([]);

        $this->assertErrorResponse('rest_missing_callback_param', $response);

        $errorData = $response->as_error()->get_error_data();
        if (isset($errorData['params'])) {
            $this->assertContains('title', $errorData['params']);
            $this->assertContains('startDateTime', $errorData['params']);
        }
    }

    /**
     * Test that creating an event with an invalid date format returns an error.
     *
     * @unreleased
     * @throws Exception
     */
    public function testEventCreationWithInvalidDateFormat()
    {
        $data = [
            'title' => 'New Title',
            'startDateTime' => '2024/01/01',
        ];

        $response = $this->handleRequest($data);

        $this->assertErrorResponse('rest_invalid_param', $response);

        $errorData = $response->as_error()->get_error_data();
        if (isset($errorData['params'])) {
            $this->assertArrayHasKey('startDateTime', $errorData['params']);
        }
    }

    /**
     * Test that unauthorized requests are denied.
     *
     * @unreleased
     * @throws Exception
     */
    public function testEventCreationRequiresAuthorization()
    {
        $data = [
            'title' => 'New Title',
            'startDateTime' => '2024-01-01 00:00:00',
        ];

        $response = $this->handleRequest($data, false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * Handle the request common to all tests.
     *
     * @unreleased
     *
     * @param array $data
     * @param bool  $authenticatedAsAdmin
     *
     * @return WP_REST_Response
     */
    private function handleRequest(
        array $data,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'POST',
            "/give-api/v2/events-tickets/events",
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        $request->set_body_params($data);

        return $this->dispatchRequest($request);
    }
}

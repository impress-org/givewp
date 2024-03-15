<?php

namespace Give\Tests\Unit\EventTickets\Routes;

use Exception;
use Give\BetaFeatures\Facades\FeatureFlag;
use Give\DonationForms\Models\DonationForm;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Routes\GetEventForms;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class GetEventFormsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.6.0
     */
    protected function getMockRequest(int $eventId): WP_REST_Request
    {
        $request = new WP_REST_Request(
            WP_REST_Server::READABLE,
            "/give-api/v2/events-tickets/event/$eventId/forms"
        );
        $request->set_param('event_id', $eventId);
        return $request;
    }

    /**
     * @since 3.6.0
     *
     * @return void
     * @throws Exception
     */
    public function testResponseContainsRelatedForm()
    {
        $event = Event::factory()->create();
        $form = DonationForm::factory()->create();

        $form->blocks->insertAfter('givewp/donation-amount', BlockModel::make([
            'name' => 'givewp/event-tickets',
            'attributes' => [
                'eventId' => $event->id,
            ],
        ]));
        $form->save();

        $response = (new GetEventForms())->handleRequest(
            $this->getMockRequest($event->id)
        );

        $this->assertContains($form->id, $response->data);
    }

    /**
     * @since 3.6.0
     *
     * @return void
     * @throws Exception
     */
    public function testResponseContainsNoRelatedForm()
    {
        $event = Event::factory()->create();

        $response = (new GetEventForms())->handleRequest(
            $this->getMockRequest($event->id)
        );

        $this->assertEmpty($response->data);
    }

    /**
     * @since 3.6.0
     *
     * @return void
     * @throws Exception
     */
    public function testResponseContainsRelatedFormWithoutCollision()
    {
        $this->refreshDatabase();
        $event = Event::factory()->create();
        $form = DonationForm::factory()->create();
        $form->blocks->insertAfter('givewp/donation-amount', BlockModel::make([
            'name' => 'givewp/event-tickets',
            'attributes' => [
                'eventId' => $event->id,
            ],
        ]));
        $form->save();

        $event2 = Event::factory()->create();
        // Create possible collision in JSON search, ie 1 vs 11.
        $collisionId = (int)($event->id . $event->id);
        Event::query()
            ->where('id', $event2->id)
            ->update(['id' => $collisionId]);
        $event2 = Event::find($collisionId);

        $form2 = DonationForm::factory()->create();
        $form2->blocks->insertAfter('givewp/donation-amount', BlockModel::make([
            'name' => 'givewp/event-tickets',
            'attributes' => [
                'eventId' => $event2->id,
            ],
        ]));
        $form2->save();

        $response = (new GetEventForms())->handleRequest(
            $this->getMockRequest($event->id)
        );

        $this->assertContains($form->id, $response->data);
        $this->assertNotContains($form2->id, $response->data);
    }
}

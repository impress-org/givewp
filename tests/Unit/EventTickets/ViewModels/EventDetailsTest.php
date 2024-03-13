<?php

namespace Give\Tests\Unit\EventTickets\ViewModels;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\EventTickets\ViewModels\EventDetails;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class EventDetailsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.6.0
     *
     * @return void
     * @throws Exception
     */
    public function testEventDetailsHasForm()
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

        $eventDetails = (new EventDetails($event))->exports();

        $this->assertEquals($form->id, $eventDetails['forms'][0]['id']);
        $this->assertEquals($form->title, $eventDetails['forms'][0]['title']);
    }

    /**
     * @since 3.6.0
     *
     * @return void
     * @throws Exception
     */
    public function testEventDetailsHasTicketType()
    {
        $ticketType = EventTicketType::factory()->create();

        $eventDetails = (new EventDetails(
            Event::find($ticketType->eventId)
        ))->exports();

        $this->assertEquals($ticketType->id, $eventDetails['ticketTypes'][0]['id']);
        $this->assertEquals($ticketType->title, $eventDetails['ticketTypes'][0]['title']);
    }

    /**
     * @since 3.6.0
     *
     * @return void
     * @throws Exception
     */
    public function testNoErrorsWhenEmptyRelationship()
    {
        $eventDetails = (new EventDetails(
            Event::factory()->create()
        ))->exports();

        $this->assertEmpty($eventDetails['ticketTypes']);
        $this->assertEmpty($eventDetails['forms']);
    }
}

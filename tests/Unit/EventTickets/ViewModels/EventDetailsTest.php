<?php

namespace Give\Tests\Unit\EventTickets\ViewModels;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class EventDetailsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
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

        $this->assertEquals($form->id, $event->forms[0]->id);
        $this->assertEquals($form->title, $event->forms[0]->title);
    }

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testEventDetailsHasTicketType()
    {
        $ticketType = EventTicketType::factory()->create();

        $event = Event::find($ticketType->eventId);

        $this->assertEquals($ticketType->id, $event->ticketTypes[0]->id);
        $this->assertEquals($ticketType->title, $event->ticketTypes[0]->title);
    }
}

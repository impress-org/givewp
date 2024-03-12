<?php

namespace Unit\EventTickets\ListTable\Columns;

use Give\EventTickets\ListTable\Columns\SalesCountColumn;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class SalesCountColumnTest extends TestCase
{
    use RefreshDatabase;

    public function testSalesCountColumnCalculatesLimitedCapacityCorrectly()
    {
        $event = Event::factory()->create();

        EventTicketType::factory()->create([
            'eventId' => $event->id,
            'capacity' => 10,
        ]);
        EventTicketType::factory()->create([
            'eventId' => $event->id,
            'capacity' => 5,
        ]);

        EventTicket::factory()->create([
            'eventId' => $event->id,
            'ticketTypeId' => 1,
        ]);
        EventTicket::factory()->create([
            'eventId' => $event->id,
            'ticketTypeId' => 1,
        ]);
        EventTicket::factory()->create([
            'eventId' => $event->id,
            'ticketTypeId' => 2,
        ]);

        $column = new SalesCountColumn();
        $cellValue = $column->getCellValue($event);
        $this->assertEquals('3 of 15', $cellValue);
    }

    public function testSalesCountColumnHandlesUnlimitedCapacityCorrectly()
    {
        $event = Event::factory()->create();

        EventTicketType::factory()->create([
            'eventId' => $event->id,
            'capacity' => 10,
        ]);
        EventTicketType::factory()->create([
            'eventId' => $event->id,
            'capacity' => null,
        ]);

        EventTicket::factory()->create([
            'eventId' => $event->id,
            'ticketTypeId' => 1,
        ]);
        EventTicket::factory()->create([
            'eventId' => $event->id,
            'ticketTypeId' => 1,
        ]);
        EventTicket::factory()->create([
            'eventId' => $event->id,
            'ticketTypeId' => 2,
        ]);

        $column = new SalesCountColumn();
        $cellValue = $column->getCellValue($event);
        $this->assertEquals('3 of ' . __('Unlimited', 'give'), $cellValue);
    }
}

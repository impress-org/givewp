<?php

namespace Unit\EventTickets\ListTable\Columns;

use Give\EventTickets\ListTable\Columns\SalesAmountColumn;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class SalesAmountColumnTest extends TestCase
{
    use RefreshDatabase;

    public function testSalesAmountColumnCalculatesLimitedCapacityCorrectly()
    {
        $event = Event::factory()->create();

        EventTicketType::factory()->create([
            'eventId' => $event->id,
            'price' => new Money(1000, give_get_currency()),
            'capacity' => 10,
        ]);
        EventTicketType::factory()->create([
            'eventId' => $event->id,
            'price' => new Money(2000, give_get_currency()),
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

        $column = new SalesAmountColumn();
        $locale = get_locale();
        $cellValue = $column->getCellValue($event, $locale);

        $expectedSales = (new Money(4000, give_get_currency()))->formatToLocale($locale);
        $expectedTotals = (new Money(20000, give_get_currency()))->formatToLocale($locale);
        $this->assertStringContainsString($expectedSales . ' of ' . $expectedTotals, $cellValue);
    }

    public function testSalesAmountColumnHandlesUnlimitedCapacityCorrectly()
    {
        $event = Event::factory()->create();

        EventTicketType::factory()->create([
            'eventId' => $event->id,
            'price' => new Money(1000, give_get_currency()),
            'capacity' => 10,
        ]);
        EventTicketType::factory()->create([
            'eventId' => $event->id,
            'price' => new Money(2000, give_get_currency()),
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

        $column = new SalesAmountColumn();
        $locale = get_locale();
        $cellValue = $column->getCellValue($event, $locale);

        $expectedSales = (new Money(4000, give_get_currency()))->formatToLocale($locale);
        $expectedTotals = __('Unlimited', 'give');
        $this->assertStringContainsString($expectedSales . ' of ' . $expectedTotals, $cellValue);
    }
}

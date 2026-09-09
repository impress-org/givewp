<?php

namespace Give\Tests\Unit\EventTickets\Actions;

use DateTime;
use Give\Donations\Models\Donation;
use Give\EventTickets\Actions\GenerateTicketsFromPurchaseData;
use Give\EventTickets\DataTransferObjects\TicketPurchaseData;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use stdClass;

/**
 * @since TBD
 */
final class GenerateTicketsFromPurchaseDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testInvokeMintsExactlyTheRequestedQuantityWithinCapacity(): void
    {
        $ticketType = EventTicketType::factory()->create([
            'eventId' => $this->makeActiveEvent()->id,
            'price' => new Money(1000, 'USD'),
            'capacity' => 5,
        ]);
        $donation = Donation::factory()->create(['amount' => new Money(1000, 'USD')]);

        $data = $this->makePurchaseData($ticketType, 3);

        (new GenerateTicketsFromPurchaseData($donation))($data);

        $this->assertSame(3, give(EventTicketRepository::class)->queryByTicketTypeId($ticketType->id)->count());
    }

    /**
     * @since TBD
     */
    public function testInvokeClampsQuantityToRemainingCapacity(): void
    {
        $ticketType = EventTicketType::factory()->create([
            'eventId' => $this->makeActiveEvent()->id,
            'price' => new Money(1000, 'USD'),
            'capacity' => 2,
        ]);
        $donation = Donation::factory()->create(['amount' => new Money(1000, 'USD')]);

        $data = $this->makePurchaseData($ticketType, 10);

        (new GenerateTicketsFromPurchaseData($donation))($data);

        $this->assertSame(2, give(EventTicketRepository::class)->queryByTicketTypeId($ticketType->id)->count());
    }

    /**
     * @since TBD
     */
    public function testInvokeMintsNothingWhenCapacityIsAlreadySpent(): void
    {
        $ticketType = EventTicketType::factory()->create([
            'eventId' => $this->makeActiveEvent()->id,
            'price' => new Money(1000, 'USD'),
            'capacity' => 1,
        ]);
        EventTicket::factory()->create([
            'eventId' => $ticketType->eventId,
            'ticketTypeId' => $ticketType->id,
        ]);
        $donation = Donation::factory()->create(['amount' => new Money(1000, 'USD')]);

        $data = $this->makePurchaseData($ticketType, 1);

        (new GenerateTicketsFromPurchaseData($donation))($data);

        $this->assertSame(1, give(EventTicketRepository::class)->queryByTicketTypeId($ticketType->id)->count());
    }

    /**
     * @since TBD
     */
    private function makePurchaseData(EventTicketType $ticketType, int $quantity): TicketPurchaseData
    {
        $object = new stdClass();
        $object->ticketId = $ticketType->id;
        $object->quantity = $quantity;

        return TicketPurchaseData::fromFieldValueObject($object, $ticketType->event);
    }

    /**
     * EventFactory's own default startDateTime (`dateTimeThisYear('+6 months')`) can land in the
     * past relative to "now", which would make the ended-event check reject it — a real event
     * date, not a bug in the check. These tests aren't about the ended-event condition, so they
     * need a guaranteed-future event instead of the shared factory default.
     *
     * @since TBD
     */
    private function makeActiveEvent(): Event
    {
        return Event::factory()->create([
            'startDateTime' => new DateTime('+1 day'),
            'endDateTime' => new DateTime('+2 days'),
        ]);
    }
}

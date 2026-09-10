<?php

namespace Give\Tests\Unit\EventTickets\DataTransferObjects;

use DateTime;
use Give\EventTickets\DataTransferObjects\TicketPurchaseData;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use stdClass;

/**
 * @since 4.16.8.1
 */
final class TicketPurchaseDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.16.8.1
     */
    public function testFromFieldValueObjectResolvesTicketTypeBelongingToTheGivenEvent(): void
    {
        $event = $this->makeActiveEvent();
        $ticketType = EventTicketType::factory()->create(['eventId' => $event->id]);

        $object = new stdClass();
        $object->ticketId = $ticketType->id;
        $object->quantity = 2;

        $data = TicketPurchaseData::fromFieldValueObject($object, $event);

        $this->assertSame($ticketType->id, $data->ticketType->id);
        $this->assertSame(2, $data->quantity);
    }

    /**
     * @since 4.16.8.1
     */
    public function testFromFieldValueObjectThrowsWhenTicketTypeBelongsToADifferentEvent(): void
    {
        $ownEvent = $this->makeActiveEvent();
        $otherEventTicketType = EventTicketType::factory()->create(['eventId' => $this->makeActiveEvent()->id]);

        $object = new stdClass();
        $object->ticketId = $otherEventTicketType->id;
        $object->quantity = 1;

        $this->expectException(InvalidArgumentException::class);

        TicketPurchaseData::fromFieldValueObject($object, $ownEvent);
    }

    /**
     * @since 4.16.8.1
     */
    public function testFromFieldValueObjectThrowsWhenTicketTypeDoesNotExist(): void
    {
        $event = $this->makeActiveEvent();

        $object = new stdClass();
        $object->ticketId = 0;
        $object->quantity = 1;

        $this->expectException(InvalidArgumentException::class);

        TicketPurchaseData::fromFieldValueObject($object, $event);
    }

    /**
     * @since 4.16.8.1
     */
    public function testFromFieldValueObjectClampsNegativeQuantityToZero(): void
    {
        $event = $this->makeActiveEvent();
        $ticketType = EventTicketType::factory()->create(['eventId' => $event->id]);

        $object = new stdClass();
        $object->ticketId = $ticketType->id;
        $object->quantity = -5;

        $data = TicketPurchaseData::fromFieldValueObject($object, $event);

        $this->assertSame(0, $data->quantity);
    }

    /**
     * @since 4.16.8.1
     */
    public function testFromFieldValueObjectThrowsWhenEventHasAlreadyEnded(): void
    {
        $event = Event::factory()->create([
            'startDateTime' => new DateTime('-3 days'),
            'endDateTime' => new DateTime('-2 days'),
        ]);
        $ticketType = EventTicketType::factory()->create(['eventId' => $event->id]);

        $object = new stdClass();
        $object->ticketId = $ticketType->id;
        $object->quantity = 1;

        $this->expectException(InvalidArgumentException::class);

        TicketPurchaseData::fromFieldValueObject($object, $event);
    }

    /**
     * EventFactory's own default startDateTime (`dateTimeThisYear('+6 months')`) can land in the
     * past relative to "now", which would make the ended-event check reject it — a real event
     * date, not a bug in the check. Tests that aren't specifically about the ended-event
     * condition need a guaranteed-future event instead of the shared factory default.
     *
     * @since 4.16.8.1
     */
    private function makeActiveEvent(): Event
    {
        return Event::factory()->create([
            'startDateTime' => new DateTime('+1 day'),
            'endDateTime' => new DateTime('+2 days'),
        ]);
    }
}

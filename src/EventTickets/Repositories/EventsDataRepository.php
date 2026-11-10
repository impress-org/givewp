<?php

declare(strict_types=1);

namespace Give\EventTickets\Repositories;

use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\Support\ValueObjects\Money;

/**
 * Loads the events list table data for a whole page at once, so the columns do not query once per row.
 *
 * @since TBD
 */
class EventsDataRepository
{
    /**
     * The ticket types by event id.
     *
     * @since TBD
     *
     * @var array<int, EventTicketType[]>
     */
    private $ticketTypes = [];

    /**
     * The sold tickets by event id.
     *
     * @since TBD
     *
     * @var array<int, EventTicket[]>
     */
    private $tickets = [];

    /**
     * The number of sold tickets by ticket type id.
     *
     * @since TBD
     *
     * @var array<int, int>
     */
    private $soldCountsByTicketType = [];

    /**
     * Loads the ticket types and the sold tickets of the given events with two queries.
     *
     * @since TBD
     *
     * @param int[] $eventIds
     */
    public static function events(array $eventIds): EventsDataRepository
    {
        $self = new self();
        $eventIds = array_values(array_unique(array_map('intval', $eventIds)));

        if (!$eventIds) {
            return $self;
        }

        $ticketTypes = give(EventTicketTypeRepository::class)
            ->prepareQuery()
            ->whereIn('event_id', $eventIds)
            ->getAll() ?? [];

        foreach ($ticketTypes as $ticketType) {
            $self->ticketTypes[$ticketType->eventId][] = $ticketType;
        }

        // The repository returns no tickets when the event tickets feature is off.
        $tickets = give(EventTicketRepository::class)
            ->prepareQuery()
            ->whereIn('tickets.event_id', $eventIds)
            ->getAll() ?? [];

        foreach ($tickets as $ticket) {
            $self->tickets[$ticket->eventId][] = $ticket;
            $ticketTypeId = $ticket->ticketTypeId;
            $self->soldCountsByTicketType[$ticketTypeId] = ($self->soldCountsByTicketType[$ticketTypeId] ?? 0) + 1;
        }

        return $self;
    }

    /**
     * The number of tickets sold for the event.
     *
     * @since TBD
     */
    public function getSoldCount(int $eventId): int
    {
        return count($this->tickets[$eventId] ?? []);
    }

    /**
     * The total number of seats of the event's ticket types.
     *
     * @since TBD
     */
    public function getCapacity(int $eventId): int
    {
        $ticketTypes = $this->ticketTypes[$eventId] ?? [];

        return array_reduce($ticketTypes, static function (int $carry, EventTicketType $ticketType) {
            return $carry + $ticketType->capacity;
        }, 0);
    }

    /**
     * The sum of the amounts paid for the event's tickets.
     *
     * The sum is made here and not when loading, so a ticket that cannot be added (for example, in
     * another currency) only breaks the cell of its own event.
     *
     * @since TBD
     */
    public function getSalesTotal(int $eventId): Money
    {
        return array_reduce($this->tickets[$eventId] ?? [], static function (Money $carry, EventTicket $ticket) {
            return $carry->add($ticket->amount);
        }, new Money(0, give_get_currency()));
    }

    /**
     * The sales total plus the price of every seat still available.
     *
     * @since TBD
     */
    public function getMaxCapacitySales(int $eventId): Money
    {
        return array_reduce($this->ticketTypes[$eventId] ?? [], function (Money $carry, EventTicketType $ticketType) {
            $ticketsAvailable = $ticketType->capacity - ($this->soldCountsByTicketType[$ticketType->id] ?? 0);

            return $carry->add($ticketType->price->multiply($ticketsAvailable));
        }, $this->getSalesTotal($eventId));
    }
}

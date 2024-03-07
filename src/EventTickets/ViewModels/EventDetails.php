<?php

namespace Give\EventTickets\ViewModels;

use Give\DonationForms\Models\DonationForm;
use Give\EventTickets\Actions\AttachAttendeeDataToTicketData;
use Give\EventTickets\DataTransferObjects\EventTicketTypeData;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;

/**
 * @unreleased
 */
class EventDetails
{

    /**
     * @unreleased
     * @var Event
     */
    protected $event;

    /**
     * @unreleased
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @unreleased
     */
    public function exports(): array
    {
        $tickets = $this->event->eventTickets()->getAll() ?? [];

        return array_merge($this->event->toArray(), [
            'ticketTypes' => array_map(function (EventTicketType $ticketType) {
                return EventTicketTypeData::make($ticketType)->toArray();
            }, $this->event->ticketTypes()->getAll() ?? []),
            'forms' => array_map(function (DonationForm $form) {
                return ['id' => $form->id, 'title' => $form->title];
            }, $this->event->forms()->getAll() ?? []),
            'tickets' => array_map(new AttachAttendeeDataToTicketData($tickets), $tickets),
        ]);
    }
}

<?php

namespace Give\EventTickets\ViewModels;

use Give\DonationForms\Models\DonationForm;
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
        return array_merge($this->event->toArray(), [

            'ticketTypes' => array_map(function (EventTicketType $ticketType) {
                return $ticketType->toArray();
            }, $this->event->ticketTypes),

            'forms' => array_map(function (DonationForm $form) {
                return ['id' => $form->id, 'title' => $form->title];
            }, $this->event->forms),

        ]);
    }
}

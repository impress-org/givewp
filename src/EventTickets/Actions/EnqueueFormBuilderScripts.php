<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\DataTransferObjects\EventTicketTypeData;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicketType;
use Give\Framework\EnqueueScript;

/**
 * @since 3.6.0
 */
class EnqueueFormBuilderScripts
{
    public function __invoke()
    {
        $scriptAsset = require GIVE_PLUGIN_DIR . 'build/eventTicketsBlock.asset.php';

        (new EnqueueScript(
            'givewp-event-tickets-block',
            'build/eventTicketsBlock.js',
            GIVE_PLUGIN_DIR,
            GIVE_PLUGIN_URL,
            'give'
        ))->enqueue();

        wp_localize_script(
            'givewp-event-tickets-block',
            'eventTicketsBlockSettings',
            [
                'events' => $this->getEvents(),
                'createEventUrl' => admin_url('edit.php?post_type=give_forms&page=give-event-tickets&new=event'),
                'listEventsUrl' => admin_url('edit.php?post_type=give_forms&page=give-event-tickets'),
                'ticketsLabel' => apply_filters(
                    'givewp_event_tickets_block/tickets_label',
                    __('Select Tickets', 'give')
                ),
                'soldOutMessage' => apply_filters(
                    'givewp_event_tickets_block/sold_out_message',
                    __(
                        'Thank you for supporting our cause. Our fundraising event tickets are officially sold out. You can still contribute by making a donation.',
                        'give'
                    )
                ),
            ]
        );

        wp_enqueue_style(
            'givewp-event-tickets-block',
            GIVE_PLUGIN_URL . 'build/eventTicketsBlock.css',
            [],
            $scriptAsset['version']
        );
    }

    private function getEvents(): array
    {
        $events = Event::query()->getAll();
        $ticketTypes = EventTicketType::query()->getAll();

        $eventData = [];

        foreach ($events as $event) {
            $eventData[$event->id] = array_map(function($propertyValue) {
                return $propertyValue instanceof \DateTime
                    ? $propertyValue->format('Y-m-d H:i:s')
                    : $propertyValue;
            }, $event->toArray());
            $eventData[$event->id]['ticketTypes'] = [];
        }

        foreach ($ticketTypes as $ticketType) {
            if ( ! isset($eventData[$ticketType->eventId])) {
                continue;
            }

            $eventData[$ticketType->eventId]['ticketTypes'][] = EventTicketTypeData::make($ticketType)->toArray();
        }

        return array_values($eventData);
    }
}

<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Models\Event;
use Give\Helpers\EnqueueScript;

class EnqueueEventDetailsScripts
{
    public function __invoke(Event $event)
    {
        $data = [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/events-tickets/events')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'adminUrl' => admin_url(),
            'event' => $event->toArray(),
        ];

        EnqueueScript::make('give-admin-event-tickets-details', 'assets/dist/js/give-admin-event-tickets-details.js')
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveEventTicketsDetails', $data)->enqueue();

        wp_enqueue_style(
            'give-admin-ui-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400..700&display=swap',
            [],
            null
        );

        wp_enqueue_style('givewp-design-system-foundation');
    }
}

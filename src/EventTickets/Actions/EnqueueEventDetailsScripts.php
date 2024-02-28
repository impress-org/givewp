<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Repositories\EventRepository;
use Give\Helpers\EnqueueScript;

class EnqueueEventDetailsScripts
{
    public function __invoke()
    {
        if (!$this->isShowing()) {
            return;
        }

        $event = give(EventRepository::class)->getById((int) $_GET['id']);

        if (!$event) {
            wp_die(__('Event not found', 'give-event-tickets'), 404);
        }

        $data = [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/events-tickets/events')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'adminUrl' => admin_url(),
            'event' => $event->toArray(),
        ];

        EnqueueScript::make('give-admin-event-tickets-details', 'assets/dist/js/give-admin-event-tickets-details.js')
            ->loadInFooter()
            ->registerTranslations()
            ->registerLocalizeData('GiveEventTickets', $data)->enqueue();

        wp_enqueue_style(
            'give-admin-ui-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400..700&display=swap',
            [],
            null
        );

        wp_enqueue_style('givewp-design-system-foundation');
    }

    /**
     * Helper function to determine if current page is Give Event Tickets admin page
     *
     * @unreleased
     */
    private function isShowing(): bool
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-event-tickets' && ! isset($_GET['view']) && isset($_GET['id']) && $_GET['id'] > 0;
    }
}

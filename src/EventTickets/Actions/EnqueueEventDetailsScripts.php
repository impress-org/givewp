<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Models\Event;
use Give\Helpers\EnqueueScript;

class EnqueueEventDetailsScripts
{
    public function __invoke(Event $event)
    {
        $data = [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/events-tickets')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'adminUrl' => admin_url(),
            'pluginUrl' => GIVE_PLUGIN_URL,
            'event' => $event->toArray(),
            'ticketTypesTable' => $this->getTicketTypesListTable(),
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

    /**
     * Return an array in the format of a ListTable for the ticket types list table
     *
     * @unreleased
     */
    private function getTicketTypesListTable(): array
    {
        return [
            'id' => 'event-ticket-types',
            'columns' => [
                [
                    'id' => 'id',
                    'label' => __('ID', 'give'),
                ],
                [
                    'id' => 'title',
                    'label' => __('Ticket', 'give'),
                ],
                [
                    'id' => 'count',
                    'label' => __('No. of tickets sold', 'give'),
                ],
                [
                    'id' => 'price',
                    'label' => __('Price', 'give'),
                ],
            ],
        ];
    }
}

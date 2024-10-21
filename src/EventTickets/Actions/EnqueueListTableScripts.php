<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\Helpers\EnqueueScript;

class EnqueueListTableScripts
{
    public function __invoke()
    {
        $data = [
            'apiRoot' => esc_url_raw(rest_url('give-api/v2/events-tickets/events/list-table')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'table' => give(EventTicketsListTable::class)->toArray(),
            'adminUrl' => admin_url(),
            'paymentMode' => give_is_test_mode(),
            'pluginUrl' => GIVE_PLUGIN_URL,
        ];

        EnqueueScript::make('give-admin-event-tickets', 'assets/dist/js/give-admin-event-tickets.js')
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
}

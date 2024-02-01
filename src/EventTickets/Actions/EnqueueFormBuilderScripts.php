<?php

namespace Give\EventTickets\Actions;

use Give\Framework\EnqueueScript;

/**
 * @unreleased
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
                'events' => [
                    [
                        'id' => 1,
                        'title' => 'Event 1',
                        'date' => '2024-01-10 10:00',
                        'description' => 'Description goes here and truncate when it exceeds two lines',
                        'tickets' => [
                            [
                                'id' => 1,
                                'name' => 'Standard',
                                'price' => 50,
                                'quantity' => 5,
                                'description' => 'Standard ticket description goes here',
                            ],
                            [
                                'id' => 2,
                                'name' => 'VIP',
                                'price' => 100,
                                'quantity' => 5,
                                'description' => 'VIP ticket description goes here',
                            ],
                        ],
                    ],
                ],
                // TODO: Update this to fetch events from the database
                'createEventUrl' => admin_url('edit.php?post_type=give_forms&page=give-event-tickets&action=new'),
                //TODO: Update this with the correct URL
                'listEventsUrl' => admin_url('edit.php?post_type=give_forms&page=give-event-tickets'),
                //TODO: Update this with the correct URL
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
}

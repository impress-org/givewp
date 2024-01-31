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
                'events' => [],
                // TODO: Update this to fetch events from the database
                'createEventUrl' => admin_url('edit.php?post_type=give_forms&page=give-event-tickets&action=new'),
                //TODO: Update this with the correct URL
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

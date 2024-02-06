<?php

namespace Give\EventTickets\Actions;

use Give\Framework\EnqueueScript;

/**
 * @unreleased
 */
class EnqueueDonationFormScripts
{
    public function __invoke()
    {
        $scriptAsset = require GIVE_PLUGIN_DIR . 'build/eventTicketsTemplate.asset.php';

        (new EnqueueScript(
            'givewp-event-tickets-template',
            'build/eventTicketsTemplate.js',
            GIVE_PLUGIN_DIR,
            GIVE_PLUGIN_URL,
            'give'
        ))->enqueue();

        wp_enqueue_style(
            'givewp-event-tickets-template',
            GIVE_PLUGIN_URL . 'build/eventTicketsTemplate.css',
            [],
            $scriptAsset['version']
        );
    }
}

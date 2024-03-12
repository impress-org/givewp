<?php

namespace Give\EventTickets\Actions;

/**
 * @unreleased
 */
class EnqueueDonationFormScripts
{
    public function __invoke()
    {
        $scriptAsset = require GIVE_PLUGIN_DIR . 'build/eventTicketsTemplate.asset.php';

        wp_enqueue_script(
            'givewp-event-tickets-template',
            GIVE_PLUGIN_URL . 'build/eventTicketsTemplate.js',
            $scriptAsset['dependencies'],
            false,
            true
        );

        wp_enqueue_style(
            'givewp-event-tickets-template',
            GIVE_PLUGIN_URL . 'build/eventTicketsTemplate.css',
            [],
            $scriptAsset['version']
        );
    }
}

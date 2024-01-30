<?php

namespace Give\EventTickets\Actions;

/**
 * @unreleased
 */
class RegisterEventsPostType
{
    public function __invoke()
    {
        register_post_type( 'give_event', [
            'public'    => false,
            'show_in_rest' => true,
            'label'     => __( 'Events', 'give-events' ),
            'menu_icon' => 'dashicons-calendar-alt',
        ]);
    }
}

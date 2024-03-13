<?php

namespace Give\EventTickets\Actions;

use Give\EventTickets\Models\Event;

/**
 * @since 3.6.0
 */
class RegisterEventsMenuItem
{
    public function __invoke()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Events', 'give'),
            esc_html__('Events', 'give') . ' <span class="update-plugins">BETA</span>',
            'edit_give_forms',
            'give-event-tickets',
            [$this, 'render']
        );
    }

    /**
     * Render admin page container
     *
     * @since 3.6.0
     */
    public function render()
    {
        if(isset($_GET['id'])) {
            $event = Event::find(absint($_GET['id']));

            if (!$event) {
                wp_die(__('Event not found', 'give-event-tickets'), 404);
            }

            give(EnqueueEventDetailsScripts::class)($event);
        } else {
            give(EnqueueListTableScripts::class)();
        }

        echo '<div id="give-admin-event-tickets-root"></div>';
    }
}

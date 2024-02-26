<?php

namespace Give\EventTickets\Actions;

/**
 * @unreleased
 */
class RegisterEventsMenuItem
{
    public function __invoke()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Events', 'give'),
            esc_html__('Events', 'give') . ' <span class="give-menu-badge">Beta</span>',
            'edit_give_forms',
            'give-event-tickets',
            [$this, 'render']
        );
    }

    /**
     * Render admin page container
     *
     * @unreleased
     */
    public function render()
    {
        echo '<div id="give-admin-event-tickets-root"></div>';
    }
}

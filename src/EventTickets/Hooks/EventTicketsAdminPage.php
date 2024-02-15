<?php

namespace Give\EventTickets\Hooks;

use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\Helpers\EnqueueScript;

class EventTicketsAdminPage
{
    /**
     * @var string
     */
    private $apiRoot;

    /**
     * @var string
     */
    private $apiNonce;

    /**
     * @var string
     */
    private $adminUrl;

    public function __construct()
    {
        $this->apiRoot = esc_url_raw(rest_url('give-api/v2/admin/event-tickets'));
        $this->apiNonce = wp_create_nonce('wp_rest');
        $this->adminUrl = admin_url();
    }

    /**
     * @unreleased
     */
    public function registerMenuItem()
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
     * @unreleased
     */
    public function loadScripts()
    {
        $data = [
            'apiRoot' => $this->apiRoot,
            'apiNonce' => $this->apiNonce,
            'table' => give(EventTicketsListTable::class)->toArray(),
            'adminUrl' => $this->adminUrl,
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

    /**
     * Render admin page container
     *
     * @unreleased
     */
    public function render()
    {
        echo '<div id="give-admin-event-tickets-root"></div>';
    }

    /**
     * Helper function to determine if current page is Give Event Tickets admin page
     *
     * @unreleased
     */
    public static function isShowing(): bool
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-event-tickets' && ! isset($_GET['view']);
    }
}

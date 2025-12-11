<?php

namespace Give\Subscriptions;

use Give\Subscriptions\Actions\LoadSubscriptionDetailsAssets;
use Give\Subscriptions\Actions\LoadSubscriptionsListTableAssets;
use Give\Subscriptions\Models\Subscription;

class SubscriptionsAdminPage
{
    /**
     * @since 2.24.0
     */
    public function loadScripts()
    {
        give(LoadSubscriptionsListTableAssets::class)();
    }

    /**
     * Render the Subscription Details page.
     *
     * @since 4.8.0
     */
    public function render()
    {
        if (self::isShowingDetailsPage()) {
            remove_action('give_forms_page_give-subscriptions', 'give_subscriptions_page');

            $subscription = Subscription::find(absint($_GET['id']));

            if ( ! $subscription) {
                wp_die(__('Subscription not found', 'give'), 404);
            }

            give(LoadSubscriptionDetailsAssets::class)();
        } else {
            give(LoadSubscriptionsListTableAssets::class)();
        }

        echo '<div id="give-admin-subscriptions-root"></div>';
    }

    /**
     * Display a button on the old subscriptions table that switches to the React view
     *
     * @since 2.24.0
     */
    public function renderReactSwitch()
    {
        ?>
        <script type="text/javascript">
            function showReactTable () {
                fetch( '<?php echo esc_url_raw(rest_url('give-api/v2/admin/subscriptions/view?isLegacy=0')) ?>', {
                    method: 'GET',
                    headers: {
                        ['X-WP-Nonce']: '<?php echo wp_create_nonce('wp_rest') ?>'
                    }
                })
                    .then((res) => {
                        window.location.reload();
                    });
            }
            jQuery( function() {
                jQuery(jQuery(".wrap .wp-header-end")).before(
                    '<button class="page-title-action" onclick="showReactTable()"><?php _e('Switch to New View', 'give') ?></button>'
                );
            });
        </script>
        <?php
    }

    /**
     * Helper function to determine if current page is Give Subscriptions admin page
     *
     * @since 2.24.0
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-subscriptions' && ! isset($_GET['view']);
    }

    /**
     * @since 4.8.0
     */
    public static function isShowingDetailsPage(): bool
    {
        return isset($_GET['id'], $_GET['page']) && 'give-subscriptions' === $_GET['page'];
    }
}

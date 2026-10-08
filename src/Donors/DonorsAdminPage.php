<?php

namespace Give\Donors;

use Give\Donors\Actions\LoadDonorDetailsAssets;
use Give\Donors\Actions\LoadDonorsListTableAssets;
use Give\Donors\Models\Donor;
use Give\Framework\Permissions\Facades\UserPermissions;

class DonorsAdminPage
{
    /**
     * @since 4.14.0 update permission capability to use facade
     * @since 2.20.0
     */
    public function registerMenuItem()
    {
        remove_submenu_page(
            'edit.php?post_type=give_forms',
            'give-donors'
        );

        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Donors', 'give'),
            esc_html__('Donors', 'give'),
            UserPermissions::donors()->viewCap(),
            'give-donors',
            [$this, 'render']
        );
    }

    /**
     * Render admin page container
     *
     * @since TBD Escape output. Guard the id param.
     * @since 4.4.0 Add new details page view
     * @since 2.20.0
     */
    public function render()
    {
        if (self::isShowingDetailsPage()) {
            $donor = Donor::find(absint($_GET['id'] ?? 0)); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only id param; it only selects which record to show and saves nothing.

            if (! $donor) {
                wp_die(esc_html__('Donor not found', 'give'), 404);
            }

            give(LoadDonorDetailsAssets::class)();
        } elseif (self::isShowing()) {
            give(LoadDonorsListTableAssets::class)();
        }

        echo '<div id="give-admin-donors-root"></div>';
    }

    /**
     * Display a button on the old donation forms table that switches to the React view
     *
     * @since TBD Escape translated output.
     * @since 2.20.0
     */
    public function renderReactSwitch()
    {
        ?>
        <script type="text/javascript">
            function showReactTable() {
                fetch('<?php echo esc_url_raw(rest_url('give-api/v2/admin/donors/view?isLegacy=0')) ?>', {
                    method: 'GET',
                    headers: {
                        ['X-WP-Nonce']: '<?php echo esc_js(wp_create_nonce('wp_rest')) ?>',
                    },
                })
                    .then((res) => {
                        window.location.reload();
                    });
            }

            jQuery(function () {
                jQuery(jQuery(".wrap .wp-header-end")).before(
                    '<button class="page-title-action" onclick="showReactTable()"><?php echo esc_js(__('Switch to New View', 'give')) ?></button>',
                );
            });
        </script>
        <?php
    }

    /**
     * Helper function to determine if current page is Give Donors admin page
     * @since 2.20.0
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-donors' && ! isset($_GET['id']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page check; it only decides what to show and saves nothing.
    }


    /**
     * @since 4.4.0
     */
    public static function isShowingDetailsPage(): bool
    {
        return isset($_GET['id'], $_GET['page']) && 'give-donors' === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page check; it only decides what to show and saves nothing.
    }

    /**
     * @since 4.13.1
     */
    public static function isShowingNewDetailsPage(): bool
    {
        return self::isShowingDetailsPage() && isset($_GET['view']) && $_GET['view'] === 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page check; it only decides what to show and saves nothing.
    }

    /**
     * Get the URL for the details page
     *
     * @since 4.4.0
     */
    public static function getDetailsPageUrl(int $donorId): string
    {
        return admin_url("edit.php?post_type=give_forms&page=give-donors&id=$donorId");
    }
}

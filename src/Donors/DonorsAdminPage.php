<?php

namespace Give\Donors;

use Give\Donors\Actions\LoadDonorDetailsAssets;
use Give\Donors\Actions\LoadDonorsListTableAssets;
use Give\Donors\Models\Donor;

class DonorsAdminPage
{
    /**
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
            'edit_give_forms',
            'give-donors',
            [$this, 'render']
        );
    }

    /**
     * Render admin page container
     *
     * @since 4.4.0 Add new details page view
     * @since 2.20.0
     */
    public function render()
    {
        if (self::isShowingDetailsPage()) {
            $donor = Donor::find(absint($_GET['id']));

            if ( ! $donor) {
                wp_die(__('Donor not found', 'give'), 404);
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
                        ['X-WP-Nonce']: '<?php echo wp_create_nonce('wp_rest') ?>',
                    },
                })
                    .then((res) => {
                        window.location.reload();
                    });
            }

            jQuery(function () {
                jQuery(jQuery(".wrap .wp-header-end")).before(
                    '<button class="page-title-action" onclick="showReactTable()"><?php _e('Switch to New View', 'give') ?></button>',
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
        return isset($_GET['page']) && $_GET['page'] === 'give-donors' && ! isset($_GET['id']);
    }


    /**
     * @since 4.4.0
     */
    public static function isShowingDetailsPage(): bool
    {
        return isset($_GET['id'], $_GET['page']) && 'give-donors' === $_GET['page'];
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

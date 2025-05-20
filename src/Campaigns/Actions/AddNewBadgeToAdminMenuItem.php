<?php

namespace Give\Campaigns\Actions;

/**
 * @since 4.0.0
 */
class AddNewBadgeToAdminMenuItem {
    /**
     * @return void
     */
    public function __invoke()
    {
        // only continue if in admin
        if (!is_admin()) {
            return;
        }

        // only show badge for existing users who have upgraded from a version prior to 4.0.0
        if (version_compare((string)get_option('give_version_upgraded_from', '4.0.0'), '4.0.0', '>=')) {
            return;
        }

        // only show badge if not dismissed
        if (get_option('givewp_new_notification_campaigns_dismissed', false) !== false) {
            return;
        }

        // add "NEW" badge to the GiveWP menu item
         add_action( 'admin_menu', function() {
            global $menu;
            array_walk($menu, static function (&$item) {
                if ($item[0] === 'GiveWP') {
                    $title = $item[0];
                    $item[0] = sprintf('<span>%s </span><span class="update-plugins">%s</span>', $title, __('NEW', 'give'));
                }
            });
        });

         // dismiss the notice when visiting the campaigns list page
         if (isset($_GET['page']) && $_GET['page'] === 'give-campaigns') {
            update_option('givewp_new_notification_campaigns_dismissed', true);
         }
    }
}

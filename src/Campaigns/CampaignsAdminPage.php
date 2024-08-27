<?php

namespace Give\Campaigns;

use Give\Campaigns\Actions\LoadCampaignsListTableAssets;

/**
 * @unreleased
 */
class CampaignsAdminPage
{
    /**
     * @unreleased
     */
    public function addCampaignsSubmenuPage()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('Campaigns', 'give'),
            esc_html__('Campaigns', 'give'),
            'edit_give_forms',
            'give-campaigns',
            [$this, 'renderCampaignsPage'],
            0
        );
    }

    /**
     * @unreleased
     */
    public function renderCampaignsPage()
    {
        give(LoadCampaignsListTableAssets::class)();

        echo '<div id="give-admin-campaigns-list-table-root"></div>';
    }
}

<?php

namespace Give\Campaigns;

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
        echo '<div id="give-admin-campaigns-root"><p style="padding: 200px 30px">The campaigns list table will be loaded here...</p></div>';
    }
}

<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;

/**
 * @unreleased
 */
class RedirectLegacyCreateFormToCreateCampaign
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        if (
            $GLOBALS['pagenow'] === 'post-new.php'
            && isset($_GET['post_type']) && $_GET['post_type'] === 'give_forms'
        ) {
            if (
                ! isset($_GET['campaignId'])
                || ! (Campaign::find(absint($_GET['campaignId'])))
            ) {
                wp_safe_redirect(admin_url('edit.php?post_type=give_forms&page=give-campaigns&new=campaign'));
                exit;
            }
        }
    }
}

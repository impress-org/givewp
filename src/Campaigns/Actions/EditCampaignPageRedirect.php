<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\CampaignPage;

/**
 * @unreleased
 */
class EditCampaignPageRedirect
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $campaign = Campaign::find(
            // @TODO (Maybe) refactor to request object.
            isset($_GET['campaign_id']) ? absint($_GET['campaign_id']) : 0
        );

        $page = $campaign->page ?: CampaignPage::create([
            // TODO: Add default attributes.
        ]);

        wp_redirect($page->getEditLinkUrl());
        exit();
    }
}

<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\CampaignPage;

/**
 * @unreleased
 */
class RedirectDisabledCampaignPage
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        if('give_campaign_page' !== get_post_type()) {
            return;
        }

        $campaignPage = CampaignPage::find(get_the_ID());

        if(!$campaignPage->campaign()->enableCampaignPage) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            get_template_part(404);
            exit;
        }
    }
}

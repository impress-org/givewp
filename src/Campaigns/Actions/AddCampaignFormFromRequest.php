<?php

namespace Give\Campaigns\Actions;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Models\DonationForm;
use WP_Post;

/**
 * @since 4.0.0
 */
class AddCampaignFormFromRequest
{
    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function optionBasedFormEditor(int $formId, WP_Post $post, bool $update)
    {
        if ( ! $update && isset($_GET['campaignId']) && $campaign = Campaign::find(absint($_GET['campaignId']))) {
            give(CampaignRepository::class)->addCampaignForm($campaign, $formId);
        }
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function visualFormBuilder(DonationForm $donationForm)
    {
        if (isset($_GET['campaignId']) && $campaign = Campaign::find(absint($_GET['campaignId']))) {
            give(CampaignRepository::class)->addCampaignForm($campaign, $donationForm->id);
        }
    }
}

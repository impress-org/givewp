<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Shortcodes\CampaignShortcode;
use Give\Campaigns\Shortcodes\CampaignFormShortcode;
use Give\Campaigns\Shortcodes\CampaignGridShortcode;
use Give\Campaigns\Shortcodes\CampaignCommentsShortcode;
use Give\Campaigns\Shortcodes\CampaignDonorsShortcode;
use Give\Campaigns\Shortcodes\CampaignDonationsShortcode;
use Give\Campaigns\Shortcodes\CampaignStatsShortcode;
use Give\Campaigns\Shortcodes\CampaignGoalShortcode;

/**
 * @since 4.5.0 new imports to support the Shortcodes directory.
 * @since 4.2.0
 */
class RegisterCampaignShortcodes
{
    /**
     * @since 4.7.0 add givewp_campaign_stats and givewp_campaign_goal shortcodes
     * @since 4.5.0 new shortcodes for CampaignDonors, CampaignComments, CampaignDonations.
     * @since 4.2.0
     */
    public function __invoke()
    {
        add_shortcode('givewp_campaign', [new CampaignShortcode(), 'renderShortcode']);
        add_shortcode('givewp_campaign_grid', [new CampaignGridShortcode(), 'renderShortcode']);
        add_shortcode('givewp_campaign_form', [new CampaignFormShortcode(), 'renderShortcode']);
        add_shortcode('givewp_campaign_comments', [new CampaignCommentsShortcode(), 'renderShortcode']);
        add_shortcode('givewp_campaign_donors', [new CampaignDonorsShortcode(), 'renderShortcode']);
        add_shortcode('givewp_campaign_donations', [new CampaignDonationsShortcode(), 'renderShortcode']);
        add_shortcode('givewp_campaign_stats', [new CampaignStatsShortcode(), 'renderShortcode']);
        add_shortcode('givewp_campaign_goal', [new CampaignGoalShortcode(), 'renderShortcode']);
    }
}

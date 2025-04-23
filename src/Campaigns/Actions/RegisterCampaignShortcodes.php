<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Blocks\Campaign\CampaignShortcode;
use Give\Campaigns\Blocks\CampaignForm\CampaignFormShortcode;
use Give\Campaigns\Blocks\CampaignGrid\CampaignGridShortcode;

/**
 * @unreleased
 */
class RegisterCampaignShortcodes
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        add_shortcode('givewp_campaign', [new CampaignShortcode(), 'renderShortcode']);
        add_shortcode('givewp_campaign_grid', [new CampaignGridShortcode(), 'renderShortcode']);
        add_shortcode('givewp_campaign_form', [new CampaignFormShortcode(), 'renderShortcode']);
    }
}

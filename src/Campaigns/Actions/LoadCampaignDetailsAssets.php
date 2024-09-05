<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ViewModels\CampaignDetailsPage;

/**
 * @unreleased
 */
class LoadCampaignDetailsAssets
{
    /**
     * @unreleased
     */
    public function __invoke(Campaign $campaign)
    {
        $handleName = 'givewp-admin-campaign-details';

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'assets/dist/js/give-admin-campaign-details.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script($handleName, 'GiveCampaignDetails',
            [
                'apiRoot' => esc_url_raw(rest_url('give-api/v2/campaigns')),
                'apiNonce' => wp_create_nonce('wp_rest'),
                'adminUrl' => admin_url(),
                'pluginUrl' => GIVE_PLUGIN_URL,
                'campaignDetailsPage' => (new CampaignDetailsPage($campaign))->exports(),
            ]
        );

        wp_enqueue_script($handleName);
        wp_enqueue_style('givewp-design-system-foundation');
    }
}

<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class LoadCampaignDetailsAssets
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $campaign = Campaign::find(
            absint($_GET['id'])
        );

        $handleName = 'givewp-admin-campaign-details';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignDetails.asset.php');

        wp_enqueue_editor();

        wp_register_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignDetails.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        $defaultForm = $campaign->defaultForm();
        wp_localize_script($handleName, 'GiveCampaignDetails',
            [
                'adminUrl' => admin_url(),
                'currency' => give_get_currency(),
                'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION') ? GIVE_RECURRING_VERSION : null,
                'defaultForm' => $defaultForm ? $defaultForm->settings->formTitle : null,
                'donationForms' => array_map(function(DonationForm $form) {
                    return [
                        'id' => $form->id,
                        'title' => $form->settings->formTitle,
                    ];
                }, $campaign->forms()->getAll()),
            ]
        );

        wp_enqueue_script($handleName);
        wp_enqueue_style('givewp-design-system-foundation');
        wp_enqueue_style(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignDetails.css',
            [],
            $scriptAsset['version']
        );
    }
}

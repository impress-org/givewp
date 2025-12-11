<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 4.3.0 set script translations
 * @since 4.0.0
 */
class EnqueueCampaignPageEditorAssets
{
    /**
     * @since 4.3.0 set script translations
     * @since 4.0.0
     */
    public function __invoke()
    {
        $currentPost = get_post();

        if (!$currentPost || !get_post_meta($currentPost->ID, CampaignPageMetaKeys::CAMPAIGN_ID, true)) {
            return;
        }

        $campaignPage = CampaignPage::find($currentPost->ID);

        if (!$campaignPage || !$campaignPage->campaignId) {
            return;
        }

        $handleName = 'givewp-campaign-page-post-type-editor';
        $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/campaignPagePostTypeEditor.asset.php');

        $campaignDetailsURL = add_query_arg([
            'post_type' => 'give_forms',
            'page' => 'give-campaigns',
            'id' => $campaignPage->campaignId,
        ], admin_url('edit.php'));

        wp_enqueue_script(
            $handleName,
            GIVE_PLUGIN_URL . 'build/campaignPagePostTypeEditor.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_localize_script(
            $handleName,
            'giveCampaignPage',
            [
                'campaignDetailsURL' => $campaignDetailsURL,
            ]
        );

        Language::setScriptTranslations($handleName);
    }
}

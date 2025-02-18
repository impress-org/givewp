<?php

namespace Give\Campaigns\Actions;

use WP_Block_Type_Registry;

/**
 * @unreleased
 *
 * Allow specific campaign blocks only on the campaign page
 */
class DisableBlocksOutsideCampaignContext
{
    public function __invoke($blocks, $editor)
    {
        $disabledBlocks = [
            'givewp/campaign-cover-block',
            'givewp/campaign-donations',
            'givewp/campaign-donors',
            'givewp/campaign-goal',
            'givewp/campaign-stats-block',
            'givewp/campaign-title',
            'givewp/campaign-donate-button',
        ];

        if ($editor->post->post_type !== 'give_campaign_page') {
            $registeredBlocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
            $registeredBlocks = array_keys($registeredBlocks);

            return array_diff($registeredBlocks, $disabledBlocks);
        }

        return $blocks;
    }
}

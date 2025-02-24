<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;

/**
 * @unreleased
 */
class CreateDefaultLayoutForCampaignPage
{
    /**
     * @unreleased
     */
    protected $blocks = [
        '<!-- wp:givewp/campaign-cover-block {"campaignId":"%id%"} /-->',
        '<!-- wp:givewp/campaign-goal {"campaignId":"%id%"} /-->',
        '<!-- wp:givewp/campaign-donate-button {"campaignId":"%id%"} /-->',
        '<!-- wp:paragraph --><p>%description%</p><!-- /wp:paragraph -->',
        '<!-- wp:givewp/campaign-donations {"campaignId":"%id%"} /-->',
        '<!-- wp:givewp/campaign-donors {"campaignId":"%id%"} /-->',
    ];

    /**
     * @unreleased
     */
    public function __invoke(Campaign $campaign)
    {

        $layout = array_map(function($block) use ($campaign) {
            return str_replace(
                ['%id%', '%description%'],
                [$campaign->id, $campaign->shortDescription],
                $block
            );
        }, $this->blocks);

        return implode('', $layout);
    }
}

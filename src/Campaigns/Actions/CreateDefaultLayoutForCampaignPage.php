<?php

namespace Give\Campaigns\Actions;

class CreateDefaultLayoutForCampaignPage
{
    protected $blocks = [
        'givewp/campaign-cover-block',
        'givewp/campaign-goal',
        'givewp/campaign-donations',
        'givewp/campaign-donors',
    ];

    public function __invoke($campaignId)
    {
        $layout = array_map(function($blockName) use ($campaignId) {
            return sprintf('<!-- wp:%s {"campaignId":"%d"} /-->', $blockName, $campaignId);
        }, $this->blocks);

        return implode('', $layout);
    }
}

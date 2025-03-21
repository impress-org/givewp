<?php

namespace Give\Campaigns\Actions;

/**
 * @since 4.0.0
 */
class CreateDefaultLayoutForCampaignPage
{
    /**
     * @since 4.0.0
     */
    protected $blocks = [
        '<!-- wp:givewp/campaign-title {"campaignId":"%id%"} /-->',
        '<!-- wp:givewp/campaign-cover-block {"campaignId":"%id%"} /-->',
        '<!-- wp:givewp/campaign-goal {"campaignId":"%id%"} /-->',
        '<!-- wp:givewp/campaign-donate-button {"campaignId":"%id%"} /-->',
        '<!-- wp:paragraph --><p>%description%</p><!-- /wp:paragraph -->',
        '<!-- wp:givewp/campaign-donations {"campaignId":"%id%"} /-->',
        '<!-- wp:givewp/campaign-donors {"campaignId":"%id%"} /-->',
    ];

    /**
     * @since 4.0.0
     */
    public function __invoke(int $campaignId, string $shortDescription): string
    {
        $layout = array_map(function ($block) use ($campaignId, $shortDescription) {
            return str_replace(
                ['%id%', '%description%'],
                [$campaignId, $shortDescription],
                $block
            );
        }, $this->blocks);

        return implode(PHP_EOL, $layout);
    }
}

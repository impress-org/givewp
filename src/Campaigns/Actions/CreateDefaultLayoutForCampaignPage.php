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
        '<!-- wp:columns -->',
        '<div class="wp-block-columns"><!-- wp:column -->',
        '<div class="wp-block-column"><!-- wp:post-featured-image {"style":{"border":{"radius":"8px"}}} /--></div>',
        '<!-- /wp:column -->',
        '<!-- wp:column -->',
        '<div class="wp-block-column"><!-- wp:givewp/campaign-goal {"campaignId":%id%} /-->',
        '<!-- wp:columns -->',
        '<div class="wp-block-columns"><!-- wp:column -->',
        '<div class="wp-block-column"><!-- wp:givewp/campaign-stats-block {"campaignId":%id%} /--></div>',
        '<!-- /wp:column -->',
        '<!-- wp:column -->',
        '<div class="wp-block-column"><!-- wp:givewp/campaign-stats-block {"campaignId":%id%,"statistic":"average-donation"} /--></div>',
        '<!-- /wp:column --></div>',
        '<!-- /wp:columns -->',
        '<!-- wp:givewp/campaign-donate-button {"campaignId":%id%} /--></div>',
        '<!-- /wp:column --></div>',
        '<!-- /wp:columns -->',
        '<!-- wp:paragraph --><p>%description%</p><!-- /wp:paragraph -->',
        '<!-- wp:givewp/campaign-donations {"campaignId":%id%} /-->',
        '<!-- wp:givewp/campaign-donors {"campaignId":%id%} /-->',
    ];

    /**
     * @since 4.0.0
     */
    public function __invoke(int $campaignId, string $shortDescription): string
    {
        $layout = array_map(static function ($block) use ($campaignId, $shortDescription) {
            return str_replace(
                ['%id%', '%description%'],
                [$campaignId, $shortDescription],
                $block
            );
        }, $this->blocks);

        return implode('', $layout);
    }
}

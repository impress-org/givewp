<?php

namespace Give\Campaigns\Actions;

/**
 * @unreleased
 */
class CreateDefaultLayoutForCampaignPage
{
    /**
     * @unreleased
     */
    protected $blocks = [
        '<!-- wp:columns -->',
        '<div class="wp-block-columns"><!-- wp:column -->',
        '<div class="wp-block-column"><!-- wp:post-featured-image /--></div>',
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
     * @unreleased
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

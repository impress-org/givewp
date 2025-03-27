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
    public function getBlocks()
    {
        return '<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:post-featured-image {"aspectRatio":"16/9","style":{"border":{"radius":"8px"}}} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:givewp/campaign-goal {"campaignId":"%campaignId%"} /-->

<!-- wp:group {"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group"><!-- wp:givewp/campaign-stats-block {"campaignId":"%campaignId%","statistic":"average-donation"} /-->

<!-- wp:givewp/campaign-stats-block {"campaignId":"%campaignId%"} /--></div>
<!-- /wp:group -->

<!-- wp:givewp/campaign-donate-button {"campaignId":"%campaignId%"} /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:heading -->
<h2 class="wp-block-heading"></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>%description%</p>
<!-- /wp:paragraph -->

<!-- wp:givewp/campaign-donations {"campaignId":%campaignId%} /-->

<!-- wp:givewp/campaign-donors {"campaignId":%campaignId%} /-->';
    }

    /**
     * @unreleased
     */
    public function __invoke(int $campaignId, string $shortDescription): string
    {
        return str_replace(
            ['%id%', '%description%'],
            [$campaignId, $shortDescription],
            $this->getBlocks()
        );
    }
}

<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Actions\CreateDefaultLayoutForCampaignPage;
use Give\Campaigns\Models\Campaign;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class CreateDefaultLayoutForCampaignPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testCampaignPageHasDefaultLayout()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create([
            'id' => 1,
            'shortDescription' => 'This is the start of the story',
        ]);

        $expectedLayout = <<<HTML
<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:post-featured-image {"style":{"border":{"radius":"8px"}}} /--></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:givewp/campaign-goal {"campaignId":1} /--><!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:givewp/campaign-stats-block {"campaignId":1} /--></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:givewp/campaign-stats-block {"campaignId":1,"statistic":"average-donation"} /--></div><!-- /wp:column --></div><!-- /wp:columns --><!-- wp:givewp/campaign-donate-button {"campaignId":1} /--></div><!-- /wp:column --></div><!-- /wp:columns --><!-- wp:paragraph --><p>This is the start of the story</p><!-- /wp:paragraph --><!-- wp:givewp/campaign-donations {"campaignId":1} /--><!-- wp:givewp/campaign-donors {"campaignId":1} /-->
HTML;

        $this->assertEquals(
            $expectedLayout,
            (new CreateDefaultLayoutForCampaignPage())($campaign->id, $campaign->shortDescription)
        );
    }
}

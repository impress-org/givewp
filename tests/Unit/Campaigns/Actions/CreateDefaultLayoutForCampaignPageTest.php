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
<!-- wp:givewp/campaign-cover-block {"campaignId":1} /-->
<!-- wp:givewp/campaign-goal {"campaignId":1} /-->
<!-- wp:givewp/campaign-donate-button {"campaignId":1} /-->
<!-- wp:paragraph --><p>This is the start of the story</p><!-- /wp:paragraph -->
<!-- wp:givewp/campaign-donations {"campaignId":1} /-->
<!-- wp:givewp/campaign-donors {"campaignId":1} /-->
HTML;

        $this->assertEquals(
            $expectedLayout,
            (new CreateDefaultLayoutForCampaignPage())($campaign->id, $campaign->shortDescription)
        );
    }
}

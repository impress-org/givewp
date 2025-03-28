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

        $layout = (new CreateDefaultLayoutForCampaignPage())($campaign->id, $campaign->shortDescription);

        $this->markTestIncomplete();
    }
}

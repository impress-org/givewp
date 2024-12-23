<?php

namespace Give\Tests\Unit\Campaigns\Models;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class CampaignPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testFindShouldReturnCampaignPage()
    {
        $campaign = Campaign::factory()->create();
        $campaignPage = CampaignPage::create([
            'campaignId' => $campaign->id,
        ]);

        $campaignPageFresh = CampaignPage::find($campaignPage->id);

        $this->assertInstanceOf(CampaignPage::class, $campaignPageFresh);
        $this->assertEquals($campaignPage->id, $campaignPageFresh->id);
    }
}

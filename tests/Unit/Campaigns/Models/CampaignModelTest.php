<?php

namespace Give\Tests\Unit\Campaigns\Models;

use Give\Campaigns\Models\Campaign;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class CampaignModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testFindShouldReturnCampaign()
    {
        $mockCampaign = Campaign::factory()->create();
        $campaign = Campaign::find($mockCampaign->id);

        $this->assertInstanceOf(Campaign::class, $campaign);
        $this->assertEquals($campaign->toArray(), $mockCampaign->toArray());
    }
}

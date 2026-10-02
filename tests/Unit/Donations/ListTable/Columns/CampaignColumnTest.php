<?php

namespace Give\Tests\Unit\Donations\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Donations\ListTable\Columns\CampaignColumn;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class CampaignColumnTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testSaysNoCampaignWhenDonationHasNoCampaign()
    {
        $donation = new Donation([
            'campaignId' => 0,
            'formTitle' => 'Standalone Form',
        ]);

        $this->assertSame('No campaign', (new CampaignColumn())->getCellValue($donation));
    }

    /**
     * @since TBD
     */
    public function testSaysNoCampaignWhenCampaignNoLongerExists()
    {
        $donation = new Donation([
            'campaignId' => 999999,
            'formTitle' => 'Orphaned Form',
        ]);

        $this->assertSame('No campaign', (new CampaignColumn())->getCellValue($donation));
    }

    /**
     * @since TBD
     */
    public function testLinksToCampaignWhenDonationHasCampaign()
    {
        $campaign = Campaign::factory()->create(['title' => 'Spring Drive']);
        $donation = new Donation(['campaignId' => $campaign->id]);

        $cell = (new CampaignColumn())->getCellValue($donation);

        $this->assertStringContainsString('Spring Drive', $cell);
        $this->assertStringContainsString("id={$campaign->id}", $cell);
    }
}

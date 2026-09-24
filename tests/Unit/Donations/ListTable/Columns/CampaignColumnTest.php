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
    public function testLinksToTheCampaignPage(): void
    {
        $campaign = Campaign::factory()->create(['title' => 'Spring Drive']);
        $donation = Donation::factory()->create(['campaignId' => $campaign->id, 'formId' => $campaign->defaultFormId]);

        $cell = (new CampaignColumn())->getCellValue($donation);

        $this->assertStringContainsString('Spring Drive', $cell);
        $this->assertStringContainsString("id={$campaign->id}", $cell);
    }

    /**
     * @since TBD
     */
    public function testSaysNoCampaignWhenTheDonationHasNone(): void
    {
        $donation = Donation::factory()->create(['campaignId' => 0]);

        $this->assertSame('No campaign', (new CampaignColumn())->getCellValue($donation));
    }

    /**
     * @since TBD
     */
    public function testSaysNoCampaignWhenTheCampaignNoLongerExists(): void
    {
        $donation = Donation::factory()->create(['campaignId' => 999999]);

        $this->assertSame('No campaign', (new CampaignColumn())->getCellValue($donation));
    }
}

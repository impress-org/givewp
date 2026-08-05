<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Actions\GetCampaignName;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class GetCampaignNameTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testReturnsCampaignTitleWhenDonationHasCampaign()
    {
        $campaign = Campaign::factory()->create(['title' => 'Save The Whales']);
        $donation = Donation::factory()->create(['formId' => $campaign->defaultFormId]);

        $this->assertSame(
            'Save The Whales',
            (new GetCampaignName())($donation->id, $donation->formId)
        );
    }

    /**
     * @since TBD
     */
    public function testFallsBackToFormTitleWhenCampaignTitleIsEmpty()
    {
        $campaign = Campaign::factory()->create(['title' => 'Temporary Title']);
        $donation = Donation::factory()->create(['formId' => $campaign->defaultFormId]);

        $campaign->title = '';
        give(\Give\Campaigns\Repositories\CampaignRepository::class)->update($campaign);

        wp_update_post([
            'ID' => $campaign->defaultFormId,
            'post_title' => 'Form Fallback Title',
        ]);

        $this->assertSame(
            'Form Fallback Title',
            (new GetCampaignName())($donation->id, $donation->formId)
        );
    }

    /**
     * @since TBD
     */
    public function testResolvesCampaignFromFormIdWhenDonationIdIsMissing()
    {
        $campaign = Campaign::factory()->create(['title' => 'Campaign From Form']);

        $this->assertSame(
            'Campaign From Form',
            (new GetCampaignName())(0, $campaign->defaultFormId)
        );
    }
}

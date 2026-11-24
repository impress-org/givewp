<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
final class UnarchiveCampaignFormAsPublishStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testShouldClearCachedPostStatusOfDefaultForm(): void
    {
        $campaign = Campaign::factory()->create();

        $campaign->status = CampaignStatus::ARCHIVED();
        $campaign->save();

        /* Prime the post cache the way a front end request does. */
        $this->assertSame('draft', get_post_status($campaign->defaultFormId));

        /* Reload so the original status is the archived one, as it is on a later request. */
        $campaign = Campaign::find($campaign->id);
        $campaign->status = CampaignStatus::ACTIVE();
        $campaign->save();

        $this->assertSame('publish', get_post_status($campaign->defaultFormId));
    }
}

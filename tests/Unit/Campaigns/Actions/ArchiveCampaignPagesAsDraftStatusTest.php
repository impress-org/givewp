<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Actions\ArchiveCampaignFormsAsDraftStatus;
use Give\Campaigns\Actions\ArchiveCampaignPagesAsDraftStatus;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignPageStatus;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.0.0
 */
final class ArchiveCampaignPagesAsDraftStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     */
    public function testPagesAreSetToDraft(): void
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();
        $page->status = CampaignPageStatus::PUBLISH();
        $page->save();

        $campaign->status = CampaignStatus::ARCHIVED();
        $campaign->save();
        $archiveCampaignFormsAsDraftStatus = new ArchiveCampaignPagesAsDraftStatus();
        $archiveCampaignFormsAsDraftStatus($campaign);

        $page = $campaign->page();

        $this->assertTrue($page->status->isDraft());
    }
}

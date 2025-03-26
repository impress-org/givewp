<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Actions\ArchiveCampaignFormsAsDraftStatus;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class ArchiveCampaignFormsAsDraftStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testFormsAreSetToDraft(): void
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);
        $form2 = DonationForm::factory()->create();
        give(CampaignRepository::class)->addCampaignForm($campaign, $form2->id);

        $this->assertEquals('publish', $form->status);

        $campaign->status = CampaignStatus::ARCHIVED();
        $campaign->save();
        $archiveCampaignFormsAsDraftStatus = new ArchiveCampaignFormsAsDraftStatus();
        $archiveCampaignFormsAsDraftStatus($campaign);

        $form = DonationForm::find($campaign->defaultFormId);
        $form2 = DonationForm::find($form2->id);

        $this->assertTrue($form->status->isDraft());
        $this->assertTrue($form2->status->isDraft());
    }
}

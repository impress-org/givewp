<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Actions\DuplicateCampaign;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
final class DuplicateCampaignTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testDuplicateMetadataDoesNotCreateAdditionalCampaignForms(): void
    {
        $campaign = Campaign::factory()->create();
        $metaKey = DonationFormMetaKeys::RECURRING_GOAL_FORMAT;

        give()->form_meta->update_meta($campaign->defaultFormId, $metaKey, 'donations');
        DB::table('give_formmeta')->insert([
            'form_id' => $campaign->defaultFormId,
            'meta_key' => $metaKey,
            'meta_value' => 'donations',
        ]);

        $duplicatedCampaign = (new DuplicateCampaign())($campaign);
        $duplicatedFormCount = DB::table('give_campaign_forms')
            ->where('campaign_id', $duplicatedCampaign->id)
            ->count();

        $this->assertSame(1, $duplicatedFormCount);
    }
}

<?php

namespace Give\Tests\Unit\Campaigns\Models;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\V2\ValueObjects\DonationFormMetaKeys;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.0.0
 */
final class CampaignModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     */
    public function testFindShouldReturnCampaign()
    {
        $mockCampaign = Campaign::factory()->create();
        $campaign = Campaign::find($mockCampaign->id);

        $this->assertInstanceOf(Campaign::class, $campaign);
    }

    /**
     * @since 4.0.0
     */
    public function testCampaignHasManyForms()
    {
        $campaign = Campaign::factory()->create();
        $form1 = DonationForm::factory()->create();
        $form2 = DonationForm::factory()->create();

        $db = DB::table('give_campaign_forms');
        $db->insert(['form_id' => $form1->id, 'campaign_id' => $campaign->id]);
        $db->insert(['form_id' => $form2->id, 'campaign_id' => $campaign->id]);

        $this->assertEquals(3, $campaign->forms()->count());
    }

    /**
     * @since TBD
     */
    public function testCampaignFormsReturnsEachFormOnceWithDuplicateMetadata()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::factory()->create();

        DB::table('give_campaign_forms')->insert([
            'form_id' => $form->id,
            'campaign_id' => $campaign->id,
        ]);

        /*
         * The second row carries a different value on purpose: DISTINCT would keep both, so only
         * grouping collapses them back into one form.
         */
        $metaKey = DonationFormMetaKeys::RECURRING_GOAL_FORMAT;
        give()->form_meta->update_meta($form->id, $metaKey, 'donations');
        DB::table('give_formmeta')->insert([
            'form_id' => $form->id,
            'meta_key' => $metaKey,
            'meta_value' => 'amount',
        ]);

        $formIds = array_map(function ($campaignForm) {
            return $campaignForm->id;
        }, $campaign->forms()->getAll());

        $this->assertEqualsCanonicalizing([$campaign->defaultFormId, $form->id], $formIds);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testCampaignHasDefaultForm()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();
        $newDefaultForm = DonationForm::factory()->create();
        give(CampaignRepository::class)->addCampaignForm($campaign, $newDefaultForm->id, true);

        $this->assertEquals($newDefaultForm->id, $campaign->defaultFormId);
    }
}

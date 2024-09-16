<?php

namespace Give\Tests\Unit\Campaigns\Models;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Database\DB;
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
        $this->assertEquals($mockCampaign->toArray(), $campaign->toArray());
    }

    /**
     * @unreleased
     */
    public function testCampaignHasManyForms()
    {
        $campaign = Campaign::factory()->create();
        $form1 = DonationForm::factory()->create();
        $form2 = DonationForm::factory()->create();

        $db = DB::table('give_campaign_forms');
        $db->insert(['form_id' => $form1->id, 'campaign_id' => $campaign->id]);
        $db->insert(['form_id' => $form2->id, 'campaign_id' => $campaign->id]);

        $this->assertEquals(2, $campaign->forms()->count());
    }

    /**
     * @unreleased
     */
    public function testCampaignHasDefaultForm()
    {
        $campaign = Campaign::factory()->create();
        $form1 = DonationForm::factory()->create();
        $form2 = DonationForm::factory()->create();

        $db = DB::table('give_campaign_forms');
        $db->insert(['form_id' => $form1->id, 'campaign_id' => $campaign->id, 'is_default' => 1]);
        $db->insert(['form_id' => $form2->id, 'campaign_id' => $campaign->id]);

        $this->assertEquals($form1->id, $campaign->form()->id);
    }
}
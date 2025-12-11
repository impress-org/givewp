<?php

namespace Give\Tests\Unit\Campaigns\Models;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Models\DonationForm;
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

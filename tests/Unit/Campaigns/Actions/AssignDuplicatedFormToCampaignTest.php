<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.0.0
 */
final class AssignDuplicatedFormToCampaignTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     */
    public function testDuplicatedFormIsAssignedToCampaign()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::find($campaign->defaultFormId);

        $db = DB::table('give_campaign_forms');

        // See give/src/DonationForms/V2/Endpoints/FormActions.php:131
        require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');
        $duplicatedFormID = \Give_Form_Duplicator::handler($form->id);
        $duplicatedFormCampaign = Campaign::findByFormId($duplicatedFormID);

        $this->assertEquals($campaign->id, $duplicatedFormCampaign->id);
    }

    /**
     * @since 4.19.0 Assert the copy stays standalone and nothing is logged.
     */
    public function testDuplicatingFormWithoutCampaignLeavesTheCopyStandalone()
    {
        $form = DonationForm::factory()->create();
        $logCount = DB::table('give_log')->count();

        // See give/src/DonationForms/V2/Endpoints/FormActions.php:131
        require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');
        $duplicatedFormID = \Give_Form_Duplicator::handler($form->id);

        $this->assertNull(Campaign::findByFormId($duplicatedFormID));
        $this->assertSame($logCount, DB::table('give_log')->count());
    }

    /**
     * @since TBD
     */
    public function testDuplicatingTheDefaultFormKeepsTheCampaignDefaultForm()
    {
        $campaign = Campaign::factory()->create();
        $defaultFormId = $campaign->defaultFormId;

        require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');
        \Give_Form_Duplicator::handler($defaultFormId);

        $this->assertSame($defaultFormId, Campaign::find($campaign->id)->defaultFormId);
    }

    /**
     * @since TBD
     */
    public function testDuplicatingAnotherCampaignFormKeepsTheCampaignDefaultForm()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::factory()->create();
        give(CampaignRepository::class)->addCampaignForm($campaign, $form->id);

        require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');
        $duplicatedFormID = \Give_Form_Duplicator::handler($form->id);

        $this->assertSame($campaign->id, Campaign::findByFormId($duplicatedFormID)->id);
        $this->assertSame($campaign->defaultFormId, Campaign::find($campaign->id)->defaultFormId);
    }
}

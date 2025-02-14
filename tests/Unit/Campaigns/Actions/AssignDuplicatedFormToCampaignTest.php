<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class AssignDuplicatedFormToCampaignTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testDuplicatedFormIsAssignedToCampaign()
    {
        $campaign = Campaign::factory()->create();
        $form = DonationForm::factory()->create();

        $db = DB::table('give_campaign_forms');
        $db->insert(['form_id' => $form->id, 'campaign_id' => $campaign->id]);

        // See give/src/DonationForms/V2/Endpoints/FormActions.php:131
        require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');
        $duplicatedFormID = \Give_Form_Duplicator::handler($form->id);
        $duplicatedFormCampaign = Campaign::findByFormId($duplicatedFormID);

        $this->assertEquals($campaign->id, $duplicatedFormCampaign->id);
    }

    public function testDuplicatingFormWithoutCampaignDoesNotCauseFatalError()
    {
        $form = DonationForm::factory()->create();

        // See give/src/DonationForms/V2/Endpoints/FormActions.php:131
        require_once(GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');
        \Give_Form_Duplicator::handler($form->id);

        // Prevent fatal error when duplicating form without campaign
        $this->assertTrue(true);
    }
}

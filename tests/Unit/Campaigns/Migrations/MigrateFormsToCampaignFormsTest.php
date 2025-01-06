<?php

namespace Give\Tests\Unit\Campaigns\Migrations;

use Exception;
use Give\Campaigns\Migrations\MigrateFormsToCampaignForms;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class MigrateFormsToCampaignFormsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testCreatesParentCampaignForDonationForm()
    {
        $form = DonationForm::factory()->create();
        $migration = new MigrateFormsToCampaignForms();

        $migration->run();

        $relationship = DB::table('give_campaign_forms')->where('form_id', $form->id)->get();

        $this->assertNotNull(Campaign::find($relationship->campaign_id));
        $this->assertEquals($form->id, $relationship->form_id);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testCreatesParentCampaignForOptionBasedDonationForm()
    {
        $formId = $this->factory()->post->create(
            [
                'post_title' => 'Test Form',
                'post_type' => 'give_forms',
                'post_status' => 'publish',
            ]
        );

        $migration = new MigrateFormsToCampaignForms();

        $migration->run();

        $relationship = DB::table('give_campaign_forms')->where('form_id', $formId)->get();

        $this->assertNotNull(Campaign::find($relationship->campaign_id));
        $this->assertEquals($formId, $relationship->form_id);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testExistingPeerToPeerCampaignFormsAreNotMigrated()
    {
        $form = DonationForm::factory()->create();
        DB::table('give_campaigns')->insert([
            'form_id' => $form->id,
        ]);

        $migration = new MigrateFormsToCampaignForms();
        $migration->run();

        $relationship = DB::table('give_campaign_forms')->where('form_id', $form->id)->get();

        $this->assertNull($relationship);
        $this->assertEquals(1, DB::table('give_campaigns')->count());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testUpgradedFormsAreNotMigrated()
    {
        $upgradedForm = DonationForm::factory()->create([
            'status' => DonationFormStatus::UPGRADED(),
        ]);

        $newForm = DonationForm::factory()->create([
            'status' => DonationFormStatus::PUBLISHED(),
        ]);

        Give()->form_meta->update_meta($newForm->id, 'migratedFormId', $upgradedForm->id);


        $migration = new MigrateFormsToCampaignForms();
        $migration->run();

        $campaign = Campaign::findByFormId($upgradedForm->id);

        $this->assertNotNull($campaign);
        $this->assertEquals(0, DB::table('give_campaigns')->where('form_id', $upgradedForm->id)->count());
        $this->assertEquals(1, DB::table('give_campaigns')->where('form_id', $newForm->id)->count());
        $this->assertEquals(2, DB::table('give_campaign_forms')->where('campaign_id', $campaign->id)->count());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testMigratedFormsAreDefault()
    {
        $form = DonationForm::factory()->create();

        $migration = new MigrateFormsToCampaignForms();
        $migration->run();

        $campaign = Campaign::findByFormId($form->id);

        $this->assertEquals($form->id, $campaign->defaultFormId);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testUpgradedFormsAreNotDefault()
    {
        $form1 = DonationForm::factory()->create([
            'status' => DonationFormStatus::UPGRADED(),
        ]);
        $form2 = DonationForm::factory()->create();
        give_update_meta($form2->id, 'migratedFormId', $form1->id);

        $migration = new MigrateFormsToCampaignForms();
        $migration->run();

        $campaign = Campaign::findByFormId($form2->id);

        $this->assertNotEquals($form1->id, $campaign->defaultFormId);
    }
}

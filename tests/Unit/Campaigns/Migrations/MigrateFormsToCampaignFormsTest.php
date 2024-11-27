<?php

namespace Give\Tests\Unit\Campaigns\Migrations;

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
     */
    public function testUpgradedFormsAreNotMigrated()
    {
        $form = DonationForm::factory()->create([
            'status' => DonationFormStatus::UPGRADED(),
        ]);

        $migration = new MigrateFormsToCampaignForms();
        $migration->run();

        $relationship = DB::table('give_campaign_forms')->where('form_id', $form->id)->get();

        $this->assertNull($relationship);
        $this->assertEquals(0, DB::table('give_campaigns')->count());
    }

    /**
     * @unreleased
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

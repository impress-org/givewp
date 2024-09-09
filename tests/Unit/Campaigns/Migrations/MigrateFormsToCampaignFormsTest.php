<?php

namespace Give\Tests\Unit\Campaigns\Migrations;

use Give\Campaigns\Migrations\MigrateFormsToCampaignForms;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Models\DonationForm;
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
}

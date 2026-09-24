<?php

namespace Give\Tests\Unit\Campaigns\Migrations;

use Give\Campaigns\Migrations\Tables\AddUniqueFormIdToCampaignFormsTable;
use Give\Campaigns\Models\Campaign;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
final class AddUniqueFormIdToCampaignFormsTableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testKeepsTheCampaignThatOwnsTheFormAsDefaultAndDropsTheOtherLinks(): void
    {
        $this->dropUniqueKey();

        $owner = Campaign::factory()->create();
        $other = Campaign::factory()->create();
        $form = DonationForm::find($owner->defaultFormId);

        DB::table('give_campaign_forms')->insert(['campaign_id' => $other->id, 'form_id' => $form->id]);

        (new AddUniqueFormIdToCampaignFormsTable())->run();

        $links = DB::table('give_campaign_forms')->where('form_id', $form->id)->getAll();

        $this->assertCount(1, $links);
        $this->assertEquals($owner->id, $links[0]->campaign_id);
        $this->assertTrue($this->formIdIsUnique());
    }

    /**
     * @since TBD
     */
    public function testKeepsTheLowestCampaignIdWhenNoCampaignOwnsTheFormAsDefault(): void
    {
        $this->dropUniqueKey();

        $first = Campaign::factory()->create();
        $second = Campaign::factory()->create();
        $form = DonationForm::factory()->create();

        DB::table('give_campaign_forms')->insert(['campaign_id' => $second->id, 'form_id' => $form->id]);
        DB::table('give_campaign_forms')->insert(['campaign_id' => $first->id, 'form_id' => $form->id]);

        (new AddUniqueFormIdToCampaignFormsTable())->run();

        $links = DB::table('give_campaign_forms')->where('form_id', $form->id)->getAll();

        $this->assertCount(1, $links);
        $this->assertEquals($first->id, $links[0]->campaign_id);
    }

    /**
     * @since TBD
     */
    public function testRepointsTheDefaultFormOfACampaignThatLostTheLink(): void
    {
        $this->dropUniqueKey();

        $winner = Campaign::factory()->create();
        $loser = Campaign::factory()->create();
        $shared = DonationForm::find($winner->defaultFormId);

        DB::table('give_campaign_forms')->insert(['campaign_id' => $loser->id, 'form_id' => $shared->id]);
        DB::table('give_campaigns')->where('id', $loser->id)->update(['form_id' => $shared->id]);

        (new AddUniqueFormIdToCampaignFormsTable())->run();

        $loserDefaultFormId = Campaign::find($loser->id)->defaultFormId;

        $this->assertEquals($winner->id, Campaign::findByFormId($shared->id)->id);
        $this->assertNotEquals($shared->id, $loserDefaultFormId);
        $this->assertEquals($loser->id, Campaign::findByFormId($loserDefaultFormId)->id);
    }

    /**
     * @since TBD
     */
    public function testRunningTwiceIsSafe(): void
    {
        (new AddUniqueFormIdToCampaignFormsTable())->run();
        (new AddUniqueFormIdToCampaignFormsTable())->run();

        $this->assertTrue($this->formIdIsUnique());
    }

    /**
     * Leave the table as the migration leaves it, so the next test bootstrap's dbDelta matches.
     */
    public function tearDown(): void
    {
        (new AddUniqueFormIdToCampaignFormsTable())->run();

        parent::tearDown();
    }

    private function dropUniqueKey(): void
    {
        global $wpdb;

        if ($this->formIdIsUnique()) {
            DB::query("ALTER TABLE {$wpdb->give_campaign_forms} DROP INDEX form_id, ADD KEY form_id (form_id)");
        }
    }

    private function formIdIsUnique(): bool
    {
        global $wpdb;

        return (bool)DB::get_var(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$wpdb->give_campaign_forms}'
            AND INDEX_NAME = 'form_id' AND NON_UNIQUE = 0"
        );
    }
}

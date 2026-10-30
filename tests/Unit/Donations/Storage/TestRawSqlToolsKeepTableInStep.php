<?php

namespace Give\Tests\Unit\Donations\Storage;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Migrations\CreateDonationsTable;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationTableRepository;
use Give\Donations\Storage\DonationStorageFlag;
use Give\FormMigration\Actions\TransferDonations;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use Give_Tools_Reset_Stats;

/**
 * Three admin tools write donation storage with raw SQL and bypass both the repository and the
 * meta hooks. Each must apply the same change to give_donations itself.
 *
 * @since TBD
 */
class TestRawSqlToolsKeepTableInStep extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();
        delete_option(DonationStorageFlag::OPTION);
        (new CreateDonationsTable())->run();
    }

    /**
     * @since TBD
     */
    public function testCampaignMergeMovesRows()
    {
        $campaign = Campaign::factory()->create();
        $destination = Campaign::factory()->create();
        $donation = Donation::factory()->create([
            'campaignId' => $campaign->id,
            'formId' => $campaign->defaultForm()->id,
        ]);

        $this->assertSame($campaign->id, (int)$this->row($donation->id)->campaign_id);

        (new CampaignRepository())->mergeCampaigns($destination, $campaign);

        $this->assertSame($destination->id, (int)$this->row($donation->id)->campaign_id);
    }

    /**
     * @since TBD
     */
    public function testFormUpgradeMovesRows()
    {
        $formV2 = $this->createSimpleDonationForm();
        $formV3 = DonationForm::factory()->create();
        $donation = Donation::factory()->create(['formId' => $formV2->id]);

        $this->assertSame($formV2->id, (int)$this->row($donation->id)->form_id);

        TransferDonations::from($formV2->id)->to($formV3->id);

        $this->assertSame($formV3->id, (int)$this->row($donation->id)->form_id);
    }

    /**
     * @since TBD
     */
    public function testResetStatsEmptiesTheTable()
    {
        Donation::factory()->count(3)->create();
        $this->assertSame(3, $this->rowCount());

        $this->runResetStats();

        $this->assertSame(0, $this->rowCount());
    }

    /**
     * @since TBD
     */
    public function testResetStatsKeepsTheStorageFlag()
    {
        Donation::factory()->create();

        $this->runResetStats();

        $this->assertTrue(DonationStorageFlag::get()->isSyncing());
    }

    /**
     * The next run finds its IDs from the surviving posts, so a stopped run must never leave table
     * rows behind for posts that are already gone.
     *
     * @since TBD
     */
    public function testResetStatsDeletesTableRowsBeforePosts()
    {
        global $wpdb;

        Donation::factory()->create();

        $queries = [];
        $capture = static function ($query) use (&$queries) {
            $queries[] = $query;

            return $query;
        };
        add_filter('query', $capture);
        $this->runResetStats();
        remove_filter('query', $capture);

        $positionOf = static function (string $prefix) use ($queries) {
            foreach ($queries as $position => $query) {
                if (strpos($query, $prefix) === 0) {
                    return $position;
                }
            }

            return null;
        };

        $tablePosition = $positionOf('DELETE FROM ' . DB::prefix(DonationTableRepository::TABLE) . ' WHERE');
        $postsPosition = $positionOf("DELETE FROM {$wpdb->posts} WHERE");

        $this->assertNotNull($tablePosition);
        $this->assertNotNull($postsPosition);
        $this->assertLessThan($postsPosition, $tablePosition);
    }

    /**
     * While the flag is posts the table may not exist, so none of the three tools may touch it.
     *
     * @since TBD
     */
    public function testToolsLeaveTheTableAloneWhileTheFlagIsPosts()
    {
        $campaign = Campaign::factory()->create();
        $destination = Campaign::factory()->create();
        $formV2 = $this->createSimpleDonationForm();
        $formV3 = DonationForm::factory()->create();
        $donation = Donation::factory()->create(['campaignId' => $campaign->id, 'formId' => $formV2->id]);

        delete_option(DonationStorageFlag::OPTION);

        (new CampaignRepository())->mergeCampaigns($destination, $campaign);
        TransferDonations::from($formV2->id)->to($formV3->id);

        $row = $this->row($donation->id);
        $this->assertSame($campaign->id, (int)$row->campaign_id);
        $this->assertSame($formV2->id, (int)$row->form_id);

        $this->runResetStats();

        $this->assertSame(1, $this->rowCount());
    }

    /**
     * @since TBD
     */
    private function runResetStats(): void
    {
        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/export/class-batch-export.php';
        require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-reset-stats.php';

        $tool = new Give_Tools_Reset_Stats();
        $tool->pre_fetch();
        $tool->get_data();
    }

    /**
     * @since TBD
     */
    private function row(int $donationId)
    {
        return DB::get_row(DB::prepare('SELECT * FROM ' . DB::prefix(DonationTableRepository::TABLE) . ' WHERE id = %d', $donationId));
    }

    /**
     * @since TBD
     */
    private function rowCount(): int
    {
        return (int)DB::get_var('SELECT COUNT(*) FROM ' . DB::prefix(DonationTableRepository::TABLE));
    }
}

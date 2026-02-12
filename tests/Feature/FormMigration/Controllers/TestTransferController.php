<?php

namespace Give\Tests\Feature\FormMigration\Controllers;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignRepository;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\FormMigration\Actions\GetMigratedFormId;
use Give\FormMigration\Controllers\MigrationController;
use Give\FormMigration\Controllers\TransferController;
use Give\FormMigration\DataTransferObjects\TransferOptions;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 *
 * @covers \Give\FormMigration\Controllers\TransferController
 */
class TestTransferController extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testShouldTransferCoreCampaignForm(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        // Create a core campaign and associate the v2 form as default
        $campaign = Campaign::factory()->create();
        give(CampaignRepository::class)->addCampaignForm($campaign, $formV2->id, true);

        // Migrate v2 to v3
        $migrationRequest = $this->getMockRequest(WP_REST_Server::CREATABLE);
        $migrationController = new MigrationController($migrationRequest);
        $migrationController($formV2);

        $v3FormId = (int)(new GetMigratedFormId)($formV2->id);
        $this->assertGreaterThan(0, $v3FormId, 'v3 form should have been created by migration');

        // Transfer
        $transferRequest = $this->getMockRequest(WP_REST_Server::CREATABLE);
        $transferOptions = new TransferOptions(false);

        $controller = new TransferController($transferRequest);
        $response = $controller($formV2, $transferOptions);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        // The campaign default form should now be the v3 form
        $updatedCampaign = Campaign::find($campaign->id);
        $this->assertSame($v3FormId, $updatedCampaign->defaultFormId);
    }

    /**
     * @unreleased
     */
    public function testShouldTransferNonCoreCampaignForm(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        // Create a non-core campaign linked via give_campaigns.form_id (e.g., P2P pattern)
        $campaignId = $this->createNonCoreCampaign($formV2->id);

        // Migrate v2 to v3
        $migrationRequest = $this->getMockRequest(WP_REST_Server::CREATABLE);
        $migrationController = new MigrationController($migrationRequest);
        $migrationController($formV2);

        $v3FormId = (int)(new GetMigratedFormId)($formV2->id);
        $this->assertGreaterThan(0, $v3FormId, 'v3 form should have been created by migration');

        // Verify v3 form was added to the junction table during migration
        $junctionEntry = DB::table('give_campaign_forms')
            ->where('form_id', $v3FormId)
            ->where('campaign_id', $campaignId)
            ->get();
        $this->assertNotNull($junctionEntry, 'v3 form should be in the junction table after migration');

        // Transfer
        $transferRequest = $this->getMockRequest(WP_REST_Server::CREATABLE);
        $transferOptions = new TransferOptions(false);

        $controller = new TransferController($transferRequest);
        $response = $controller($formV2, $transferOptions);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        // The campaign form_id should now point to the v3 form
        $updatedCampaign = DB::table('give_campaigns')
            ->where('id', $campaignId)
            ->get();
        $this->assertSame($v3FormId, (int)$updatedCampaign->form_id);
    }

    /**
     * Creates a non-core campaign row directly in give_campaigns (simulating how P2P inserts campaigns).
     *
     * @unreleased
     */
    private function createNonCoreCampaign(int $formId): int
    {
        DB::table('give_campaigns')
            ->insert([
                'form_id' => $formId,
                'campaign_type' => '',
                'campaign_title' => 'Test Non-Core Campaign',
                'status' => 'active',
                'campaign_goal' => 10000,
                'goal_type' => 'amount',
                'date_created' => current_time('mysql'),
                'start_date' => current_time('mysql'),
            ]);

        return DB::last_insert_id();
    }

    /**
     * @unreleased
     */
    private function getMockRequest(string $method): WP_REST_Request
    {
        return new WP_REST_Request(
            $method,
            '/wp/v2/admin/forms/transfer'
        );
    }
}

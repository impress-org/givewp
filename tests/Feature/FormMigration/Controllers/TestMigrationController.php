<?php

namespace Give\Tests\Feature\FormMigration\Controllers;

use Give\FormMigration\Actions\GetMigratedFormId;
use Give\FormMigration\Controllers\MigrationController;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;


/**
 * @since 3.4.0
 *
 * @covers \Give\FormMigration\Controllers\MigrationController
 */
class TestMigrationController extends TestCase
{
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @since 3.4.0
     */
    public function testShouldMigrateFormV2ToV3(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        $this->createCampaignForDonationForm($formV2->id);

        $request = $this->getMockRequest(WP_REST_Server::CREATABLE);

        $controller = new MigrationController($request);

        $response = $controller($formV2);

        $formV3Id = (int)(new GetMigratedFormId)($formV2->id);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        $this->assertSame($response->data, [
            'v2FormId' => $formV2->id,
            'v3FormId' => $formV3Id,
             'redirect' => add_query_arg([
                'post_type' => 'give_forms',
                'page' => 'givewp-form-builder',
                'donationFormID' => $formV3Id,
            ], admin_url('edit.php'))
        ]);
    }

    /**
     * @unreleased
     */
    public function testShouldAssociateV3FormWithNonCoreCampaign(): void
    {
        $formV2 = $this->createSimpleDonationForm();

        // Create a non-core campaign linked via give_campaigns.form_id (e.g., P2P pattern)
        DB::table('give_campaigns')
            ->insert([
                'form_id' => $formV2->id,
                'campaign_type' => '',
                'campaign_title' => 'Test Non-Core Campaign',
                'status' => 'active',
                'campaign_goal' => 10000,
                'goal_type' => 'amount',
                'date_created' => current_time('mysql'),
                'start_date' => current_time('mysql'),
            ]);
        $campaignId = DB::last_insert_id();

        $request = $this->getMockRequest(WP_REST_Server::CREATABLE);
        $controller = new MigrationController($request);
        $response = $controller($formV2);

        $formV3Id = (int)(new GetMigratedFormId)($formV2->id);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        // The v3 form should be added to the junction table for the non-core campaign
        $junctionEntry = DB::table('give_campaign_forms')
            ->where('form_id', $formV3Id)
            ->where('campaign_id', $campaignId)
            ->get();
        $this->assertNotNull($junctionEntry, 'v3 form should be in the junction table for the non-core campaign');

        // The original campaign form_id should still point to the v2 form (updated during transfer, not migration)
        $campaignData = DB::table('give_campaigns')
            ->where('id', $campaignId)
            ->get();
        $this->assertSame($formV2->id, (int)$campaignData->form_id);
    }

    /**
     *
     * @since 3.4.0
     */
    public function getMockRequest(string $method): WP_REST_Request
    {
        return new WP_REST_Request(
            $method,
            '/wp/v2/' . 'admin/forms/migrate/(?P<id>\d+)'
        );
    }
}

<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Campaigns;

use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * Tests for SVUL-74: Consistent campaign status check in CampaignCommentsController::get_items().
 *
 * CampaignController::get_item() already blocks non-admins from accessing inactive (draft/archived)
 * campaigns. This test suite verifies that CampaignCommentsController::get_items() applies the same
 * status check so comments on inactive campaigns are consistently protected.
 *
 * @unreleased
 */
final class CampaignCommentsAccessControlTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    private function buildRoute(int $campaignId): string
    {
        return '/' . CampaignRoute::NAMESPACE . '/campaigns/' . $campaignId . '/comments';
    }

    // --- Inactive campaigns are blocked for non-admins ---

    /**
     * @unreleased
     */
    public function testUnauthenticatedUserCannotViewCommentsOnDraftCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id));
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testUnauthenticatedUserCannotViewCommentsOnArchivedCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id));
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testAuthenticatedNonAdminCannotViewCommentsOnDraftCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id), [], 'subscriber');
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * @unreleased
     */
    public function testAuthenticatedNonAdminCannotViewCommentsOnArchivedCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id), [], 'subscriber');
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    // --- Admins bypass the status check ---

    /**
     * @unreleased
     */
    public function testAdminCanViewCommentsOnDraftCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id), [], 'administrator');
        $request->set_param('id', $campaign->id);

        $this->assertEquals(200, $this->dispatchRequest($request)->get_status());
    }

    /**
     * @unreleased
     */
    public function testAdminCanViewCommentsOnArchivedCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id), [], 'administrator');
        $request->set_param('id', $campaign->id);

        $this->assertEquals(200, $this->dispatchRequest($request)->get_status());
    }


    /**
     * @unreleased
     */
    public function testAnyoneCanViewCommentsOnActiveCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id));
        $request->set_param('id', $campaign->id);

        $this->assertEquals(200, $this->dispatchRequest($request)->get_status());
    }
}

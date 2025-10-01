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
 * @unreleased
 */
class CampaignRouteGetItemsTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that unauthenticated users cannot access non-active campaigns via GET /campaigns.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserCannotAccessNonActiveCampaigns()
    {
        // Create campaigns with different statuses
        $activeCampaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $draftCampaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);
        $archivedCampaign = Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['draft', 'archived']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }

    /**
     * Test that unauthenticated users can access active campaigns via GET /campaigns.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserCanAccessActiveCampaigns()
    {
        $activeCampaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['active']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that unauthenticated users can access campaigns without status filter via GET /campaigns.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserCanAccessCampaignsWithoutStatusFilter()
    {
        $activeCampaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that admin users can access all campaign statuses via GET /campaigns.
     *
     * @unreleased
     */
    public function testAdminUserCanAccessAllCampaignStatuses()
    {
        $activeCampaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $draftCampaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);
        $archivedCampaign = Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(['status' => ['active', 'draft', 'archived']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that mixed status requests are blocked for unauthenticated users.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserCannotAccessMixedStatusRequests()
    {
        $activeCampaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $draftCampaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['active', 'draft']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }
}

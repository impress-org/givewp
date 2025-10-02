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
class CampaignRouteGetItemTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that unauthenticated users cannot access individual non-active campaign via GET /campaigns/{id}.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserCannotAccessNonActiveCampaign()
    {
        $draftCampaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $draftCampaign->id, CampaignRoute::CAMPAIGN);
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }

    /**
     * Test that unauthenticated users can access individual active campaign via GET /campaigns/{id}.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserCanAccessActiveCampaign()
    {
        $activeCampaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $activeCampaign->id, CampaignRoute::CAMPAIGN);
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that admin users can access individual non-active campaign via GET /campaigns/{id}.
     *
     * @unreleased
     */
    public function testAdminUserCanAccessNonActiveCampaign()
    {
        $draftCampaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $draftCampaign->id, CampaignRoute::CAMPAIGN);
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that admin users can access individual archived campaign via GET /campaigns/{id}.
     *
     * @unreleased
     */
    public function testAdminUserCanAccessArchivedCampaign()
    {
        $archivedCampaign = Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $archivedCampaign->id, CampaignRoute::CAMPAIGN);
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that unauthenticated users get 404 for non-existent campaign.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserGets404ForNonExistentCampaign()
    {
        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', '999', CampaignRoute::CAMPAIGN);
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(404, $response->get_status());
    }
}

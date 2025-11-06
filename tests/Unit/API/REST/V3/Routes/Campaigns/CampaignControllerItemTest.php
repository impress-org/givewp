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
 * @unreleased item endpoint tests for campaigns
 */
class CampaignControllerItemTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that admin users can create a campaign via POST /campaigns.
     *
     * @unreleased
     */
    public function testCreateCampaignShouldCreateModelWithValidData()
    {
        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'title' => 'Save the Forest',
            'goal' => 10000,
            'goalType' => 'amount',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(201, $status);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals('Save the Forest', $data['title']);
        $this->assertEquals(10000, $data['goal']);
        $this->assertEquals('amount', $data['goalType']);
        $this->assertEquals('active', $data['status']);

        // Verify persistence in database
        $createdCampaign = Campaign::find($data['id']);
        $this->assertNotNull($createdCampaign);
        $this->assertEquals('Save the Forest', $createdCampaign->title);
        $this->assertEquals(10000, $createdCampaign->goal);
        $this->assertEquals('amount', $createdCampaign->goalType->getValue());
        $this->assertEquals('active', $createdCampaign->status->getValue());
    }

    /**
     * Test that creating a campaign validates required fields.
     *
     * @unreleased
     */
    public function testCreateCampaignShouldReturn400ErrorWhenRequiredFieldsMissing()
    {
        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'title' => 'Missing Required Fields',
            // Missing: goal, goalType
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(400, $status);
        $this->assertEquals('rest_missing_callback_param', $data['code']);
        $this->assertStringContainsString('Missing parameter(s):', $data['message']);
        $this->assertStringContainsString('goal', $data['message']);
        $this->assertStringContainsString('goalType', $data['message']);
    }

    /**
     * Test that creating a campaign with invalid params returns 400.
     *
     * @unreleased
     */
    public function testCreateCampaignShouldReturn400ErrorForInvalidParameters()
    {
        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest('POST', $route, [], 'administrator');
        $request->set_body_params([
            'title' => 'Invalid Goal Type',
            'goal' => 1000,
            'goalType' => 'invalid_type',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(400, $status);
        $this->assertEquals('rest_invalid_param', $data['code']);
        $this->assertStringContainsString('Invalid parameter(s): goalType', $data['message']);
    }

    /**
     * Test that non-admin users cannot create a campaign.
     *
     * @unreleased
     */
    public function testCreateCampaignShouldReturn403ErrorWhenNotAdminUser()
    {
        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest('POST', $route, [], 'subscriber');
        $request->set_body_params([
            'title' => 'Unauthorized Create',
            'goal' => 1000,
            'goalType' => 'amount',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * Test that admin users can update a campaign via PUT /campaigns/{id}.
     *
     * @unreleased
     */
    public function testUpdateCampaignShouldUpdateModelProperties()
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $campaign->id, CampaignRoute::CAMPAIGN);
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'status' => 'archived',
            'title' => 'Updated Campaign Title',
            'shortDescription' => 'Updated short description',
            'goal' => 20000,
            'goalType' => 'donations',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);
        $this->assertEquals('archived', $data['status']);
        $this->assertEquals('Updated Campaign Title', $data['title']);
        $this->assertEquals('Updated short description', $data['shortDescription']);
        $this->assertEquals(20000, $data['goal']);
        $this->assertEquals('donations', $data['goalType']);

        // Verify persistence in database
        $updatedCampaign = Campaign::find($campaign->id);
        $this->assertEquals('archived', $updatedCampaign->status->getValue());
        $this->assertEquals('Updated Campaign Title', $updatedCampaign->title);
        $this->assertEquals('Updated short description', $updatedCampaign->shortDescription);
        $this->assertEquals(20000, $updatedCampaign->goal);
        $this->assertEquals('donations', $updatedCampaign->goalType->getValue());
    }

    /**
     * Test that updating a non-existent campaign returns 404.
     *
     * @unreleased
     */
    public function testUpdateCampaignShouldReturn404ErrorWhenCampaignNotFound()
    {
        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', '999', CampaignRoute::CAMPAIGN);
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'title' => 'Does not matter',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }

    /**
     * Test that non-admin users cannot update a campaign.
     *
     * @unreleased
     */
    public function testUpdateCampaignShouldReturn403ErrorWhenNotAdminUser()
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $campaign->id, CampaignRoute::CAMPAIGN);
        $request = $this->createRequest('PUT', $route, [], 'subscriber');
        $request->set_body_params([
            'title' => 'Unauthorized Update',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * Test that unauthenticated users cannot access individual non-active campaign via GET /campaigns/{id}.
     *
     * @since 4.10.1
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
     * @since 4.10.1
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
     * @since 4.10.1
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
     * @since 4.10.1
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
     * @since 4.10.1
     */
    public function testUnauthenticatedUserGets404ForNonExistentCampaign()
    {
        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', '999', CampaignRoute::CAMPAIGN);
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(404, $response->get_status());
    }

    /**
     * Test that DELETE is not supported for campaigns and returns 404 (no route).
     *
     * @unreleased
     */
    public function testDeleteCampaignShouldReturn404WhenRouteNotFound()
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $campaign->id, CampaignRoute::CAMPAIGN);
        $request = $this->createRequest('DELETE', $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $this->assertEquals(404, $response->get_status());
    }
}



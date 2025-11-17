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
 * @unreleased collection endpoint tests for campaigns
 */
class CampaignControllerCollectionTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Ensure collection returns all expected view model properties.
     *
     * @unreleased
     */
    public function testGetCampaignsShouldReturnAllViewModelProperties()
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(['status' => ['active']]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        // Remove additional property added by prepare_response_for_collection()
        // and any links if present in transformed data
        if (isset($data[0]['_links'])) {
            unset($data[0]['_links']);
        }

        $this->assertEquals(200, $status);
        $this->assertNotEmpty($data);

        // Some fields (dates, pagePermalink) are formatted during response preparation; use response values for those
        $expected = [
            'id' => $campaign->id,
            'pageId' => $data[0]['pageId'], // depends on page creation hook
            'pagePermalink' => $data[0]['pagePermalink'], // derived from pageId
            'defaultFormId' => $campaign->defaultFormId,
            'defaultFormTitle' => $campaign->defaultForm()->title,
            'type' => $campaign->type->getValue(),
            'title' => $campaign->title,
            'shortDescription' => $campaign->shortDescription,
            'longDescription' => $campaign->longDescription,
            'logo' => $campaign->logo,
            'image' => $campaign->image,
            'primaryColor' => $campaign->primaryColor,
            'secondaryColor' => $campaign->secondaryColor,
            'goal' => $campaign->goal,
            'goalType' => $campaign->goalType->getValue(),
            'goalStats' => $campaign->getGoalStats(),
            'status' => $campaign->status->getValue(),
            'startDate' => $data[0]['startDate'],
            'endDate' => $data[0]['endDate'],
            'createdAt' => $data[0]['createdAt'],
        ];

        $this->assertEquals($expected, $data[0]);
    }

    /**
     * Ensure collection items include self links like other resources.
     *
     * @unreleased
     */
    public function testGetCampaignsShouldReturnSelfLink()
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['active']]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        // The $response->get_data() method does not include _links data
        $data = $this->responseToData($response, true);

        $this->assertEquals(200, $status);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('_links', $data[0]);
        $this->assertArrayHasKey('self', $data[0]['_links']);
        $this->assertEquals($campaign->id, $data[0]['id']);
    }

    /**
     * Verify pagination headers and page results.
     *
     * @unreleased
     */
    public function testGetCampaignsWithPagination()
    {
        $campaign1 = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $campaign2 = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        // Page 1
        $request->set_query_params([
            'status' => ['active'],
            'page' => 1,
            'per_page' => 1,
            'sortBy' => 'date',
            'orderBy' => 'asc',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);
        $headers = $response->get_headers();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($campaign1->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);

        // Page 2
        $request->set_query_params([
            'status' => ['active'],
            'page' => 2,
            'per_page' => 1,
            'sortBy' => 'date',
            'orderBy' => 'asc',
        ]);
        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);
        $headers = $response->get_headers();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($campaign2->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);
    }

    /**
     * Test that unauthenticated users cannot access non-active campaigns via GET /campaigns.
     *
     * @since 4.10.1
     */
    public function testUnauthenticatedUserCannotAccessNonActiveCampaigns()
    {
        Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);
        Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['draft', 'archived']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }

    /**
     * Test that unauthenticated users can access active campaigns via GET /campaigns.
     *
     * @since 4.10.1
     */
    public function testUnauthenticatedUserCanAccessActiveCampaigns()
    {
        Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['active']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that unauthenticated users can access campaigns without status filter via GET /campaigns.
     *
     * @since 4.10.1
     */
    public function testUnauthenticatedUserCanAccessCampaignsWithoutStatusFilter()
    {
        Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that admin users can access all campaign statuses via GET /campaigns.
     *
     * @since 4.10.1
     */
    public function testAdminUserCanAccessAllCampaignStatuses()
    {
        Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);
        Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(['status' => ['active', 'draft', 'archived']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that mixed status requests are blocked for unauthenticated users.
     *
     * @since 4.10.1
     */
    public function testUnauthenticatedUserCannotAccessMixedStatusRequests()
    {
        Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGNS;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['active', 'draft']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }
}



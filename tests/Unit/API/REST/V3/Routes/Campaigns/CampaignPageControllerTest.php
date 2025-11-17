<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Campaigns;

use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignPageStatus;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased tests for campaign page subresource
 */
class CampaignPageControllerTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @unreleased
     */
    public function testCreateCampaignPageShouldReturn201AndCampaignPageResource()
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE(), 'pageId' => 1]);

        $campaign->pageId = null;
        $campaign->save();

        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $campaign->id, CampaignRoute::CAMPAIGN) . '/page';
        $request = $this->createRequest('POST', $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $campaign = Campaign::find($campaign->id);
        $campaignPage = $campaign->page();

        $this->assertEquals(201, $status);
        $this->assertNotNull($campaignPage);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($campaignPage->id, $data['id']);
        $this->assertArrayHasKey('campaignId', $data);
        $this->assertEquals($campaign->id, $data['campaignId']);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals(CampaignPageStatus::DRAFT()->getValue(), $data['status']);
    }

    /**
     * @unreleased
     */
    public function testCreateCampaignPageShouldReturn404WhenCampaignNotFound()
    {
        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', '999999', CampaignRoute::CAMPAIGN) . '/page';
        $request = $this->createRequest('POST', $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        $this->assertEquals(404, $status);
        $this->assertEquals('campaign_not_found', $data['code']);
    }

    /**
     * @unreleased
     */
    public function testCreateCampaignPageShouldReturn403WhenNotAdminUser()
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $route = '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $campaign->id, CampaignRoute::CAMPAIGN) . '/page';
        $request = $this->createRequest('POST', $route, [], 'subscriber');

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }
}



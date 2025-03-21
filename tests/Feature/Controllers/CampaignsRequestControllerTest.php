<?php

namespace Give\Tests\Feature\Controllers;

use Exception;
use Give\Campaigns\Controllers\CampaignRequestController;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\ViewModels\CampaignViewModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class CampaignsRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testShowShouldReturnCampaignData()
    {
        $campaign = Campaign::factory()->create();
        $campaignViewModel = new CampaignViewModel($campaign);

        $request = $this->getMockRequest(WP_REST_Server::READABLE);
        $request->set_param('id', $campaign->id);

        $response = (new CampaignRequestController())->getCampaign($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertSame(
            $response->data,
            $campaignViewModel->exports()
        );
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testUpdateShouldReturnUpdatedCampaignData()
    {
        $campaign = Campaign::factory()->create();
        $campaign->title = 'Updated Campaign Title';

        $campaignViewModel = new CampaignViewModel($campaign);

        $request = $this->getMockRequest(WP_REST_Server::CREATABLE);
        $request->set_param('id', $campaign->id);
        $request->set_param('title', $campaign->title);

        $response = (new CampaignRequestController())->updateCampaign($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertSame(
            $response->data,
            $campaignViewModel->exports()
        );
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testUpdateShouldFailIfCampaignDoesNotExist()
    {
        $request = $this->getMockRequest(WP_REST_Server::CREATABLE);
        $request->set_param('id', 99);

        $response = (new CampaignRequestController())->updateCampaign($request);

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertSame('campaign_not_found', $response->get_error_code());
    }

    /**
     * @since 4.0.0
     */
    public function testShowShouldFailIfCampaignDoesNotExist()
    {
        $request = $this->getMockRequest(WP_REST_Server::READABLE);
        $request->set_param('id', 99);

        $response = (new CampaignRequestController())->getCampaign($request);

        $this->assertInstanceOf(WP_Error::class, $response);
        $this->assertSame('campaign_not_found', $response->get_error_code());
    }


    /**
     *
     * @since 4.0.0
     */
    public function getMockRequest(string $method): WP_REST_Request
    {
        return new WP_REST_Request(
            $method,
            CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGN
        );
    }
}


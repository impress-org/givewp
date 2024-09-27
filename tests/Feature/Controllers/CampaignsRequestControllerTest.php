<?php

namespace Give\Tests\Feature\Controllers;

use Exception;
use Give\Campaigns\Controllers\CampaignRequestController;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignRoute;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class CampaignsRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShowShouldReturnCampaignData()
    {
        $campaign = Campaign::factory()->create();

        $request = $this->getMockRequest(WP_REST_Server::READABLE);
        $request->set_param('id', $campaign->id);

        $response = (new CampaignRequestController())->getCampaign($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertSame(
            $this->campaignViewModelFromArray($response->data),
            $this->campaignViewModelFromArray($campaign->toArray())
        );
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testUpdateShouldReturnUpdatedCampaignData()
    {
        $campaign = Campaign::factory()->create();
        $campaign->title = 'Updated Campaign Title';

        $request = $this->getMockRequest(WP_REST_Server::CREATABLE);
        $request->set_param('id', $campaign->id);
        $request->set_param('title', $campaign->title);

        $response = (new CampaignRequestController())->updateCampaign($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertSame(
            $this->campaignViewModelFromArray($response->data),
            $this->campaignViewModelFromArray($campaign->toArray())
        );
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     */
    public function getMockRequest(string $method): WP_REST_Request
    {
        return new WP_REST_Request(
            $method,
            CampaignRoute::NAMESPACE . '/' . CampaignRoute::CAMPAIGN
        );
    }


    /**
     * @unreleased
     */
    public function campaignViewModelFromArray(array $data): array
    {
        return [
            'id' => $data['id'],
            'type' => $data['type']->getValue(),
            'title' => $data['title'],
            'shortDescription' => $data['shortDescription'],
            'longDescription' => $data['longDescription'],
            'logo' => $data['logo'],
            'image' => $data['image'],
            'primaryColor' => $data['primaryColor'],
            'secondaryColor' => $data['secondaryColor'],
            'goal' => (int)$data['goal'],
            'goalType' => $data['goalType'],
            'status' => $data['status']->getValue(),
            'startDate' => Temporal::getFormattedDateTime($data['startDate']),
            'endDate' => Temporal::getFormattedDateTime($data['endDate']),
            'createdAt' => Temporal::getFormattedDateTime($data['createdAt']),
        ];
    }
}


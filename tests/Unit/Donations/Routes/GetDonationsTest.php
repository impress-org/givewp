<?php

namespace Unit\Donations\Routes;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationRoute;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetDonationsTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsByCampaignId()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var  Donation $donation */
        $donation = Donation::factory()->create();
        give()->payment_meta->update_meta($donation->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::DONATIONS;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'campaignId' => $campaign->id,
            ]
        );

        $response = $this->dispatchRequest($request);
        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donation->id, $data[0]->id);
    }
}

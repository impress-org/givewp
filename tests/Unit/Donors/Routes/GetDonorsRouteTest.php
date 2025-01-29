<?php

namespace Unit\Donors\Routes;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\ValueObjects\DonorRoute;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetDonorsRouteTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorsByCampaignId()
    {
        //Donor::query()->delete();

        Donation::query()->delete();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);
        $donor1 = $donation1->donor;
        give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);
        give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::DONOR_ID, $donor1->id);

        /** @var  Donation $donation2 */
        $donation2 = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);
        $donor2 = $donation2->donor;
        give()->payment_meta->update_meta($donation2->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);
        give()->payment_meta->update_meta($donation2->id, DonationMetaKeys::DONOR_ID, $donor2->id);

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
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
        $this->assertEquals(2, count($data));
        $this->assertEquals($donor1->id, $data[0]->id);
        $this->assertEquals($donor2->id, $data[1]->id);
    }
}

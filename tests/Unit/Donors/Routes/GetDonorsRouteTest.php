<?php

namespace Unit\Donors\Routes;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorRoute;
use Give\Framework\Database\DB;
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
    public function testGetDonorsWithPagination()
    {
        DB::query("DELETE FROM " . DB::prefix('give_donors'));

        /** @var  Donor $donor1 */
        $donor1 = Donor::factory()->create();

        /** @var  Donor $donor2 */
        $donor2 = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'onlyWithDonations' => false,
                'page' => 1,
                'per_page' => 1,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $headers = $response->get_headers();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($donor1->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);
        $request->set_query_params(
            [
                'onlyWithDonations' => false,
                'page' => 2,
                'per_page' => 1,
            ]
        );
        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $headers = $response->get_headers();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($donor2->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorsByCampaignId()
    {
        Donation::query()->delete();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);
        $donor1 = $donation1->donor;
        give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);
        give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::DONOR_ID, $donor1->id);

        /** @var  Donation $donation2 */
        $donation2 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);
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
        $this->assertEquals($donor1->id, $data[0]['id']);
        $this->assertEquals($donor2->id, $data[1]['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorsShouldNotReturnSensitiveData()
    {
        Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'onlyWithDonations' => false,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveProperties = [
            'userId',
            'email',
            'phone',
            'additionalEmails',
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data[0], $sensitiveProperties));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorsShouldNotReturnAnonymousDonors()
    {
        Donation::query()->delete();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);
        $donor = $donation->donor;
        give()->payment_meta->update_meta($donation->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);
        give()->payment_meta->update_meta($donation->id, DonationMetaKeys::DONOR_ID, $donor->id);

        /** @var  Donation $anonymousDonation */
        $anonymousDonation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);
        $anonymousDonor = $anonymousDonation->donor; // This anonymous donor should NOT be returned to the data array.
        give()->payment_meta->update_meta($anonymousDonation->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);
        give()->payment_meta->update_meta($anonymousDonation->id, DonationMetaKeys::DONOR_ID, $anonymousDonor->id);

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($donor->id, $data[0]['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorsShouldReturnAnonymousDonors()
    {
        Donation::query()->delete();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);
        $donor = $donation->donor;
        give()->payment_meta->update_meta($donation->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);
        give()->payment_meta->update_meta($donation->id, DonationMetaKeys::DONOR_ID, $donor->id);

        /** @var  Donation $anonymousDonation */
        $anonymousDonation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);
        $anonymousDonor = $anonymousDonation->donor; // This anonymous donor should be returned to the data array.
        give()->payment_meta->update_meta($anonymousDonation->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);
        give()->payment_meta->update_meta($anonymousDonation->id, DonationMetaKeys::DONOR_ID, $anonymousDonor->id);

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'hideAnonymousDonors' => false,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(2, count($data));
        $this->assertEquals($donor->id, $data[0]['id']);
        $this->assertEquals($anonymousDonor->id, $data[1]['id']);
    }
}

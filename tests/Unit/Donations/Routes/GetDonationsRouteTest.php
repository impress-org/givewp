<?php

namespace Unit\Donations\Routes;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationRoute;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetDonationsRouteTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsWithPagination()
    {
        Donation::query()->delete();

        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        /** @var  Donation $donation2 */
        $donation2 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
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
        $this->assertEquals($donation1->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);

        $request->set_query_params(
            [
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
        $this->assertEquals($donation2->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsByCampaignId()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);
        give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);

        /** @var  Donation $donation2 */
        $donation2 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);
        give()->payment_meta->update_meta($donation2->id, DonationMetaKeys::CAMPAIGN_ID, $campaign->id);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
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
        $this->assertEquals($donation1->id, $data[0]['id']);
        $this->assertEquals($donation2->id, $data[1]['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsShouldNotReturnSensitiveData()
    {
        Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveProperties = [
            'donorIp',
            'email',
            'phone',
            'billingAddress',
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data[0], $sensitiveProperties));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsShouldNotReturnAnonymousDonations()
    {
        Donation::query()->delete();

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        // This anonymous donation should NOT be returned to the data array.
        Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        
        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($donation->id, $data[0]['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsShouldReturnAnonymousDonations()
    {
        Donation::query()->delete();

        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        /**
         * This anonymous donation should be returned to the data array.
         * @var  Donation $donation2
         * */
        $donation2 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'hideAnonymousDonations' => false,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(2, count($data));
        $this->assertEquals($donation1->id, $data[0]['id']);
        $this->assertEquals($donation2->id, $data[1]['id']);
    }
}

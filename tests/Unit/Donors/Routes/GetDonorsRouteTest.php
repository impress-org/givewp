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
use Give\Framework\Support\ValueObjects\Money;
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

        $donor1 = $this->getDonor1WithDonationAssociated($campaign->id);
        $donor2 = $this->getDonor2WithDonationAssociated($campaign->id);

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

        $donor1 = $this->getDonor1WithDonationAssociated($campaign->id);
        $donor2 = $this->getDonor2WithDonationAssociated($campaign->id, true);

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($donor1->id, $data[0]['id']);
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

        $donor1 = $this->getDonor1WithDonationAssociated($campaign->id);
        $donor2 = $this->getDonor2WithDonationAssociated($campaign->id, true);

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
        $this->assertEquals($donor1->id, $data[0]['id']);
        $this->assertEquals($donor2->id, $data[1]['id']);
    }

    /**
     * @unreleased
     *
     * @dataProvider sortableColumnsDataProvider
     *
     * @throws Exception
     */
    public function testGetDonorsSortedColumns($sortableColumn)
    {
        DB::query("DELETE FROM " . DB::prefix('give_donors'));

        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();

        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();


        $donor1 = $this->getDonor1WithDonationAssociated($campaign1->id);
        $donor2 = $this->getDonor2WithDonationAssociated($campaign1->id);
        $donor3 = $this->getDonor3WithDonationAssociated($campaign2->id);

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        /**
         * Ascendant Direction
         */
        $request->set_query_params(
            [
                'page' => 1,
                'per_page' => 30,
                'sort' => $sortableColumn,
                'direction' => 'ASC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(3, count($data));
        $this->assertEquals($donor1->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($donor2->{$sortableColumn}, $data[1][$sortableColumn]);
        $this->assertEquals($donor3->{$sortableColumn}, $data[2][$sortableColumn]);

        $request->set_query_params(
            [
                'page' => 1,
                'per_page' => 30,
                'sort' => $sortableColumn,
                'direction' => 'ASC',
                'campaignId' => $campaign1->id, // Filtering by campaignId
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(2, count($data));
        $this->assertEquals($donor1->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($donor2->{$sortableColumn}, $data[1][$sortableColumn]);

        /**
         * Descendant Direction
         */
        $request->set_query_params(
            [
                'page' => 1,
                'per_page' => 3,
                'sort' => $sortableColumn,
                'direction' => 'DESC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(3, count($data));
        $this->assertEquals($donor3->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($donor2->{$sortableColumn}, $data[1][$sortableColumn]);
        $this->assertEquals($donor1->{$sortableColumn}, $data[2][$sortableColumn]);

        $request->set_query_params(
            [
                'page' => 1,
                'per_page' => 30,
                'sort' => $sortableColumn,
                'direction' => 'DESC',
                'campaignId' => $campaign1->id, // Filtering by campaignId
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(2, count($data));
        $this->assertEquals($donor2->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($donor1->{$sortableColumn}, $data[1][$sortableColumn]);
    }

    public function sortableColumnsDataProvider(): array
    {
        return [
            ['id'],
            ['createdAt'],
            ['name'],
            ['firstName'],
            ['lastName'],
            ['totalAmountDonated'],
            ['totalNumberOfDonations'],
        ];
    }

    /**
     * @throws Exception
     */
    private function getDonor1WithDonationAssociated(int $campaignId, bool $anonymous = false): Donor
    {
        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => $anonymous]);
        $donor1 = $donation1->donor;

        $donor1->firstName = 'A';
        $donor1->lastName = 'A';
        $donor1->name = 'A A';
        $donor1->totalAmountDonated = new Money(100, 'USD');
        $donor1->totalNumberOfDonations = 1;
        $donor1->save();

        give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::DONOR_ID, $donor1->id);

        return Donor::find($donor1->id);
    }

    /**
     * @throws Exception
     */
    private function getDonor2WithDonationAssociated(int $campaignId, bool $anonymous = false): Donor
    {
        /** @var  Donation $donation2 */
        $donation2 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => $anonymous]);
        $donor2 = $donation2->donor;

        $donor2->firstName = 'B';
        $donor2->lastName = 'B';
        $donor2->name = 'B B';
        $donor2->totalAmountDonated = new Money(200, 'USD');
        $donor2->totalNumberOfDonations = 2;
        $donor2->save();

        give()->payment_meta->update_meta($donation2->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        give()->payment_meta->update_meta($donation2->id, DonationMetaKeys::DONOR_ID, $donor2->id);

        return Donor::find($donor2->id);
    }

    /**
     * @throws Exception
     */
    private function getDonor3WithDonationAssociated(int $campaignId, bool $anonymous = false): Donor
    {
        /** @var  Donation $donation3 */
        $donation3 = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => $anonymous]);
        $donor3 = $donation3->donor;

        $donor3->firstName = 'C';
        $donor3->lastName = 'C';
        $donor3->name = 'C C';
        $donor3->totalAmountDonated = new Money(300, 'USD');
        $donor3->totalNumberOfDonations = 3;
        $donor3->save();

        give()->payment_meta->update_meta($donation3->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        give()->payment_meta->update_meta($donation3->id, DonationMetaKeys::DONOR_ID, $donor3->id);

        return Donor::find($donor3->id);
    }
}

<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class GetDonorsRouteTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     */
    public function testGetDonorsShouldReturnAllModelProperties()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorsShouldReturnAllModelProperties',
                'user_pass' => 'testGetDonorsShouldReturnAllModelProperties',
                'user_email' => 'testGetDonorsShouldReturnAllModelProperties@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'onlyWithDonations' => false,
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        // Remove additional property add by the prepare_response_for_collection() method
        unset($data[0]['_links']);

        // TODO: show shape of DateTime objects
        $createdAtJson = json_encode($data[0]['createdAt']);

        $this->assertEquals(200, $status);
        $this->assertEquals([
            'id' => $donor->id,
            'userId' => $donor->userId,
            'createdAt' => json_decode($createdAtJson, true),
            'name' => $donor->name,
            'firstName' => $donor->firstName,
            'lastName' => $donor->lastName,
            'email' => $donor->email,
            'phone' => $donor->phone,
            'prefix' => $donor->prefix,
            'additionalEmails' => $donor->additionalEmails,
            'totalAmountDonated' => $donor->totalAmountDonated->toArray(),
            'totalNumberOfDonations' => $donor->totalNumberOfDonations,
        ], $data[0]);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsShouldNotIncludeSensitiveData()
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
        $this->assertEmpty(array_intersect_key($data[0], array_flip($sensitiveProperties)));
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsShouldIncludeSensitiveData()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorsShouldIncludeSensitiveData',
                'user_pass' => 'testGetDonorsShouldIncludeSensitiveData',
                'user_email' => 'testGetDonorsShouldIncludeSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'onlyWithDonations' => false,
                'includeSensitiveData' => true,
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
        $this->assertNotEmpty(array_intersect_key($data[0], array_flip($sensitiveProperties)));
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testGetDonorsShouldReturn403ErrorSensitiveData',
                'user_pass' => 'testGetDonorsShouldReturn403ErrorSensitiveData',
                'user_email' => 'testGetDonorsShouldReturn403ErrorSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'onlyWithDonations' => false,
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsShouldReturnOnlyDonorsWithDonations()
    {
        DB::query("DELETE FROM " . DB::prefix('give_donors'));

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        $donor1 = $this->createDonor1WithDonation($campaign->id);
        $donor2 = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'onlyWithDonations' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($donor1->id, $data[0]['id']);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsShouldReturnDonorsWithOrWithoutDonations()
    {
        DB::query("DELETE FROM " . DB::prefix('give_donors'));

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        $donor1 = $this->createDonor1WithDonation($campaign->id);
        $donor2 = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'onlyWithDonations' => false,
                'direction' => 'ASC',
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
     * @since 4.0.0
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
                'direction' => 'ASC',
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
                'direction' => 'ASC',
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
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsByCampaignId()
    {
        Donation::query()->delete();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        $donor1 = $this->createDonor1WithDonation($campaign->id);
        $donor2 = $this->createDonor2WithDonation($campaign->id);

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'campaignId' => $campaign->id,
                'direction' => 'ASC',
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
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsShouldNotIncludeAnonymousDonors()
    {
        Donation::query()->delete();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        $donor1 = $this->createDonor1WithDonation($campaign->id);
        $donor2 = $this->createDonor2WithDonation($campaign->id, true);

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
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsShouldIncludeAnonymousDonors()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorsShouldIncludeAnonymousDonors',
                'user_pass' => 'testGetDonorsShouldIncludeAnonymousDonors',
                'user_email' => 'testGetDonorsShouldIncludeAnonymousDonors@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        Donation::query()->delete();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        $donor1 = $this->createDonor1WithDonation($campaign->id);
        $donor2 = $this->createDonor2WithDonation($campaign->id, true);

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonors' => 'include',
                'direction' => 'ASC',
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
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsShouldReturn403ErrorWhenNotAdminUserIncludeAnonymousDonors()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testGetDonorsShouldReturn403ErrorAnonymousDonors',
                'user_pass' => 'testGetDonorsShouldReturn403ErrorAnonymousDonors',
                'user_email' => 'testGetDonorsShouldReturn403ErrorAnonymousDonors@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        Donation::query()->delete();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        $this->createDonor1WithDonation($campaign->id);
        $this->createDonor2WithDonation($campaign->id, true);

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonors' => 'include',
                'direction' => 'ASC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorsShouldRedactAnonymousDonors()
    {
        Donation::query()->delete();

        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        $donor1 = $this->createDonor1WithDonation($campaign->id);
        $donor2 = $this->createDonor2WithDonation($campaign->id, true);

        $route = '/' . DonorRoute::NAMESPACE . '/donors';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonors' => 'redact',
                'direction' => 'ASC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(2, count($data));
        $this->assertEquals($donor1->id, $data[0]['id']);
        $this->assertEquals(0, $data[1]['id']);

        $anonymousDataRedacted = [
            //'id', // This property is Checked above...
            'name',
            'firstName',
            'lastName',
            'prefix',
        ];

        foreach ($anonymousDataRedacted as $property) {
            $this->assertEquals(__('anonymous', 'give'), $data[1][$property]);
        }
    }

    /**
     * @since 4.0.0
     *
     * @dataProvider sortableColumnsDataProvider
     *
     * @throws Exception
     */
    public function testGetDonorsSortedByColumns($sortableColumn)
    {
        DB::query("DELETE FROM " . DB::prefix('give_donors'));

        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();

        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();

        $donor1 = $this->createDonor1WithDonation($campaign1->id);
        $donor2 = $this->createDonor2WithDonation($campaign1->id);
        $donor3 = $this->createDonor3WithDonation($campaign2->id);

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

    /**
     * @since 4.0.0
     */
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
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createDonor1WithDonation(int $campaignId = 0, bool $anonymous = false): Donor
    {
        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => $anonymous,
            'mode' => DonationMode::LIVE(),
        ]);
        $donor1 = $donation1->donor;

        $donor1->firstName = 'A';
        $donor1->lastName = 'A';
        $donor1->name = 'A A';
        $donor1->totalAmountDonated = new Money(100, 'USD');
        $donor1->totalNumberOfDonations = 1;
        $donor1->save();

        give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::DONOR_ID, $donor1->id);

        if ($campaignId) {
            give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        }

        return Donor::find($donor1->id);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createDonor2WithDonation(int $campaignId = 0, bool $anonymous = false): Donor
    {
        /** @var  Donation $donation2 */
        $donation2 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => $anonymous,
            'mode' => DonationMode::LIVE(),
        ]);
        $donor2 = $donation2->donor;

        $donor2->firstName = 'B';
        $donor2->lastName = 'B';
        $donor2->name = 'B B';
        $donor2->totalAmountDonated = new Money(200, 'USD');
        $donor2->totalNumberOfDonations = 2;
        $donor2->save();

        give()->payment_meta->update_meta($donation2->id, DonationMetaKeys::DONOR_ID, $donor2->id);

        if ($campaignId) {
            give()->payment_meta->update_meta($donation2->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        }

        return Donor::find($donor2->id);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createDonor3WithDonation(int $campaignId = 0, bool $anonymous = false): Donor
    {
        /** @var  Donation $donation3 */
        $donation3 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => $anonymous,
            'mode' => DonationMode::LIVE(),
        ]);
        $donor3 = $donation3->donor;

        $donor3->firstName = 'C';
        $donor3->lastName = 'C';
        $donor3->name = 'C C';
        $donor3->totalAmountDonated = new Money(300, 'USD');
        $donor3->totalNumberOfDonations = 3;
        $donor3->save();

        give()->payment_meta->update_meta($donation3->id, DonationMetaKeys::DONOR_ID, $donor3->id);

        if ($campaignId) {
            give()->payment_meta->update_meta($donation3->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        }

        return Donor::find($donor3->id);
    }
}

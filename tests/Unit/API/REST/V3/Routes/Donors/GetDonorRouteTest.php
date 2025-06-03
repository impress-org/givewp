<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class GetDonorRouteTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @since 4.0.0
     */
    public function testGetDonorShouldReturnAllModelProperties()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorShouldReturnAllModelProperties',
                'user_pass' => 'testGetDonorShouldReturnAllModelProperties',
                'user_email' => 'testGetDonorShouldReturnAllModelProperties@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
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

        // TODO: show shape of DateTime objects
        $createdAtJson = json_encode($data['createdAt']);

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
        ], $data);
    }

    /**
     * @unreleased
     */
    public function testGetDonorShouldReturnSelfLink()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _links data
        $data = $this->responseToData($response, true);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_links', $data);
        $this->assertArrayHasKey('self', $data['_links']);
    }

    /**
     * @unreleased
     */
    public function testGetDonorShouldReturnStatisticsLink()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _links data
        $data = $this->responseToData($response, true);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_links', $data);
        $this->assertArrayHasKey('givewp:statistics', $data['_links']);
    }

    /**
     * @unreleased
     */
    public function testGetDonorShouldEmbedStatistics()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params([
            '_embed' => 'givewp:statistics',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _embedded data
        $data = $this->responseToData($response, true);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_embedded', $data);
        $this->assertArrayHasKey('givewp:statistics', $data['_embedded']);
        $this->assertIsArray($data['_embedded']['givewp:statistics']);
        $this->assertNotEmpty($data['_embedded']['givewp:statistics'][0]);
    }

    /**
     * @throws Exception
     */
    public function testGetDonor()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorShouldNotIncludeSensitiveData()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

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
        $this->assertEmpty(array_intersect_key($data, $sensitiveProperties));
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorShouldIncludeSensitiveData()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorShouldIncludeSensitiveData',
                'user_pass' => 'testGetDonorShouldIncludeSensitiveData',
                'user_email' => 'testGetDonorShouldIncludeSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
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
        $this->assertNotEmpty(array_intersect_key($data, array_flip($sensitiveProperties)));
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testGetDonorShouldReturn403ErrorSensitiveData',
                'user_pass' => 'testGetDonorShouldReturn403ErrorSensitiveData',
                'user_email' => 'testGetDonorShouldReturn403ErrorSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
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
    public function testGetDonorShouldIncludeAnonymousDonor()
    {
        Donation::query()->delete();

        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonorShouldIncludeAnonymousDonations',
                'user_pass' => 'testGetDonorShouldIncludeAnonymousDonations',
                'user_email' => 'testGetDonorShouldIncludeAnonymousDonations@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        $donor = $this->createAnonymousDonorWithDonation();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonors' => 'include',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorShouldReturn403ErrorWhenNotAdminUserIncludeIncludeAnonymousDonor()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testGetDonorShouldReturn403ErrorAnonymousDonors',
                'user_pass' => 'testGetDonorShouldReturn403ErrorAnonymousDonors',
                'user_email' => 'testGetDonorShouldReturn403ErrorAnonymousDonors@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        Donation::query()->delete();

        $donor = $this->createAnonymousDonorWithDonation();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonors' => 'include',
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
    public function testGetDonorShouldRedactAnonymousDonor()
    {
        Donation::query()->delete();

        $donor = $this->createAnonymousDonorWithDonation();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonors' => 'redact',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(0, $data['id']);

        $anonymousDataRedacted = [
            'name',
            'firstName',
            'lastName',
            'prefix',
        ];

        foreach ($anonymousDataRedacted as $property) {
            $this->assertEquals(__('anonymous', 'give'), $data[$property]);
        }
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createAnonymousDonorWithDonation(): Donor
    {
        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => true,
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

        return Donor::find($donor1->id);
    }
}

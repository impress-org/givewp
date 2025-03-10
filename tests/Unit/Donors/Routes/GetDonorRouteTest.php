<?php

namespace Unit\Donors\Routes;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationRoute;
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
     * @throws Exception
     */
    public function testGetDonor()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/donors/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorShouldNotIncludeSensitiveData()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/donors/' . $donor->id;
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
     * @unreleased
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

        $route = '/' . DonationRoute::NAMESPACE . '/donors/' . $donor->id;
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
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/donors/' . $donor->id;
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
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorShouldIncludeAnonymousData()
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

        $route = '/' . DonationRoute::NAMESPACE . '/donors/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonations' => 'include',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorShouldReturn403ErrorWhenNotAdminUserIncludeIncludeAnonymousData()
    {
        Donation::query()->delete();

        $donor = $this->createAnonymousDonorWithDonation();

        $route = '/' . DonationRoute::NAMESPACE . '/donors/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonations' => 'include',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonorShouldRedactAnonymousData()
    {
        Donation::query()->delete();

        $donor = $this->createAnonymousDonorWithDonation();

        $route = '/' . DonationRoute::NAMESPACE . '/donors/' . $donor->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonations' => 'redact',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(0, $data['id']);

        $anonymousDataRedacted = [
            //'id', // This property is Checked above...
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
     * @unreleased
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

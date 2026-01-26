<?php

namespace Unit\API\REST\V3\Routes\Donors;

use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\Facades\Str;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

class DonorRouteGetTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @since 4.0.0
     */
    public function testGetDonorShouldReturnAllModelProperties()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params([
            'onlyWithDonations' => false,
            'includeSensitiveData' => true,
        ]);

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
            'company' => $donor->company,
            'addresses' => array_map(fn ($address) => $address->toArray(), $donor->addresses),
            'avatarId' => $donor->avatarId,
            'avatarUrl' => null,
            'customFields' => [],
            'wpUserPermalink' => $donor->userId ? get_edit_user_link($donor->userId) : null,
            'additionalEmails' => $donor->additionalEmails,
            'totalAmountDonated' => $donor->totalAmountDonated->toArray(),
            'totalNumberOfDonations' => $donor->totalNumberOfDonations,
        ], $data);
    }

    /**
     * @since 4.4.0
     */
    public function testGetDonorShouldReturnSelfLink()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _links data
        $data = $this->responseToData($response, false);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_links', $data);
        $this->assertArrayHasKey('self', $data['_links']);
    }

    /**
     * @since 4.4.0
     */
    public function testGetDonorShouldReturnStatisticsLink()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _links data
        $data = $this->responseToData($response, false);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_links', $data);
        $this->assertArrayHasKey('givewp:statistics', $data['_links']);
    }

    /**
     * @since 4.14.0
     */
    public function testGetDonorShouldReturnDonationsLink()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _links data
        $data = $this->responseToData($response, false);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_links', $data);
        $this->assertArrayHasKey('givewp:donations', $data['_links']);
        $this->assertArrayHasKey('href', $data['_links']['givewp:donations'][0]);
        $this->assertStringContainsString('donorId=' . $donor->id, $data['_links']['givewp:donations'][0]['href']);
    }

    /**
     * @since 4.14.0
     */
    public function testGetDonorShouldReturnSubscriptionsLink()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _links data
        $data = $this->responseToData($response, false);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_links', $data);
        $this->assertArrayHasKey('givewp:subscriptions', $data['_links']);
        $this->assertArrayHasKey('href', $data['_links']['givewp:subscriptions'][0]);
        $this->assertStringContainsString('donorId=' . $donor->id, $data['_links']['givewp:subscriptions'][0]['href']);
    }

    /**
     * @since 4.4.0
     */
    public function testGetDonorShouldEmbedStatistics()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, ['_embed' => 'givewp:statistics']);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _embedded data
        $data = $this->responseToData($response, ['givewp:statistics']);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_embedded', $data);
        $this->assertArrayHasKey('givewp:statistics', $data['_embedded']);
        $this->assertIsArray($data['_embedded']['givewp:statistics']);
        $this->assertNotEmpty($data['_embedded']['givewp:statistics'][0]);
    }

    /**
     * @since 4.14.0
     */
    public function testGetDonorShouldEmbedDonations()
    {
        /** @var  Donor $donor */
        $donor = $this->createDonorWithDonation();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(['_embed' => 'givewp:donations']);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _embedded data
        $data = $this->responseToData($response, ['givewp:donations']);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_embedded', $data);
        $this->assertArrayHasKey('givewp:donations', $data['_embedded']);
        $this->assertIsArray($data['_embedded']['givewp:donations']);
        $this->assertNotEmpty($data['_embedded']['givewp:donations'][0]);
    }

    /**
     * @since 4.14.0
     */
    public function testGetDonorShouldEmbedSubscriptions()
    {
        /** @var  Donor $donor */
        $donor = $this->createDonorWithSubscription();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, ['_embed' => 'givewp:subscriptions']);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        //The $response->get_data() method do not include _embedded data
        $data = $this->responseToData($response, ['givewp:subscriptions']);

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
        $this->assertArrayHasKey('_embedded', $data);
        $this->assertArrayHasKey('givewp:subscriptions', $data['_embedded']);
        $this->assertIsArray($data['_embedded']['givewp:subscriptions']);
        $this->assertNotEmpty($data['_embedded']['givewp:subscriptions'][0]);
    }

    /**
     * @throws Exception
     */
    public function testGetDonor()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donor->id, $data['id']);
    }

    /**
     * @since 4.14.0 name and lastName should return only the first letter of the last name when sensitive data is not included
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonorShouldNotIncludeSensitiveData()
    {
        /** @var  Donor $donor */
        $donor = Donor::factory()->create();

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveProperties = [
            'userId',
            'email',
            'phone',
            'additionalEmails',
            'addresses',
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data, $sensitiveProperties));

        // lastName should return only the first letter when sensitive data is not included
        $this->assertEquals(Str::substr($donor->lastName, 0, 1), $data['lastName']);

        // name should return the full name and the first letter of the last name
        $this->assertEquals($donor->firstName . ' ' . Str::substr($donor->lastName, 0, 1), $data['name']);
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
            'lastName',
            'addresses',
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
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(['anonymousDonors' => 'redact']);

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
     * @since 4.14.0
     *
     * @throws Exception
     */
    public function testGetDonorShouldAllowOwnerToViewOwnSensitiveData()
    {
        // Create a user
        $user = $this->factory()->user->create([
            'role' => 'subscriber',
            'user_login' => 'testDonorOwner3',
            'user_email' => 'testDonorOwner3@test.com',
        ]);

        // Create a donor linked to this user
        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user,
            'email' => 'testDonorOwner3@test.com',
            'phone' => '1234567890',
        ]);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $donor->id;
        // Use WP_REST_Request directly to maintain the custom user
        wp_set_current_user($user);
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['includeSensitiveData' => true]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals($donor->id, $data['id']);
        $this->assertEquals($donor->email, $data['email']);
        $this->assertEquals($donor->phone, $data['phone']);
    }

    /**
     * @since 4.14.0
     *
     * @throws Exception
     */
    public function testGetDonorShouldReturn403WhenNonOwnerTriesToViewOtherDonorSensitiveData()
    {
        // Create a user
        $user = $this->factory()->user->create([
            'role' => 'subscriber',
            'user_login' => 'testDonorOwner4',
            'user_email' => 'testDonorOwner4@test.com',
        ]);

        // Create another user and donor
        $otherUser = $this->factory()->user->create([
            'role' => 'subscriber',
            'user_login' => 'otherUser',
            'user_email' => 'otherUser@test.com',
        ]);

        /** @var Donor $otherDonor */
        $otherDonor = Donor::factory()->create([
            'userId' => $otherUser,
        ]);

        $route = '/' . DonorRoute::NAMESPACE . '/' . DonorRoute::BASE . '/' . $otherDonor->id;
        // Use WP_REST_Request directly to maintain the custom user
        wp_set_current_user($user);
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['includeSensitiveData' => true]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(403, $response->get_status());
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

    /**
     * @since 4.14.0
     *
     * @throws Exception
     */
    private function createDonorWithDonation(): Donor
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'mode' => DonationMode::LIVE(),
            'anonymous' => false,
        ]);
        $donor = $donation->donor;

        give()->payment_meta->update_meta($donation->id, DonationMetaKeys::DONOR_ID, $donor->id);

        return Donor::find($donor->id);
    }

    /**
     * @since 4.14.0
     *
     * @throws Exception
     */
    private function createDonorWithSubscription(): Donor
    {
        $subscription = $this->createSubscription();

        return $subscription->donor;
    }

    /**
     * @since 4.14.0
     *
     * @throws Exception
     */
    private function createSubscription(string $mode = 'live', string $status = 'active', int $amount = 10000): Subscription
    {
        $donor = Donor::factory()->create();

        return Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'amount' => new Money($amount, 'USD'),
            'status' => new SubscriptionStatus($status),
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'installments' => 0,
            'mode' => new SubscriptionMode($mode),
            'donorId' => $donor->id,
        ], [
            'anonymous' => false,
            'donorId' => $donor->id,
        ]);
    }
}

<?php

namespace Unit\Donations\Routes;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Routes\RegisterDonationRoutes;
use Give\Donations\ValueObjects\DonationRoute;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Helpers\Hooks;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetDonationRouteTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function setUp()
    {
        Hooks::addAction('rest_api_init', RegisterDonationRoutes::class);

        parent::setUp();
    }

    /**
     * @unreleased
     */
    public function testGetDonationShouldReturnAllModelProperties()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $newAdminUser = self::factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationShouldReturnAllModelProperties',
                'user_pass' => 'testGetDonationShouldReturnAllModelProperties',
                'user_email' => 'testGetDonationShouldReturnAllModelProperties@test.com',
            ]
        );

        wp_set_current_user($newAdminUser);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        // TODO: show shape of DateTime objects
        $createdAtJson = json_encode($data['createdAt']);
        $updatedAtJson = json_encode($data['updatedAt']);

        $this->assertEquals(200, $status);
        $this->assertEquals([
            'id' => $donation->id,
            'campaignId' => $donation->campaignId,
            'formId' => $donation->formId,
            'formTitle' => $donation->formTitle,
            'purchaseKey' => $donation->purchaseKey,
            'donorIp' => $donation->donorIp,
            'createdAt' => json_decode($createdAtJson, true),
            'updatedAt' => json_decode($updatedAtJson, true),
            'status' => $donation->status->getValue(),
            'type' => $donation->type->getValue(),
            'mode' => $donation->mode->getValue(),
            'amount' => $donation->amount->toArray(),
            'feeAmountRecovered' => $donation->feeAmountRecovered ? $donation->feeAmountRecovered->toArray() : null,
            'exchangeRate' => $donation->exchangeRate,
            'gatewayId' => $donation->gatewayId,
            'donorId' => $donation->donorId,
            'honorific' => $donation->honorific,
            'firstName' => $donation->firstName,
            'lastName' => $donation->lastName,
            'email' => $donation->email,
            'phone' => $donation->phone,
            'subscriptionId' => $donation->subscriptionId,
            'billingAddress' => $donation->billingAddress ? $donation->billingAddress->toArray() : null,
            'anonymous' => $donation->anonymous,
            'levelId' => $donation->levelId,
            'gatewayTransactionId' => $donation->gatewayTransactionId,
            'company' => $donation->company,
            'comment' => $donation->comment,
        ], $data);
    }

    /**
     * @throws Exception
     */
    public function testGetDonation()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donation->id, $data['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldNotIncludeSensitiveData()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveData = [
            'donorIp',
            'email',
            'phone',
            'billingAddress',
            'purchaseKey'
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data, array_flip($sensitiveData)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldIncludeSensitiveData()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationShouldIncludeSensitiveData',
                'user_pass' => 'testGetDonationShouldIncludeSensitiveData',
                'user_email' => 'testGetDonationShouldIncludeSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveData = [
            'donorIp',
            'email',
            'phone',
            'billingAddress',
            'purchaseKey'
        ];

        $this->assertEquals(200, $status);
        $this->assertNotEmpty(array_intersect_key($data, array_flip($sensitiveData)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testGetDonationShouldReturn403ErrorSensitiveData',
                'user_pass' => 'testGetDonationShouldReturn403ErrorSensitiveData',
                'user_email' => 'testGetDonationShouldReturn403ErrorSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
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
    public function testGetDonationShouldNotReturnAnonymousDonationByDefault()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);


        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldIncludeAnonymousDonation()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationsShouldIncludeAnonymousDonations',
                'user_pass' => 'testGetDonationsShouldIncludeAnonymousDonations',
                'user_email' => 'testGetDonationsShouldIncludeAnonymousDonations@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
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
        $this->assertEquals($donation->id, $data['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationShouldReturn403ErrorWhenNotAdminUserIncludeAnonymousDonation()
    {
        $newSubscriberUser = $this->factory()->user->create(
            [
                'role' => 'subscriber',
                'user_login' => 'testGetDonationShouldReturn403ErrorAnonymousDonation',
                'user_pass' => 'testGetDonationShouldReturn403ErrorAnonymousDonation',
                'user_email' => 'testGetDonationShouldReturn403ErrorAnonymousDonation@test.com',
            ]
        );
        wp_set_current_user($newSubscriberUser);

        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
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
    public function testGetDonationShouldRedactAnonymousDonation()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
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
        $this->assertEquals($donation->id, $data['id']);

        $anonymousDataRedacted = [
            'donorId',
            'honorific',
            'firstName',
            'lastName',
            'company',
        ];

        foreach ($anonymousDataRedacted as $property) {
            $this->assertEquals(__('anonymous', 'give'), $data[$property]);
        }
    }
}

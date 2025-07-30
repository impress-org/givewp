<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Exception;
use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class GetDonationRouteTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @since 4.0.0
     */
    public function testGetDonationShouldReturnAllModelProperties()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $data = $response->get_data();
        $dataJson = json_encode($data);

        $data = json_decode($dataJson, true);

        $this->assertEquals(200, $status);

        // Verify DateTime object structure for createdAt and updatedAt
        $this->assertIsArray($data['createdAt']);
        $this->assertArrayHasKey('date', $data['createdAt']);
        $this->assertArrayHasKey('timezone', $data['createdAt']);
        $this->assertArrayHasKey('timezone_type', $data['createdAt']);

        $this->assertIsArray($data['updatedAt']);
        $this->assertArrayHasKey('date', $data['updatedAt']);
        $this->assertArrayHasKey('timezone', $data['updatedAt']);
        $this->assertArrayHasKey('timezone_type', $data['updatedAt']);

        $this->assertEquals([
            'id' => $donation->id,
            'campaignId' => $donation->campaignId,
            'formId' => $donation->formId,
            'formTitle' => $donation->formTitle,
            'purchaseKey' => $donation->purchaseKey,
            'donorIp' => $donation->donorIp,
            'createdAt' => $data['createdAt'], // Keep actual DateTime object structure
            'updatedAt' => $data['updatedAt'], // Keep actual DateTime object structure
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
            'customFields' => $data['customFields'], // Custom fields are dynamic, so we'll just check they exist
            'eventTicketsAmount' => $donation->eventTicketsAmount()->toArray(),
            'eventTickets' => [],
            'gateway' => array_merge(
                $donation->gateway()->toArray(),
                [
                    'transactionUrl' => $donation->gateway()->getTransactionUrl($donation),
                ]
            )
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
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($donation->id, $data['id']);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationShouldNotIncludeSensitiveData()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveData = [
            'donorIp',
            'email',
            'phone',
            'billingAddress',
            'purchaseKey',
            'customFields'
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data, $sensitiveData));
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationShouldIncludeSensitiveData()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

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
            'purchaseKey',
            'customFields'
        ];

        $this->assertEquals(200, $status);
        $this->assertNotEmpty(array_intersect_key($data, array_flip($sensitiveData)));
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => false]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'subscriber');

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
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationShouldNotReturnAnonymousDonationByDefault()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);


        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationShouldIncludeAnonymousDonation()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

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
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationShouldReturn403ErrorWhenNotAdminUserIncludeAnonymousDonation()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'subscriber');

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
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationShouldRedactAnonymousDonation()
    {
        /** @var  Donation $donation */
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE(), 'anonymous' => true]);

        $route = '/' . DonationRoute::NAMESPACE . '/donations/' . $donation->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

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
            'customFields',
        ];

        foreach ($anonymousDataRedacted as $property) {
            if ($property === 'donorId') {
                $this->assertEquals(0, $data[$property]);
            } else if ($property === 'customFields') {
                $this->assertEquals([], $data[$property]);
            } else {
                $this->assertEquals(__('anonymous', 'give'), $data[$property]);
            }
        }
    }
}

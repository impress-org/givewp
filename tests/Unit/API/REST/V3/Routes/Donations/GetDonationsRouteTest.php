<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Exception;
use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use WP_REST_Server;

/**
 * @since 4.0.0
 */
class GetDonationsRouteTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @unreleased updated the date format
     * @since 4.0.0
     */
    public function testGetDonationShouldReturnAllModelProperties()
    {
        $donation = $this->createDonation1();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        // Remove additional property add by the prepare_response_for_collection() method
        unset($data[0]['_links']);

        $this->assertEquals(200, $status);

        $this->assertIsString($data[0]['createdAt']);
        $this->assertIsString($data[0]['updatedAt']);

        $this->assertEquals([
            'id' => $donation->id,
            'campaignId' => $donation->campaignId,
            'formId' => $donation->formId,
            'formTitle' => $donation->formTitle,
            'purchaseKey' => $donation->purchaseKey,
            'donorIp' => $donation->donorIp,
            'createdAt' => $data[0]['createdAt'],
            'updatedAt' => $data[0]['updatedAt'],
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
            'customFields' => $data[0]['customFields'], // Custom fields are dynamic, so we'll just check they exist
            'eventTicketsAmount' => $data[0]['eventTicketsAmount'],
            'eventTickets' => [],
            'gateway' => array_merge(
                $donation->gateway()->toArray(),
                [
                    'transactionUrl' => $donation->gateway()->getTransactionUrl($donation),
                ]
            )
        ], $data[0]);
    }

    /**
    * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationsShouldNotIncludeSensitiveData()
    {
        $this->createDonation1();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveProperties = [
            'donorIp',
            'email',
            'phone',
            'billingAddress',
            'purchaseKey',
            'customFields',
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data[0], $sensitiveProperties));
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationsShouldIncludeSensitiveData()
    {
        $this->createDonation1();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        $request->set_query_params(
            [
                'includeSensitiveData' => true,
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $sensitiveProperties = [
            'donorIp',
            'email',
            'phone',
            'billingAddress',
            'purchaseKey',
            'customFields',
        ];

        $this->assertEquals(200, $status);
        $this->assertNotEmpty(array_intersect_key($data[0], array_flip($sensitiveProperties)));
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationsShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
    {
        $this->createDonation1();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
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
    public function testGetDonationsWithPagination()
    {
        Donation::query()->delete();

        $donation1 = $this->createDonation1();
        $donation2 = $this->createDonation2();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $request->set_query_params(
            [
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
        $this->assertEquals($donation1->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);

        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
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
        $this->assertEquals($donation2->id, $data[0]['id']);
        $this->assertEquals(2, $headers['X-WP-Total']);
        $this->assertEquals(2, $headers['X-WP-TotalPages']);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationsByCampaignId()
    {
        /** @var Campaign $campaign */
        $campaign = Campaign::factory()->create();

        $donation1 = $this->createDonation1($campaign->id);
        $donation2 = $this->createDonation2($campaign->id);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
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
        $this->assertEquals($donation1->id, $data[0]['id']);
        $this->assertEquals($donation2->id, $data[1]['id']);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationsShouldNotIncludeAnonymousDonations()
    {
        Donation::query()->delete();

        $donation1 = $this->createDonation1();

        // This anonymous donation should NOT be returned to the data array.
        $donation2 = $this->createDonation2(0, true);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($donation1->id, $data[0]['id']);
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationsShouldIncludeAnonymousDonations()
    {
        Donation::query()->delete();

        $donation1 = $this->createDonation1();

        // This anonymous donation should be returned to the data array.
        $donation2 = $this->createDonation2(0, true);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(
            [
                'anonymousDonations' => 'include',
                'direction' => 'ASC',
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
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testGetDonationsShouldReturn403ErrorWhenNotAdminUserIncludeAnonymousDonations()
    {
        Donation::query()->delete();

        $this->createDonation2(0, true);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
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
    public function testGetDonationsShouldRedactAnonymousDonations()
    {
        Donation::query()->delete();

        $donation1 = $this->createDonation1();

        // This anonymous donation should be returned to the data array.
        $donation2 = $this->createDonation2(0, true);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(
            [
                'anonymousDonations' => 'redact',
                'direction' => 'ASC',
            ]
        );

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(2, count($data));
        $this->assertEquals($donation1->id, $data[0]['id']);
        $this->assertEquals($donation2->id, $data[1]['id']);

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
                $this->assertEquals(0, $data[1][$property]);
            } elseif ($property === 'customFields') {
                $this->assertEquals([], $data[1][$property]);
            } else {
                $this->assertEquals(__('anonymous', 'give'), $data[1][$property]);
            }
        }
    }

    /**
     * @since 4.0.0
     *
     * @dataProvider sortableColumnsDataProvider
     *
     * @throws Exception
     */
    public function testGetDonationsSortedByColumns($sortableColumn)
    {
        Donation::query()->delete();

        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();

        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();


        $donation1 = $this->createDonation1($campaign1->id);
        $donation2 = $this->createDonation2($campaign1->id);
        $donation3 = $this->createDonation3($campaign2->id);


        $route = '/' . DonorRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

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
        $this->assertEquals($this->getExpectedValue($donation1, $sortableColumn), $data[0][$sortableColumn]);
        $this->assertEquals($this->getExpectedValue($donation2, $sortableColumn), $data[1][$sortableColumn]);
        $this->assertEquals($this->getExpectedValue($donation3, $sortableColumn), $data[2][$sortableColumn]);

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
        $this->assertEquals($this->getExpectedValue($donation1, $sortableColumn), $data[0][$sortableColumn]);
        $this->assertEquals($this->getExpectedValue($donation2, $sortableColumn), $data[1][$sortableColumn]);

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
        $this->assertEquals($this->getExpectedValue($donation3, $sortableColumn), $data[0][$sortableColumn]);
        $this->assertEquals($this->getExpectedValue($donation2, $sortableColumn), $data[1][$sortableColumn]);
        $this->assertEquals($this->getExpectedValue($donation1, $sortableColumn), $data[2][$sortableColumn]);

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
        $this->assertEquals($this->getExpectedValue($donation2, $sortableColumn), $data[0][$sortableColumn]);
        $this->assertEquals($this->getExpectedValue($donation1, $sortableColumn), $data[1][$sortableColumn]);
    }

    /**
     * @since 4.8.0
     */
    public function testGetDonationsBySubscriptionId()
    {
        // Create a subscription with initial donation
        $subscription = $this->createSubscription();

        // Create renewal donations for the subscription
        $renewal1 = $subscription->createRenewal();
        $renewal2 = $subscription->createRenewal();

        // Create another subscription with different donations
        $subscription2 = $this->createSubscription();
        $renewal3 = $subscription2->createRenewal();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        $request->set_query_params(
            [
                'subscriptionId' => $subscription->id,
            ]
        );

        $response = $this->dispatchRequest($request);
        $data = json_decode(json_encode($response->get_data()), true);

        $this->assertEquals(200, $response->get_status());

        // Should return 3 donations: initial donation + 2 renewals
        $this->assertCount(3, $data);

        // All donations should belong to the specified subscription
        foreach ($data as $donation) {
            $this->assertEquals($subscription->id, $donation['subscriptionId']);
        }
    }

    /**
     * @since 4.8.0
     */
    public function testGetDonationsBySubscriptionIdShouldReturnEmptyWhenSubscriptionDoesNotExist()
    {
        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        $request->set_query_params(
            [
                'subscriptionId' => 99999, // Non-existent subscription ID
            ]
        );

        $response = $this->dispatchRequest($request);
        $data = json_decode(json_encode($response->get_data()), true);

        $this->assertEquals(200, $response->get_status());
        $this->assertCount(0, $data);
    }

    /**
     * @since 4.8.0
     */
    public function testGetDonationsBySubscriptionIdShouldReturnOnlyDonationsFromSpecifiedSubscription()
    {
        // Create first subscription with donations
        $subscription1 = $this->createSubscription();
        $renewal1 = $subscription1->createRenewal();

        // Create second subscription with donations
        $subscription2 = $this->createSubscription();
        $renewal2 = $subscription2->createRenewal();
        $renewal3 = $subscription2->createRenewal();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        // Filter by first subscription
        $request->set_query_params(
            [
                'subscriptionId' => $subscription1->id,
            ]
        );

        $response = $this->dispatchRequest($request);
        $data = json_decode(json_encode($response->get_data()), true);

        $this->assertEquals(200, $response->get_status());

        // Should return only donations from subscription1 (2 donations: initial + 1 renewal)
        $this->assertCount(2, $data);

        // All donations should belong to subscription1
        foreach ($data as $donation) {
            $this->assertEquals($subscription1->id, $donation['subscriptionId']);
            $this->assertNotEquals($subscription2->id, $donation['subscriptionId']);
        }
    }

    /**
     * @since 4.0.0
     */
    public function sortableColumnsDataProvider(): array
    {
        return [
            ['id'],
            ['createdAt'],
            ['updatedAt'],
            ['status'],
            ['amount'],
            ['feeAmountRecovered'],
            ['donorId'],
            ['firstName'],
            ['lastName'],
        ];
    }

    /**
     * @unreleased updated the amount values
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createDonation1(int $campaignId = 0, bool $anonymous = false): Donation
    {
        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => $anonymous,
            'amount' => Money::fromDecimal(91.27, 'USD'),
            'feeAmountRecovered' => Money::fromDecimal(1.27, 'USD'),
            'firstName' => 'A',
            'lastName' => 'A',
            'mode' => DonationMode::LIVE(),
        ]);

        if ($campaignId) {
            give()->payment_meta->update_meta($donation1->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        }

        return $donation1;
    }

    /**
     * @unreleased updated the amount values
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createDonation2(int $campaignId = 0, bool $anonymous = false): Donation
    {
        /** @var  Donation $donation2 */
        $donation2 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => $anonymous,
            'amount' => Money::fromDecimal(221.38, 'USD'),
            'feeAmountRecovered' => Money::fromDecimal(1.38, 'USD'),
            'firstName' => 'B',
            'lastName' => 'B',
            'mode' => DonationMode::LIVE(),
        ]);

        if ($campaignId) {
            give()->payment_meta->update_meta($donation2->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        }

        return $donation2;
    }

    /**
     * @unreleased updated the amount values
     * @since 4.0.0
     *
     * @throws Exception
     */
    private function createDonation3(int $campaignId = 0, bool $anonymous = false): Donation
    {
        /** @var  Donation $donation3 */
        $donation3 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => $anonymous,
            'amount' => Money::fromDecimal(316.45, 'USD'),
            'feeAmountRecovered' => Money::fromDecimal(1.45, 'USD'),
            'firstName' => 'C',
            'lastName' => 'C',
            'mode' => DonationMode::LIVE(),
        ]);

        if ($campaignId) {
            give()->payment_meta->update_meta($donation3->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        }

        return $donation3;
    }

    /**
     * @since 4.8.0
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

    /**
     * Get expected value for comparison with API response
     *
     * @since 4.6.0
     */
    private function getExpectedValue(Donation $donation, string $column)
    {
        if ($column === 'createdAt' || $column === 'updatedAt') {
            return $donation->{$column}->format('Y-m-d\TH:i:s');
        }

        return $donation->{$column};
    }
}

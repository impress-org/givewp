<?php

namespace Unit\Donations\Routes;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\Routes\RegisterDonationRoutes;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationRoute;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Routes\RegisterDonorRoutes;
use Give\Donors\ValueObjects\DonorRoute;
use Give\Framework\Support\ValueObjects\Money;
use Give\Helpers\Hooks;
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
        $donation = $this->createDonation1();

        $newAdminUser = self::factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationShouldReturnAllModelProperties',
                'user_pass' => 'testGetDonationShouldReturnAllModelProperties',
                'user_email' => 'testGetDonationShouldReturnAllModelProperties@test.com',
            ]
        );

        wp_set_current_user($newAdminUser);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
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
        $createdAtJson = json_encode($data[0]['createdAt']);
        $updatedAtJson = json_encode($data[0]['updatedAt']);

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
        ], $data[0]);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsShouldNotIncludeSensitiveData()
    {
        $this->createDonation1();

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
            'purchaseKey'
        ];

        $this->assertEquals(200, $status);
        $this->assertEmpty(array_intersect_key($data[0], array_flip($sensitiveProperties)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsShouldIncludeSensitiveData()
    {
        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'testGetDonationsShouldReturnSensitiveData',
                'user_pass' => 'testGetDonationsShouldReturnSensitiveData',
                'user_email' => 'testGetDonationsShouldReturnSensitiveData@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        $this->createDonation1();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

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
            'purchaseKey'
        ];

        $this->assertEquals(200, $status);
        $this->assertNotEmpty(array_intersect_key($data[0], array_flip($sensitiveProperties)));
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsShouldReturn403ErrorWhenNotAdminUserIncludeSensitiveData()
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

        $this->createDonation1();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
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
    public function testGetDonationsWithPagination()
    {
        Donation::query()->delete();

        $donation1 = $this->createDonation1();
        $donation2 = $this->createDonation2();

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

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
     * @unreleased
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
        $this->assertEquals($donation1->id, $data[0]['id']);
        $this->assertEquals($donation2->id, $data[1]['id']);
    }

    /**
     * @unreleased
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
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(1, count($data));
        $this->assertEquals($donation1->id, $data[0]['id']);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsShouldIncludeAnonymousDonations()
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

        Donation::query()->delete();

        $donation1 = $this->createDonation1();

        // This anonymous donation should be returned to the data array.
        $donation2 = $this->createDonation2(0, true);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
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
     * @unreleased
     *
     * @throws Exception
     */
    public function testGetDonationsShouldReturn403ErrorWhenNotAdminUserIncludeAnonymousDonations()
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

        Donation::query()->delete();

        $this->createDonation2(0, true);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
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
    public function testGetDonationsShouldRedactAnonymousDonations()
    {
        Donation::query()->delete();

        $donation1 = $this->createDonation1();

        // This anonymous donation should be returned to the data array.
        $donation2 = $this->createDonation2(0, true);

        $route = '/' . DonationRoute::NAMESPACE . '/donations';
        $request = new WP_REST_Request(WP_REST_Server::READABLE, $route);
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
        ];

        foreach ($anonymousDataRedacted as $property) {
            $this->assertEquals(__('anonymous', 'give'), $data[1][$property]);
        }
    }

    /**
     * @unreleased
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
        $this->assertEquals($donation1->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($donation2->{$sortableColumn}, $data[1][$sortableColumn]);
        $this->assertEquals($donation3->{$sortableColumn}, $data[2][$sortableColumn]);

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
        $this->assertEquals($donation1->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($donation2->{$sortableColumn}, $data[1][$sortableColumn]);

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
        $this->assertEquals($donation3->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($donation2->{$sortableColumn}, $data[1][$sortableColumn]);
        $this->assertEquals($donation1->{$sortableColumn}, $data[2][$sortableColumn]);

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
        $this->assertEquals($donation2->{$sortableColumn}, $data[0][$sortableColumn]);
        $this->assertEquals($donation1->{$sortableColumn}, $data[1][$sortableColumn]);
    }

    /**
     * @unreleased
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
     * @unreleased
     *
     * @throws Exception
     */
    private function createDonation1(int $campaignId = 0, bool $anonymous = false): Donation
    {
        /** @var  Donation $donation1 */
        $donation1 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => $anonymous,
            'amount' => new Money(100, 'USD'),
            'feeAmountRecovered' => new Money(10, 'USD'),
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
     * @unreleased
     *
     * @throws Exception
     */
    private function createDonation2(int $campaignId = 0, bool $anonymous = false): Donation
    {
        /** @var  Donation $donation2 */
        $donation2 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => $anonymous,
            'amount' => new Money(200, 'USD'),
            'feeAmountRecovered' => new Money(20, 'USD'),
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
     * @unreleased
     *
     * @throws Exception
     */
    private function createDonation3(int $campaignId = 0, bool $anonymous = false): Donation
    {
        /** @var  Donation $donation3 */
        $donation3 = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'anonymous' => $anonymous,
            'amount' => new Money(300, 'USD'),
            'feeAmountRecovered' => new Money(30, 'USD'),
            'firstName' => 'C',
            'lastName' => 'C',
            'mode' => DonationMode::LIVE(),
        ]);

        if ($campaignId) {
            give()->payment_meta->update_meta($donation3->id, DonationMetaKeys::CAMPAIGN_ID, $campaignId);
        }

        return $donation3;
    }
}

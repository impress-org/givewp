<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;

/**
 * @since 4.6.0
 */
class DonationRouteUpdateTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @since 4.6.0
     */
    public function testUpdateDonationShouldUpdateModelProperties()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'firstName' => 'Updated First Name',
            'lastName' => 'Updated Last Name',
            'email' => 'updated@test.com',
            'phone' => '1234567890',
            'company' => 'Updated Company',
            'comment' => 'Updated comment',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals('Updated First Name', $data['firstName']);
        $this->assertEquals('Updated Last Name', $data['lastName']);
        $this->assertEquals('updated@test.com', $data['email']);
        $this->assertEquals('1234567890', $data['phone']);
        $this->assertEquals('Updated Company', $data['company']);
        $this->assertEquals('Updated comment', $data['comment']);
    }

    /**
     * @since 4.6.0
     */
    public function testUpdateDonationShouldNotUpdateNonEditableFields()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();
        $originalId = $donation->id;
        $originalPurchaseKey = $donation->purchaseKey;
        $originalDonorIp = $donation->donorIp;
        $originalMode = $donation->mode->getValue();
        $originalType = $donation->type->getValue();
        $originalCampaignId = $donation->campaignId;

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'purchaseKey' => '999999',
            'donorIp' => '192.168.1.1',
            'mode' => 'live',
            'type' => 'renewal',
            'campaignId' => 123,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals($originalId, $data['id']);
        $this->assertEquals($originalPurchaseKey, $data['purchaseKey']);
        $this->assertEquals($originalDonorIp, $data['donorIp']);
        $this->assertEquals($originalMode, $data['mode']);
        $this->assertEquals($originalType, $data['type']);
        $this->assertEquals($originalCampaignId, $data['campaignId']);
    }

    /**
     * @since 4.6.0
     */
    public function testUpdateDonationShouldReturn404ErrorWhenDonationNotFound()
    {
        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/999999';
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'firstName' => 'Updated First Name',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }

    /**
     * @since 4.6.0
     */
    public function testUpdateDonationShouldReturn403ErrorWhenNotAdminUser()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id;
        $request = $this->createRequest('PUT', $route, [], 'subscriber');
        $request->set_body_params([
            'firstName' => 'Updated First Name',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @since 4.6.0
     */
    public function testDeleteDonationShouldDeleteDonation()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();
        $donationId = $donation->id;

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_query_params(['force' => 'true']);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertTrue($data['deleted']);
        $this->assertEquals($donationId, $data['previous']['id']);

        // Verify donation is actually deleted
        $deletedDonation = Donation::find($donationId);
        $this->assertNull($deletedDonation);
    }

        /**
     * @since 4.6.0
     */
    public function testDeleteDonationShouldTrashDonationWhenForceIsFalse()
    {
    /** @var Donation $donation */
    $donation = Donation::factory()->create();

    $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id;
    $request = $this->createRequest('DELETE', $route, ['force' => 'false'], 'administrator');

    $response = $this->dispatchRequest($request);

    $this->assertEquals(200, $response->get_status());

    $trashedDonation = Donation::find($donation->id);
    $this->assertNotNull($trashedDonation);
    $this->assertEquals('trash', $trashedDonation->status->getValue());
    }


    /**
     * @since 4.6.0
     */
    public function testDeleteDonationShouldReturn404ErrorWhenDonationNotFound()
    {
        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/999999';
        $request = $this->createRequest('DELETE', $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }

    /**
     * @since 4.6.0
     */
    public function testDeleteDonationShouldReturn403ErrorWhenNotAdminUser()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id;
        $request = $this->createRequest('DELETE', $route, [], 'subscriber');

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @since 4.6.0
     */
    public function testDeleteMultipleDonationsShouldDeleteAllValidDonations()
    {
        /** @var Donation[] $donations */
        $donations = [
            Donation::factory()->create(),
            Donation::factory()->create(),
            Donation::factory()->create(),
        ];

        $donationIds = array_map(function($donation) {
            return $donation->id;
        }, $donations);

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_query_params(['force' => 'true']);
        $request->set_body_params([
            'ids' => $donationIds,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(3, $data['total_requested']);
        $this->assertEquals(3, $data['total_deleted']);
        $this->assertEquals(0, $data['total_errors']);
        $this->assertCount(3, $data['deleted']);
        $this->assertCount(0, $data['errors']);

        // Verify all donations are actually deleted
        foreach ($donationIds as $id) {
            $deletedDonation = Donation::find($id);
            $this->assertNull($deletedDonation);
        }
    }

    /**
     * @since 4.6.0
     */
    public function testDeleteMultipleDonationsShouldHandleMixedValidAndInvalidIds()
    {
        /** @var Donation[] $donations */
        $donations = [
            Donation::factory()->create(),
            Donation::factory()->create(),
        ];

        $donationIds = [
            $donations[0]->id,
            999999, // Invalid ID
            $donations[1]->id,
        ];

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'administrator');
        $request->set_query_params(['force' => 'true']);
        $request->set_body_params([
            'ids' => $donationIds,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertEquals(3, $data['total_requested']);
        $this->assertEquals(2, $data['total_deleted']);
        $this->assertEquals(1, $data['total_errors']);
        $this->assertCount(2, $data['deleted']);
        $this->assertCount(1, $data['errors']);

        // Verify valid donations are deleted and invalid ID error is reported
        $deletedDonation = Donation::find($donations[0]->id);
        $this->assertNull($deletedDonation);
        $deletedDonation = Donation::find($donations[1]->id);
        $this->assertNull($deletedDonation);
        $this->assertEquals(999999, $data['errors'][0]['id']);
    }

    /**
     * @since 4.6.0
     */
    public function testDeleteMultipleDonationsShouldReturn403ErrorWhenNotAdminUser()
    {
        /** @var Donation[] $donations */
        $donations = [
            Donation::factory()->create(),
            Donation::factory()->create(),
        ];

        $donationIds = array_map(function($donation) {
            return $donation->id;
        }, $donations);

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE;
        $request = $this->createRequest('DELETE', $route, [], 'subscriber');
        $request->set_query_params(['force' => 'true']);
        $request->set_body_params([
            'ids' => $donationIds,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @since 4.6.0
     */
    public function testUpdateDonationShouldAcceptNullValuesForOptionalFields()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id;
        $request = $this->createRequest('PUT', $route, [], 'administrator');
        $request->set_body_params([
            'phone' => null,
            'company' => null,
            'comment' => null,
            'honorific' => null,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertNull($data['phone']);
        $this->assertNull($data['company']);
        $this->assertNull($data['comment']);
        $this->assertNull($data['honorific']);
    }

        /**
     * @since 4.6.0
     */
    public function testRefundDonationShouldRefundDonationSuccessfully()
    {
        /** @var PaymentGatewayRegister $registrar */
        $registrar = give(PaymentGatewayRegister::class);
        if (!$registrar->hasPaymentGateway(TestGateway::id())) {
            $registrar->registerGateway(TestGateway::class);
        }

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayId' => TestGateway::id(),
            'status' => DonationStatus::COMPLETE()
        ]);

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id . '/refund';
        $request = $this->createRequest('POST', $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();

        $this->assertEquals(200, $status);
        $this->assertTrue($data['status']->isRefunded());
    }

    /**
     * @since 4.6.0
     */
    public function testRefundDonationShouldReturn404ErrorWhenDonationNotFound()
    {
        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/999999/refund';
        $request = $this->createRequest('POST', $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(404, $status);
    }

        /**
     * @since 4.6.0
     */
    public function testRefundDonationShouldReturn403ErrorWhenNotAdminUser()
    {
        /** @var PaymentGatewayRegister $registrar */
        $registrar = give(PaymentGatewayRegister::class);
        if (!$registrar->hasPaymentGateway(TestGateway::id())) {
            $registrar->registerGateway(TestGateway::class);
        }

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayId' => TestGateway::id(),
            'status' => DonationStatus::COMPLETE()
        ]);

        $route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE . '/' . $donation->id . '/refund';
        $request = $this->createRequest('POST', $route, [], 'subscriber');

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }
}

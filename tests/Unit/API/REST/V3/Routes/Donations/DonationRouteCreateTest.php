<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Campaigns\Models\Campaign;
use Give\Donors\Models\Donor;
use Give\DonationForms\Models\DonationForm;
use Give\PaymentGateways\Gateways\Offline\OfflineGateway;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Donations\Models\Donation;

/**
 * @since 4.8.0
 */
class DonationRouteCreateTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @var Donor
     */
    protected $donor;

    /**
     * @var DonationForm
     */
    protected $form;

    /**
     * @var string
     */
    protected $route;

    /**
     * Initialize common test objects.
     *
     * @since 4.8.0
     */
    private function initializeTestObjects()
    {
        if (!isset($this->donor)) {
            $this->donor = Donor::factory()->create();
        }
        if (!isset($this->form)) {
            $this->form = DonationForm::factory()->create();

            // Create a campaign and associate it with the form so that campaignId can be auto-generated
            $campaign = Campaign::factory()->create();
            give()->campaigns->addCampaignForm($campaign, $this->form->id);
        }
        if (!isset($this->route)) {
            $this->route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE;
        }
    }

    /**
     * @since 4.8.0
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initializeTestObjects();
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldCreateModelWithRequiredFields()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@test.com',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(201, $status);
        $this->assertEquals($this->donor->id, $data['donorId']);
        $this->assertEquals(100.00, $data['amount']['value']);
        $this->assertEquals('USD', $data['amount']['currency']);
        $this->assertEquals(TestGateway::id(), $data['gatewayId']);
        $this->assertEquals('test', $data['mode']);
        $this->assertEquals('John', $data['firstName']);
        $this->assertEquals('Doe', $data['lastName']);
        $this->assertEquals('john.doe@test.com', $data['email']);
        $this->assertEquals('publish', $data['status']);
        $this->assertEquals('single', $data['type']);
        $this->assertFalse($data['anonymous']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldUseDefaultValuesWhenNotProvided()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 50.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'live',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(201, $status);
        $this->assertEquals('publish', $data['status']);
        $this->assertEquals('single', $data['type']);
        $this->assertFalse($data['anonymous']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldAcceptAllOptionalFields()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 200.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@test.com',
            'phone' => '1234567890',
            'company' => 'Test Company',
            'honorific' => 'Mr.',
            'status' => 'publish',
            'type' => 'single',
            'anonymous' => true,
            'formId' => $this->form->id,
            'formTitle' => $this->form->title,
            'levelId' => 'premium',
            'gatewayTransactionId' => 'txn_123456',
            'exchangeRate' => '1.0',
            'comment' => 'Test donation comment',
            'billingAddress' => [
                'address1' => '123 Test St',
                'address2' => 'Apt 4B',
                'city' => 'Test City',
                'state' => 'TS',
                'country' => 'US',
                'zip' => '12345',
            ],
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(201, $status);
        $this->assertEquals('Jane', $data['firstName']);
        $this->assertEquals('Smith', $data['lastName']);
        $this->assertEquals('jane.smith@test.com', $data['email']);
        $this->assertEquals('1234567890', $data['phone']);
        $this->assertEquals('Test Company', $data['company']);
        $this->assertEquals('Mr.', $data['honorific']);
        $this->assertEquals('publish', $data['status']);
        $this->assertEquals('single', $data['type']);
        $this->assertTrue($data['anonymous']);
        // campaignId is now auto-generated by the repository from the form's associated campaign
        $this->assertNotNull($data['campaignId'], 'campaignId should be auto-generated from the form\'s associated campaign but was: ' . var_export($data['campaignId'], true));
        $this->assertIsInt($data['campaignId']);
        $this->assertEquals($this->form->id, $data['formId']);
        $this->assertEquals($this->form->title, $data['formTitle']);
        $this->assertEquals('premium', $data['levelId']);
        $this->assertEquals('txn_123456', $data['gatewayTransactionId']);
        $this->assertEquals('1.0', $data['exchangeRate']);
        $this->assertEquals('Test donation comment', $data['comment']);
        $this->assertEquals('123 Test St', $data['billingAddress']['address1']);
        $this->assertEquals('Apt 4B', $data['billingAddress']['address2']);
        $this->assertEquals('Test City', $data['billingAddress']['city']);
        $this->assertEquals('TS', $data['billingAddress']['state']);
        $this->assertEquals('US', $data['billingAddress']['country']);
        $this->assertEquals('12345', $data['billingAddress']['zip']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldReturn400ErrorWhenMissingRequiredFields()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(400, $status);
        // The error message should contain "Missing required field: donorId"
        $this->assertStringContainsString('Missing required field: donorId', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldReturn400ErrorWhenDonorIdDoesNotExist()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => 999999,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Failed to create donation', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldReturn403ErrorWhenNotAdminUser()
    {
        $request = $this->createRequest('POST', $this->route, [], 'subscriber');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();

        $this->assertEquals(403, $status);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldHandleInvalidStatusValue()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
            'status' => 'invalid_status',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Invalid parameter(s): status', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldHandleInvalidTypeValue()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
            'type' => 'invalid_type',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Invalid parameter(s): type', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldHandleInvalidModeValue()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'invalid_mode',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Invalid parameter(s): mode', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldHandleInvalidAmountFormat()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => 'invalid_amount',
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Invalid parameter(s): amount', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldHandleInvalidBillingAddressFormat()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
            'billingAddress' => 'invalid_address',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(400, $status);
        $this->assertStringContainsString('Invalid parameter(s): billingAddress', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldIgnoreAutoGeneratedFields()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(201, $status);
        $this->assertNotEquals(999999, $data['id']);
        $this->assertNotEquals('2023-01-01T00:00:00Z', $data['createdAt']['date']);
        $this->assertNotEquals('2023-01-01T00:00:00Z', $data['updatedAt']['date']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldIncludeSensitiveDataInResponse()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@test.com',
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(201, $status);
        $this->assertArrayHasKey('donorIp', $data);
        $this->assertArrayHasKey('purchaseKey', $data);
        $this->assertArrayHasKey('customFields', $data);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldIncludeAnonymousDonationInResponse()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john.doe@test.com',
            'anonymous' => true,
        ]);

        $response = $this->dispatchRequest($request);

        $status = $response->get_status();
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        $this->assertEquals(201, $status);
        $this->assertTrue($data['anonymous']);
        $this->assertArrayHasKey('firstName', $data);
        $this->assertArrayHasKey('lastName', $data);
        $this->assertArrayHasKey('email', $data);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldReturn400ErrorWhenSubscriptionIdProvidedButTypeIsSingle()
    {
        $subscription = Subscription::factory()->create();

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'subscriptionId' => $subscription->id,
            'type' => 'single',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertStringContainsString('When subscriptionId is provided, type must be "subscription" or "renewal"', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldReturn400ErrorWhenSubscriptionIdZeroButTypeIsSubscription()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'subscriptionId' => 0,
            'type' => 'subscription',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertStringContainsString('When subscriptionId is zero, type can only be "single"', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldReturn404ErrorWhenSubscriptionNotFound()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'subscriptionId' => 99999,
            'type' => 'renewal',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(404, $response->get_status());
        $data = $response->get_data();
        $this->assertStringContainsString('Subscription not found', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldReturn400ErrorWhenSubscriptionDonationAlreadyExists()
    {
        $subscription = Subscription::factory()->createWithDonation();

        // Try to create another subscription donation for the same subscription
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'subscriptionId' => $subscription->id,
            'type' => 'subscription',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertStringContainsString('A subscription donation already exists for this subscription', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldReturn400ErrorWhenSubscriptionInstallmentsExceeded()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 1, // Only 1 installment allowed
        ]);

        // Try to create another donation (should fail)
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertStringContainsString('Cannot create donation: subscription installments limit reached', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldSucceedWithValidSubscriptionData()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 5, // Allow multiple installments
        ]);

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals($subscription->id, $data['subscriptionId']);
        $this->assertEquals('renewal', $data['type']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldSucceedWithValidRenewalData()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 5, // Allow multiple installments
        ]);

        // Create renewal donation
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals($subscription->id, $data['subscriptionId']);
        $this->assertEquals('renewal', $data['type']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldSetTypeToSingleWhenNotProvided()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            // Don't set type or subscriptionId
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals('single', $data['type']);
        $this->assertEquals(0, $data['subscriptionId']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateRenewalShouldSucceedWithMinimalParameters()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 5, // Allow multiple installments
        ]);

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals($subscription->id, $data['subscriptionId']);
        $this->assertEquals('renewal', $data['type']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionDonationShouldSucceedWhenGatewayMatches()
    {
        $subscription = Subscription::factory()->create([
            'gatewayId' => TestGateway::id(),
        ]);

        $subscription->gatewayId = TestGateway::id();
        $subscription->save();
        $subscription = Subscription::find($subscription->id);

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => ['amount' => 100.00, 'currency' => 'USD'],
            'gatewayId' => TestGateway::id(), // Same gateway
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'subscriptionId' => $subscription->id,
            'type' => 'subscription',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals($subscription->id, $data['subscriptionId']);
        $this->assertEquals('subscription', $data['type']);
        $this->assertEquals(TestGateway::id(), $data['gatewayId']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateRenewalDonationShouldReturn400ErrorWhenGatewayMismatch()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'installments' => 12, // Set higher installments to avoid limit
        ]);

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
            'gatewayId' => OfflineGateway::id(), // Different gateway
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(400, $response->get_status());
        $data = $response->get_data();
        $this->assertStringContainsString('Gateway ID must match the subscription gateway for subscription and renewal donations', $data['message']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateRenewalDonationShouldSucceedWhenGatewayMatches()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'installments' => 12, // Set higher installments to avoid limit
        ]);

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
            'gatewayId' => TestGateway::id(), // Same gateway
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals($subscription->id, $data['subscriptionId']);
        $this->assertEquals('renewal', $data['type']);
        $this->assertEquals(TestGateway::id(), $data['gatewayId']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateDonationWithCustomDatesShouldSucceed()
    {
        $customCreatedAt = [
            'date' => '2023-01-15T10:30:00',
            'timezone' => 'America/New_York',
            'timezone_type' => 2,
        ];
        $customUpdatedAt = [
            'date' => '2023-01-15T11:45:00',
            'timezone' => 'America/New_York',
            'timezone_type' => 2,
        ];

        // Convert arrays to DateTime objects for comparison
        $expectedCreatedAt = new \DateTime($customCreatedAt['date'], new \DateTimeZone($customCreatedAt['timezone']));
        $expectedUpdatedAt = new \DateTime($customUpdatedAt['date'], new \DateTimeZone($customUpdatedAt['timezone']));

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@test.com',
            'createdAt' => $customCreatedAt,
            'updatedAt' => $customUpdatedAt,
        ]);

        $response = $this->dispatchRequest($request);

        if ($response->get_status() !== 201) {
            $data = $response->get_data();
            $this->fail('Expected status 201, got ' . $response->get_status() . '. Error: ' . json_encode($data));
        }

        $data = $response->get_data();

        // Verify that the custom dates were set correctly
        $this->assertInstanceOf(\DateTime::class, $data['createdAt']);
        $this->assertInstanceOf(\DateTime::class, $data['updatedAt']);

        // Compare the returned dates with the expected DateTime objects
        $this->assertEquals($expectedCreatedAt->format('Y-m-d H:i:s'), $data['createdAt']->format('Y-m-d H:i:s'));
        $this->assertEquals($expectedUpdatedAt->format('Y-m-d H:i:s'), $data['updatedAt']->format('Y-m-d H:i:s'));
    }

    /**
     * @since 4.8.0
     */
    public function testCreateRenewalWithCustomDatesShouldSucceed()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'installments' => 12, // Set higher installments to avoid limit
        ]);

        $customCreatedAt = [
            'date' => '2023-02-20T14:20:00',
            'timezone' => 'America/New_York',
            'timezone_type' => 2,
        ];
        $customUpdatedAt = [
            'date' => '2023-02-20T15:30:00',
            'timezone' => 'America/New_York',
            'timezone_type' => 2,
        ];

        // Convert arrays to DateTime objects for comparison
        $expectedCreatedAt = new \DateTime($customCreatedAt['date'], new \DateTimeZone($customCreatedAt['timezone']));
        $expectedUpdatedAt = new \DateTime($customUpdatedAt['date'], new \DateTimeZone($customUpdatedAt['timezone']));

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
            'gatewayId' => TestGateway::id(),
            'createdAt' => $customCreatedAt,
            'updatedAt' => $customUpdatedAt,
        ]);

        $response = $this->dispatchRequest($request);

        if ($response->get_status() !== 201) {
            $data = $response->get_data();
            $this->fail('Expected status 201, got ' . $response->get_status() . '. Error: ' . json_encode($data));
        }

        $data = $response->get_data();

        // Verify that the custom dates were set correctly
        $this->assertInstanceOf(\DateTime::class, $data['createdAt']);
        $this->assertInstanceOf(\DateTime::class, $data['updatedAt']);

        // Compare the returned dates with the expected DateTime objects
        $this->assertEquals($expectedCreatedAt->format('Y-m-d H:i:s'), $data['createdAt']->format('Y-m-d H:i:s'));
        $this->assertEquals($expectedUpdatedAt->format('Y-m-d H:i:s'), $data['updatedAt']->format('Y-m-d H:i:s'));

        $this->assertEquals($subscription->id, $data['subscriptionId']);
        $this->assertEquals('renewal', $data['type']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateRenewalWithUpdateRenewalDateShouldUpdateSubscriptionRenewalDate()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'installments' => 12, // Set higher installments to avoid limit
        ]);

        $customCreatedAt = [
            'date' => '2023-03-25T16:30:00',
            'timezone' => 'America/New_York',
            'timezone_type' => 2,
        ];

        // Convert array to DateTime object for comparison
        $baseRenewalDate = new \DateTime($customCreatedAt['date'], new \DateTimeZone($customCreatedAt['timezone']));

        // Calculate expected renewal date based on subscription period and frequency
        // The bumpRenewalDate() will calculate the next renewal date from the base date
        $expectedRenewalDate = clone $baseRenewalDate;

        // Get the actual subscription period from the subscription
        $period = $subscription->period ?? 'month';
        $frequency = $subscription->frequency ?? 1;

        // Calculate the next renewal date based on the actual subscription settings
        $expectedRenewalDate->modify("+{$frequency} {$period}");

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
            'gatewayId' => TestGateway::id(),
            'createdAt' => $customCreatedAt,
            'updateRenewalDate' => true,
        ]);

        $response = $this->dispatchRequest($request);

        if ($response->get_status() !== 201) {
            $data = $response->get_data();
            $this->fail('Expected status 201, got ' . $response->get_status() . '. Error: ' . json_encode($data));
        }

        $data = $response->get_data();

        // Verify that the donation was created successfully
        $this->assertEquals($subscription->id, $data['subscriptionId']);
        $this->assertEquals('renewal', $data['type']);

        // Verify that the subscription renewal date was updated
        $updatedSubscription = Subscription::find($subscription->id);
        $this->assertNotNull($updatedSubscription->renewsAt);
        $this->assertEquals($expectedRenewalDate->format('Y-m-d H:i:s'), $updatedSubscription->renewsAt->format('Y-m-d H:i:s'));
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSubscriptionWithUpdateRenewalDateShouldNotUpdateSubscriptionRenewalDate()
    {
        $subscription = Subscription::factory()->create([
            'gatewayId' => TestGateway::id(),
        ]);

        // Store the original renewal date
        $originalRenewalDate = $subscription->renewsAt;

        $customCreatedAt = [
            'date' => '2023-04-10T09:15:00',
            'timezone' => 'America/New_York',
            'timezone_type' => 2,
        ];

        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'subscriptionId' => $subscription->id,
            'type' => 'subscription',
            'createdAt' => $customCreatedAt,
            'updateRenewalDate' => true,
        ]);

        $response = $this->dispatchRequest($request);

        if ($response->get_status() !== 201) {
            $data = $response->get_data();
            $this->fail('Expected status 201, got ' . $response->get_status() . '. Error: ' . json_encode($data));
        }

        $data = $response->get_data();

        // Verify that the donation was created successfully
        $this->assertEquals($subscription->id, $data['subscriptionId']);
        $this->assertEquals('subscription', $data['type']);

        // Verify that the subscription renewal date was NOT updated (should remain the same)
        $updatedSubscription = Subscription::find($subscription->id);
        $this->assertNotNull($updatedSubscription->renewsAt);
        $this->assertEquals($originalRenewalDate->format('Y-m-d H:i:s'), $updatedSubscription->renewsAt->format('Y-m-d H:i:s'));
    }

    /**
     * @since 4.8.0
     */
    public function testCreateSingleDonationWithUpdateRenewalDateShouldNotUpdateSubscriptionRenewalDate()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
            'createdAt' => [
                'date' => '2023-05-20T12:00:00',
                'timezone' => 'America/New_York',
                'timezone_type' => 2,
            ],
            'updateRenewalDate' => true,
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();

        // Verify that the donation was created successfully as a single donation
        $this->assertEquals('single', $data['type']);
        $this->assertEquals(0, $data['subscriptionId']);
    }



    /**
     * @since 4.8.0
     */
    public function testCreateDonationShouldAutoGenerateCampaignId()
    {
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'donorId' => $this->donor->id,
            'amount' => [
                'amount' => 100.00,
                'currency' => 'USD',
            ],
            'gatewayId' => TestGateway::id(),
            'mode' => 'test',
            'formId' => $this->form->id,
            'firstName' => 'John',
            'email' => 'john@example.com',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();

        // Verify that campaignId is auto-generated by the repository from the form's associated campaign
        $this->assertNotNull($data['campaignId'], 'campaignId should be auto-generated from the form\'s associated campaign');
        $this->assertIsInt($data['campaignId']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateRenewalShouldUseCampaignIdFromInitialDonation()
    {
        // Create a campaign and associate it with the form
        $campaign = Campaign::factory()->create();
        give()->campaigns->addCampaignForm($campaign, $this->form->id);

        // Create a subscription with an initial donation that has a campaignId
        $initialCampaignId = 123;
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'installments' => 12, // Set higher installments to avoid limit
        ]);

        // Update the initial donation to have a specific campaignId
        $initialDonation = $subscription->initialDonation();
        $initialDonation->campaignId = $initialCampaignId;
        $initialDonation->save();

        // Create renewal request
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();

        // Verify that the renewal has the same campaignId as the initial donation
        $this->assertEquals($initialCampaignId, $data['campaignId']);
        $this->assertEquals('renewal', $data['type']);
        $this->assertEquals($subscription->id, $data['subscriptionId']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateRenewalShouldAutoGenerateCampaignId()
    {
        // Create a campaign and associate it with the form
        $campaign = \Give\Campaigns\Models\Campaign::factory()->create();
        give()->campaigns->addCampaignForm($campaign, $this->form->id);

        // Create a subscription with an initial donation that has a campaignId
        $initialCampaignId = 123;
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'installments' => 12, // Set higher installments to avoid limit
        ]);

        // Update the initial donation to have a specific campaignId
        $initialDonation = $subscription->initialDonation();
        $initialDonation->campaignId = $initialCampaignId;
        $initialDonation->save();

        // Create renewal request (campaignId is now auto-generated)
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();

        // Verify that campaignId is auto-generated by the repository from the form's associated campaign
        $this->assertNotNull($data['campaignId'], 'campaignId should be auto-generated from the form\'s associated campaign');
        $this->assertIsInt($data['campaignId']);
        $this->assertEquals('renewal', $data['type']);
        $this->assertEquals($subscription->id, $data['subscriptionId']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateRenewalShouldUseFormCampaignIdWhenInitialDonationHasNoCampaignId()
    {
        // Create a subscription first to get its form ID
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'installments' => 12, // Set higher installments to avoid limit
        ]);

        // Create a campaign and associate it with the subscription's form
        $campaign = Campaign::factory()->create();
        give()->campaigns->addCampaignForm($campaign, $subscription->donationFormId);

        // Update the initial donation to have no campaignId
        $initialDonation = $subscription->initialDonation();
        $initialDonation->campaignId = 0; // No campaignId
        $initialDonation->save();

        // Create renewal request
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();

        // Verify that the renewal gets a campaignId (auto-generated from the form's associated campaign)
        $this->assertNotNull($data['campaignId'], 'campaignId should be auto-generated from the form\'s associated campaign');
        $this->assertIsInt($data['campaignId']);
        $this->assertEquals('renewal', $data['type']);
        $this->assertEquals($subscription->id, $data['subscriptionId']);
    }

    /**
     * @since 4.8.0
     */
    public function testCreateRenewalShouldAllowNewAmountValue()
    {
        // Create a subscription with an initial donation
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'installments' => 12, // Set higher installments to avoid limit
        ]);

        // Get the initial donation amount for comparison
        $initialDonation = $subscription->initialDonation();
        $initialAmount = $initialDonation->amount->getAmount();

        // Create renewal request with a different amount
        $newAmount = 150.00; // Different from initial amount
        $request = $this->createRequest('POST', $this->route, [], 'administrator');
        $request->set_body_params([
            'subscriptionId' => $subscription->id,
            'type' => 'renewal',
            'amount' => [
                'amount' => $newAmount,
                'currency' => 'USD',
            ],
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();
        $dataJson = json_encode($data);
        $data = json_decode($dataJson, true);

        // Verify that the renewal was created successfully
        $this->assertEquals('renewal', $data['type']);
        $this->assertEquals($subscription->id, $data['subscriptionId']);

        // Verify that the renewal has the new amount
        $this->assertEquals($newAmount, $data['amount']['value']);
        $this->assertEquals('USD', $data['amount']['currency']);

        // Verify that the amount is different from the initial donation
        $this->assertNotEquals($initialAmount, $data['amount']['value']);

        // Verify that the renewal amount is correctly formatted
        $this->assertIsArray($data['amount']);
        $this->assertArrayHasKey('value', $data['amount']);
        $this->assertArrayHasKey('currency', $data['amount']);
    }
}

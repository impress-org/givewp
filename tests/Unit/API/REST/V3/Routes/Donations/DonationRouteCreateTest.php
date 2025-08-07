<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donors\Models\Donor;
use Give\DonationForms\Models\DonationForm;
use Give\PaymentGateways\Gateways\Offline\OfflineGateway;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;

/**
 * @unreleased
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
     * @unreleased
     */
    private function initializeTestObjects()
    {
        if (!isset($this->donor)) {
            $this->donor = Donor::factory()->create();
        }
        if (!isset($this->form)) {
            $this->form = DonationForm::factory()->create();
        }
        if (!isset($this->route)) {
            $this->route = '/' . DonationRoute::NAMESPACE . '/' . DonationRoute::BASE;
        }
    }

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initializeTestObjects();
    }

    /**
     * @unreleased
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
        $this->assertEquals('pending', $data['status']);
        $this->assertEquals('single', $data['type']);
        $this->assertFalse($data['anonymous']);
    }

    /**
     * @unreleased
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
        $this->assertEquals('pending', $data['status']);
        $this->assertEquals('single', $data['type']);
        $this->assertFalse($data['anonymous']);
    }

    /**
     * @unreleased
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
            'campaignId' => 123,
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
        $this->assertEquals(123, $data['campaignId']);
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
} 
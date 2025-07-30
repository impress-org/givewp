<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Donations;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donors\Models\Donor;
use Give\DonationForms\Models\DonationForm;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
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
            'type' => 'subscription',
            'anonymous' => true,
            'campaignId' => 123,
            'formId' => $this->form->id,
            'formTitle' => $this->form->title,
            'subscriptionId' => 789,
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
        $this->assertEquals('subscription', $data['type']);
        $this->assertTrue($data['anonymous']);
        $this->assertEquals(123, $data['campaignId']);
        $this->assertEquals($this->form->id, $data['formId']);
        $this->assertEquals($this->form->title, $data['formTitle']);
        $this->assertEquals(789, $data['subscriptionId']);
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
} 
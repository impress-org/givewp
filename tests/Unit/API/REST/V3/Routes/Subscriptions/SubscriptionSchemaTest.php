<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Subscriptions;

use Exception;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\API\REST\V3\SchemaValidationTrait;
use WP_REST_Server;

/**
 * @unreleased
 */
class SubscriptionSchemaTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;
    use SchemaValidationTrait;

    /**
     * @unreleased
     */
    public function testSubscriptionSchemaShouldMatchActualResponse()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        /** @var Subscription $subscription */
        $subscription = $this->createSubscription();

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);
        $test = $this->responseToData($schemaResponse, true);

        // Get actual subscription data
        $dataRoute = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
        $dataRequest->set_query_params(['includeSensitiveData' => true]);
        $dataResponse = $this->dispatchRequest($dataRequest);
        $actualDataJson = json_encode($dataResponse->get_data());
        $actualData = json_decode($actualDataJson, true);

        // Validate that all required schema properties exist in actual response
        $this->validateSchemaProperties($schema, $actualData);

        // Validate data types match schema
        $this->validateDataTypes($schema, $actualData);

        // Validate enum values match schema
        $this->validateEnumValues($schema, $actualData);
    }

    /**
     * @unreleased
     */
    public function testSubscriptionCollectionSchemaShouldMatchActualResponse()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        /** @var Subscription $subscription */
        $subscription = $this->createSubscription();

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schemaJson = json_encode($schemaResponse->get_data());
        $schema = json_decode($schemaJson, true);

        // Get actual collection data
        $dataRoute = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE;
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
        $dataRequest->set_query_params(['includeSensitiveData' => true]);
        $dataResponse = $this->dispatchRequest($dataRequest);
        $actualDataJson = json_encode($dataResponse->get_data());
        $actualData = json_decode($actualDataJson, true);

        // Assert that we have data in the collection
        $this->assertNotEmpty($actualData, 'Collection should contain at least one subscription');
        $this->assertIsArray($actualData, 'Collection should be an array');

        // Validate first item in collection
        if (!empty($actualData)) {
            $firstItem = $actualData[0];
            $this->validateSchemaProperties($schema, $firstItem);
            $this->validateDataTypes($schema, $firstItem);
            $this->validateEnumValues($schema, $firstItem);
        }
    }

    /**
     * @unreleased
     */
    public function testDateFormatsShouldBeConsistentWithWordPressStandards()
    {
        DB::query("DELETE FROM " . DB::prefix('give_subscriptions'));

        /** @var Subscription $subscription */
        $subscription = $this->createSubscription();

        $route = '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscription->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(['includeSensitiveData' => true]);
        $response = $this->dispatchRequest($request);
        $dataJson = json_encode($response->get_data());
        $data = json_decode($dataJson, true);

        // Check if dates are in WordPress standard format (ISO 8601 without timezone)
        if (isset($data['createdAt'])) {
            $this->validateDateFormat($data['createdAt'], 'createdAt');
        }

        if (isset($data['renewsAt'])) {
            $this->validateDateFormat($data['renewsAt'], 'renewsAt');
        }
    }

    /**
     * @unreleased
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

<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Subscriptions;

use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * @unreleased
 */
class SubscriptionSchemaTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @unreleased
     */
    public function testSubscriptionSchemaShouldMatchActualResponse()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::ACTIVE()]);

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . SubscriptionRoute::NAMESPACE . '/subscriptions';
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schema = $schemaResponse->get_data();

        // Get actual subscription data
        $dataRoute = '/' . SubscriptionRoute::NAMESPACE . '/subscriptions/' . $subscription->id;
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
        $dataRequest->set_query_params(['includeSensitiveData' => true]);
        $dataResponse = $this->dispatchRequest($dataRequest);
        $actualData = $dataResponse->get_data();

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
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::ACTIVE()]);

        // Get the schema via OPTIONS request
        $schemaRoute = '/' . SubscriptionRoute::NAMESPACE . '/subscriptions';
        $schemaRequest = $this->createRequest('OPTIONS', $schemaRoute, [], 'administrator');
        $schemaResponse = $this->dispatchRequest($schemaRequest);
        $schema = $schemaResponse->get_data();

        // Get actual collection data
        $dataRoute = '/' . SubscriptionRoute::NAMESPACE . '/subscriptions';
        $dataRequest = $this->createRequest(WP_REST_Server::READABLE, $dataRoute, [], 'administrator');
        $dataRequest->set_query_params(['includeSensitiveData' => true]);
        $dataResponse = $this->dispatchRequest($dataRequest);
        $actualData = $dataResponse->get_data();

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
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create(['status' => SubscriptionStatus::ACTIVE()]);

        $route = '/' . SubscriptionRoute::NAMESPACE . '/subscriptions/' . $subscription->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(['includeSensitiveData' => true]);
        $response = $this->dispatchRequest($request);
        $data = $response->get_data();

        // Check if dates are in WordPress standard format (RFC3339/ISO8601)
        if (isset($data['createdAt'])) {
            $this->validateDateFormat($data['createdAt'], 'createdAt');
        }

        if (isset($data['renewsAt'])) {
            $this->validateDateFormat($data['renewsAt'], 'renewsAt');
        }
    }

    /**
     * @unreleased
     */
    private function validateSchemaProperties($schema, $actualData)
    {
        if (!isset($schema['schema']['properties'])) {
            $this->fail('Schema does not contain properties');
        }

        $schemaProperties = $schema['schema']['properties'];
        $requiredProperties = $schema['schema']['required'] ?? [];

        // Check that all required properties exist
        foreach ($requiredProperties as $requiredProperty) {
            $this->assertArrayHasKey(
                $requiredProperty,
                $actualData,
                "Required property '{$requiredProperty}' is missing from response"
            );
        }

        // Check that all schema properties that exist in actual data are properly defined
        foreach ($actualData as $property => $value) {
            if (isset($schemaProperties[$property])) {
                // Property exists in schema, which is good
                continue;
            }

            // Allow some dynamic properties that might not be in schema
            $allowedDynamicProperties = ['_links', 'customFields'];
            if (!in_array($property, $allowedDynamicProperties)) {
                $this->fail("Property '{$property}' exists in response but not in schema");
            }
        }
    }

    /**
     * @unreleased
     */
    private function validateDataTypes($schema, $actualData)
    {
        $schemaProperties = $schema['schema']['properties'];

        foreach ($actualData as $property => $value) {
            if (!isset($schemaProperties[$property])) {
                continue; // Skip properties not in schema
            }

            // Skip properties that don't have a type (like objects with properties)
            if (!isset($schemaProperties[$property]['type'])) {
                continue;
            }

            $expectedType = $schemaProperties[$property]['type'];
            $actualType = $this->getActualType($value);

            // Handle oneOf schemas (like dates that can be string or object)
            if (isset($schemaProperties[$property]['oneOf'])) {
                $this->validateOneOfSchema($schemaProperties[$property]['oneOf'], $value, $property);
                continue;
            }

            // Handle array types
            if (is_array($expectedType)) {
                $this->assertContains(
                    $actualType,
                    $expectedType,
                    "Property '{$property}' type '{$actualType}' should be one of: " . implode(', ', $expectedType)
                );
            } else {
                $this->assertEquals(
                    $expectedType,
                    $actualType,
                    "Property '{$property}' type mismatch. Expected: {$expectedType}, Actual: {$actualType}"
                );
            }
        }
    }

    /**
     * @unreleased
     */
    private function validateEnumValues($schema, $actualData)
    {
        $schemaProperties = $schema['schema']['properties'];

        foreach ($actualData as $property => $value) {
            if (!isset($schemaProperties[$property]['enum'])) {
                continue;
            }

            $allowedValues = $schemaProperties[$property]['enum'];
            $this->assertContains(
                $value,
                $allowedValues,
                "Property '{$property}' value '{$value}' is not in allowed enum values: " . implode(', ', $allowedValues)
            );
        }
    }

    /**
     * @unreleased
     */
    private function validateOneOfSchema($oneOfSchemas, $value, $propertyName)
    {
        $validTypes = [];
        foreach ($oneOfSchemas as $schema) {
            $validTypes[] = $schema['type'];
        }

        $actualType = $this->getActualType($value);
        $this->assertContains(
            $actualType,
            $validTypes,
            "Property '{$propertyName}' type '{$actualType}' should be one of: " . implode(', ', $validTypes)
        );
    }

    /**
     * @unreleased
     */
    private function validateDateFormat($dateValue, $propertyName)
    {
        if (is_string($dateValue)) {
            // Should be RFC3339/ISO8601 format
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/',
                $dateValue,
                "Property '{$propertyName}' should be in RFC3339 format when returned as string"
            );
        } elseif (is_array($dateValue)) {
            // Should have date, timezone, and timezone_type keys
            $this->assertArrayHasKey('date', $dateValue, "Property '{$propertyName}' array should have 'date' key");
            $this->assertArrayHasKey('timezone', $dateValue, "Property '{$propertyName}' array should have 'timezone' key");
            $this->assertArrayHasKey('timezone_type', $dateValue, "Property '{$propertyName}' array should have 'timezone_type' key");

            // The date should be in RFC3339 format
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/',
                $dateValue['date'],
                "Property '{$propertyName}' date should be in RFC3339 format"
            );
        } else {
            $this->fail("Property '{$propertyName}' should be either string or array, got " . gettype($dateValue));
        }
    }

    /**
     * @unreleased
     */
    private function getActualType($value)
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return 'boolean';
        }
        if (is_int($value)) {
            return 'integer';
        }
        if (is_float($value)) {
            return 'number';
        }
        if (is_string($value)) {
            return 'string';
        }
        if (is_array($value)) {
            return 'array';
        }
        if (is_object($value)) {
            return 'object';
        }

        return gettype($value);
    }
}

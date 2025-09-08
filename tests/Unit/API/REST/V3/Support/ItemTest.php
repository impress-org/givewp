<?php

namespace Give\Tests\Unit\API\REST\V3\Support;

use DateTime;
use Give\API\REST\V3\Support\Item;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class ItemTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testFormatForResponseShouldFormatSpecifiedDateFields()
    {
        $item = [
            'id' => 123,
            'createdAt' => new DateTime('2023-12-25 14:30:00'),
            'updatedAt' => new DateTime('2023-12-26 15:45:00'),
            'renewsAt' => new DateTime('2024-01-01 00:00:00'),
            'name' => 'Test Subscription',
            'amount' => 100.00,
        ];

        $dateFields = ['createdAt', 'updatedAt', 'renewsAt'];
        $valueObjectFields = [];

        $formatted = Item::formatForResponse($item, $dateFields, $valueObjectFields);

        // Date fields should be formatted to WordPress-compatible ISO 8601 strings (without timezone)
        $this->assertIsString($formatted['createdAt']);
        $this->assertEquals('2023-12-25T14:30:00', $formatted['createdAt']);
        $this->assertIsString($formatted['updatedAt']);
        $this->assertEquals('2023-12-26T15:45:00', $formatted['updatedAt']);
        $this->assertIsString($formatted['renewsAt']);
        $this->assertEquals('2024-01-01T00:00:00', $formatted['renewsAt']);

        // Non-date fields should remain unchanged
        $this->assertEquals(123, $formatted['id']);
        $this->assertEquals('Test Subscription', $formatted['name']);
        $this->assertEquals(100.00, $formatted['amount']);
    }

    /**
     * @unreleased
     */
    public function testFormatForResponseShouldFormatSpecifiedValueObjects()
    {
        $item = [
            'id' => 123,
            'status' => new SubscriptionStatus('active'),
            'period' => new SubscriptionPeriod('month'),
            'name' => 'Test Subscription',
            'amount' => 100.00,
        ];

        $dateFields = [];
        $valueObjectFields = ['status', 'period'];

        $formatted = Item::formatForResponse($item, $dateFields, $valueObjectFields);

        // Value objects should be converted to their string values
        $this->assertEquals('active', $formatted['status']);
        $this->assertEquals('month', $formatted['period']);

        // Non-value object fields should remain unchanged
        $this->assertEquals(123, $formatted['id']);
        $this->assertEquals('Test Subscription', $formatted['name']);
        $this->assertEquals(100.00, $formatted['amount']);
    }

    /**
     * @unreleased
     */
    public function testFormatForResponseShouldHandleMixedDataTypes()
    {
        $item = [
            'id' => 123,
            'createdAt' => new DateTime('2023-12-25 14:30:00'),
            'status' => new SubscriptionStatus('active'),
            'updatedAt' => new DateTime('2023-12-26 15:45:00'),
            'period' => new SubscriptionPeriod('month'),
            'name' => 'Test Subscription',
        ];

        $dateFields = ['createdAt', 'updatedAt'];
        $valueObjectFields = ['status', 'period'];

        $formatted = Item::formatForResponse($item, $dateFields, $valueObjectFields);

        // Date fields should be formatted
        $this->assertIsString($formatted['createdAt']);
        $this->assertEquals('2023-12-25T14:30:00', $formatted['createdAt']);
        $this->assertIsString($formatted['updatedAt']);
        $this->assertEquals('2023-12-26T15:45:00', $formatted['updatedAt']);

        // Value objects should be converted
        $this->assertEquals('active', $formatted['status']);
        $this->assertEquals('month', $formatted['period']);

        // Regular fields should remain unchanged
        $this->assertEquals(123, $formatted['id']);
        $this->assertEquals('Test Subscription', $formatted['name']);
    }

    /**
     * @unreleased
     */
    public function testFormatDatesForResponseShouldFormatDateTimeObjects()
    {
        $item = [
            'createdAt' => new DateTime('2023-12-25 14:30:00'),
            'updatedAt' => new DateTime('2023-12-26 15:45:00'),
            'name' => 'Test Item',
        ];

        $dateFields = ['createdAt', 'updatedAt'];
        $formatted = Item::formatDatesForResponse($item, $dateFields);

        $this->assertIsString($formatted['createdAt']);
        $this->assertEquals('2023-12-25T14:30:00', $formatted['createdAt']);
        $this->assertIsString($formatted['updatedAt']);
        $this->assertEquals('2023-12-26T15:45:00', $formatted['updatedAt']);
        $this->assertEquals('Test Item', $formatted['name']);
    }

    /**
     * @unreleased
     */
    public function testFormatDatesForResponseShouldHandleNullValues()
    {
        $item = [
            'createdAt' => null,
            'updatedAt' => new DateTime('2023-12-26 15:45:00'),
            'name' => 'Test Item',
        ];

        $dateFields = ['createdAt', 'updatedAt'];
        $formatted = Item::formatDatesForResponse($item, $dateFields);

        $this->assertNull($formatted['createdAt']);
        $this->assertIsString($formatted['updatedAt']);
        $this->assertEquals('2023-12-26T15:45:00', $formatted['updatedAt']);
        $this->assertEquals('Test Item', $formatted['name']);
    }

    /**
     * @unreleased
     */
    public function testFormatValueObjectsForResponseShouldConvertValueObjects()
    {
        $item = [
            'status' => new SubscriptionStatus('active'),
            'period' => new SubscriptionPeriod('month'),
            'name' => 'Test Item',
        ];

        $valueObjectFields = ['status', 'period'];
        $formatted = Item::formatValueObjectsForResponse($item, $valueObjectFields);

        $this->assertEquals('active', $formatted['status']);
        $this->assertEquals('month', $formatted['period']);
        $this->assertEquals('Test Item', $formatted['name']);
    }
}

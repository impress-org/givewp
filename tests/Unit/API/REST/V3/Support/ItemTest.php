<?php

namespace Give\Tests\Unit\API\REST\V3\Support;

use DateTime;
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
    public function testFormatAllForResponseShouldAutoDetectDateFields()
    {
        $data = [
            'id' => 123,
            'createdAt' => new DateTime('2023-12-25 14:30:00'),
            'updatedAt' => '2023-12-26 15:45:00',
            'renewsAt' => new DateTime('2024-01-01 00:00:00'),
            'name' => 'Test Subscription',
            'amount' => 100.00,
        ];

        $formatted = \Give\API\REST\V3\Support\Item::formatAllForResponse($data);

        // Date fields should be formatted to ISO 8601 strings
        $this->assertIsString($formatted['createdAt']);
        $this->assertStringContainsString('2023-12-25T14:30:00', $formatted['createdAt']);
        $this->assertIsString($formatted['updatedAt']);
        $this->assertStringContainsString('2023-12-26T15:45:00', $formatted['updatedAt']);
        $this->assertIsString($formatted['renewsAt']);
        $this->assertStringContainsString('2024-01-01T00:00:00', $formatted['renewsAt']);

        // Non-date fields should remain unchanged
        $this->assertEquals(123, $formatted['id']);
        $this->assertEquals('Test Subscription', $formatted['name']);
        $this->assertEquals(100.00, $formatted['amount']);
    }

    /**
     * @unreleased
     */
    public function testFormatAllForResponseShouldAutoDetectValueObjects()
    {
        $data = [
            'id' => 123,
            'status' => new SubscriptionStatus('active'),
            'period' => new SubscriptionPeriod('month'),
            'name' => 'Test Subscription',
            'amount' => 100.00,
        ];

        $formatted = \Give\API\REST\V3\Support\Item::formatAllForResponse($data);

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
    public function testFormatAllForResponseShouldHandleMixedDataTypes()
    {
        $data = [
            'id' => 123,
            'createdAt' => new DateTime('2023-12-25 14:30:00'),
            'status' => new SubscriptionStatus('active'),
            'updatedAt' => '2023-12-26 15:45:00',
            'period' => new SubscriptionPeriod('month'),
            'name' => 'Test Subscription',
        ];

        $formatted = \Give\API\REST\V3\Support\Item::formatAllForResponse($data);

        // Date fields should be formatted
        $this->assertIsString($formatted['createdAt']);
        $this->assertStringContainsString('2023-12-25T14:30:00', $formatted['createdAt']);
        $this->assertIsString($formatted['updatedAt']);
        $this->assertStringContainsString('2023-12-26T15:45:00', $formatted['updatedAt']);

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
    public function testFormatDateForResponseShouldHandleDateTimeObjects()
    {
        $dateTime = new DateTime('2023-12-25 14:30:00');
        $formatted = \Give\API\REST\V3\Support\Item::formatDateForResponse($dateTime);

        $this->assertIsString($formatted);
        $this->assertStringContainsString('2023-12-25T14:30:00', $formatted);
    }

    /**
     * @unreleased
     */
    public function testFormatDateForResponseShouldHandleStringDates()
    {
        $dateString = '2023-12-25 14:30:00';
        $formatted = \Give\API\REST\V3\Support\Item::formatDateForResponse($dateString);

        $this->assertIsString($formatted);
        $this->assertStringContainsString('2023-12-25T14:30:00', $formatted);
    }

    /**
     * @unreleased
     */
    public function testFormatDateForResponseShouldHandleNullValues()
    {
        $formatted = \Give\API\REST\V3\Support\Item::formatDateForResponse(null);
        $this->assertNull($formatted);
    }
}

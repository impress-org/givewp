<?php

namespace Give\Tests\Unit\API\REST\V3\Support;

use DateTime;
use Give\API\REST\V3\Support\Formatter;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class FormatterTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testFormatAllShouldAutoDetectDateFields()
    {
        $data = [
            'id' => 123,
            'createdAt' => new DateTime('2023-12-25 14:30:00'),
            'updatedAt' => '2023-12-26 15:45:00',
            'renewsAt' => new DateTime('2024-01-01 00:00:00'),
            'name' => 'Test Subscription',
            'amount' => 100.00,
        ];

        $formatted = Formatter::formatAll($data);

        // Date fields should be formatted to ISO 8601
        $this->assertIsString($formatted['createdAt']);
        $this->assertStringStartsWith('2023-12-25T14:30:00', $formatted['createdAt']);

        $this->assertIsString($formatted['updatedAt']);
        $this->assertStringStartsWith('2023-12-26T15:45:00', $formatted['updatedAt']);

        $this->assertIsString($formatted['renewsAt']);
        $this->assertStringStartsWith('2024-01-01T00:00:00', $formatted['renewsAt']);

        // Non-date fields should remain unchanged
        $this->assertEquals(123, $formatted['id']);
        $this->assertEquals('Test Subscription', $formatted['name']);
        $this->assertEquals(100.00, $formatted['amount']);
    }

    /**
     * @unreleased
     */
    public function testFormatAllShouldAutoDetectValueObjects()
    {
        $data = [
            'id' => 123,
            'period' => new SubscriptionPeriod('month'),
            'status' => new SubscriptionStatus('active'),
            'mode' => 'test',
            'name' => 'Test Subscription',
        ];

        $formatted = Formatter::formatAll($data);

        // Value Objects should be converted to their primitive values
        $this->assertEquals('month', $formatted['period']);
        $this->assertEquals('active', $formatted['status']);

        // Non-value object fields should remain unchanged
        $this->assertEquals(123, $formatted['id']);
        $this->assertEquals('test', $formatted['mode']);
        $this->assertEquals('Test Subscription', $formatted['name']);
    }

    /**
     * @unreleased
     */
    public function testFormatAllShouldHandleMixedDataTypes()
    {
        $data = [
            'id' => 123,
            'createdAt' => new DateTime('2023-12-25 14:30:00'),
            'period' => new SubscriptionPeriod('month'),
            'status' => new SubscriptionStatus('active'),
            'renewsAt' => '2024-01-01 00:00:00',
            'name' => 'Test Subscription',
        ];

        $formatted = Formatter::formatAll($data);

        // Date fields should be formatted
        $this->assertIsString($formatted['createdAt']);
        $this->assertStringStartsWith('2023-12-25T14:30:00', $formatted['createdAt']);

        $this->assertIsString($formatted['renewsAt']);
        $this->assertStringStartsWith('2024-01-01T00:00:00', $formatted['renewsAt']);

        // Value Objects should be converted
        $this->assertEquals('month', $formatted['period']);
        $this->assertEquals('active', $formatted['status']);

        // Other fields should remain unchanged
        $this->assertEquals(123, $formatted['id']);
        $this->assertEquals('Test Subscription', $formatted['name']);
    }

    /**
     * @unreleased
     */
    public function testFormatAllShouldHandleEmptyArrays()
    {
        $data = [];

        $formatted = Formatter::formatAll($data);

        $this->assertIsArray($formatted);
        $this->assertEmpty($formatted);
    }

    /**
     * @unreleased
     */
    public function testFormatAllShouldHandleNullValues()
    {
        $data = [
            'id' => 123,
            'createdAt' => null,
            'period' => null,
            'name' => 'Test Subscription',
        ];

        $formatted = Formatter::formatAll($data);

        $this->assertEquals(123, $formatted['id']);
        $this->assertNull($formatted['createdAt']);
        $this->assertNull($formatted['period']);
        $this->assertEquals('Test Subscription', $formatted['name']);
    }
}

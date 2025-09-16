<?php

namespace Give\Tests\Unit\API\REST\V3\Support;

use DateTime;
use Give\API\REST\V3\Support\Item;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class ItemTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testFormatDatesForResponseShouldFormatSpecifiedDateFields()
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

        $formatted = Item::formatDatesForResponse($item, $dateFields);

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
    public function testFormatDatesForResponseShouldHandleMultipleDateFields()
    {
        $item = [
            'id' => 123,
            'createdAt' => new DateTime('2023-12-25 14:30:00'),
            'updatedAt' => new DateTime('2023-12-26 15:45:00'),
            'name' => 'Test Subscription',
        ];

        $dateFields = ['createdAt', 'updatedAt'];

        $formatted = Item::formatDatesForResponse($item, $dateFields);

        // Date fields should be formatted
        $this->assertIsString($formatted['createdAt']);
        $this->assertEquals('2023-12-25T14:30:00', $formatted['createdAt']);
        $this->assertIsString($formatted['updatedAt']);
        $this->assertEquals('2023-12-26T15:45:00', $formatted['updatedAt']);

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
}

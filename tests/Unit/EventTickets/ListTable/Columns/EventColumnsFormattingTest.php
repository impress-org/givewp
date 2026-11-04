<?php

namespace Give\Tests\Unit\EventTickets\ListTable\Columns;

use DateTime;
use DateTimeZone;
use Give\EventTickets\ListTable\Columns\DateColumn;
use Give\EventTickets\ListTable\Columns\SalesAmountColumn;
use Give\EventTickets\ListTable\Columns\SalesCountColumn;
use Give\EventTickets\Models\Event;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class EventColumnsFormattingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        update_option('date_format', 'F j, Y');
        update_option('time_format', 'g:i a');
        update_option('timezone_string', 'UTC');
        foreach (
            [
                'currency' => 'USD',
                'currency_position' => 'before',
                'thousands_separator' => ',',
                'decimal_separator' => '.',
                'number_decimals' => 2,
                'auto_format_currency' => 'on',
            ] as $key => $value
        ) {
            give_update_option($key, $value);
        }
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach (['date_format', 'time_format', 'timezone_string'] as $option) {
            delete_option($option);
        }

        foreach (['currency', 'currency_position', 'thousands_separator', 'decimal_separator', 'number_decimals', 'auto_format_currency'] as $key) {
            give_delete_option($key);
        }

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testSalesAmountFollowsSiteSettingsWhateverLocaleIsPassed(): void
    {
        $event = Event::factory()->create();
        $column = new SalesAmountColumn();

        $cell = $column->getCellValue($event, 'pt_BR');

        $this->assertStringContainsString('$0.00 of $0.00', html_entity_decode($cell));
        $this->assertSame(['amount' => '0.00', 'currency' => 'USD'], $column->getRawValue($event));
        $this->assertSame('money', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testSalesAmountRawValueAddsNoQuery(): void
    {
        global $wpdb;

        $event = Event::factory()->create();
        $column = new SalesAmountColumn();
        $column->getCellValue($event);

        $before = $wpdb->num_queries;
        $column->getRawValue($event);

        $this->assertSame(0, $wpdb->num_queries - $before);
    }

    /**
     * @since TBD
     */
    public function testDateNoLongerUsesAFixedPattern(): void
    {
        update_option('date_format', 'd/m/Y');

        $event = Event::factory()->create([
            'startDateTime' => new DateTime('2025-11-29 16:16:00', new DateTimeZone('UTC')),
        ]);
        $column = new DateColumn();

        $this->assertSame('29/11/2025 at 4:16 pm', $column->getCellValue($event));
        $this->assertSame('2025-11-29T16:16:00Z', $column->getRawValue($event));
        $this->assertSame('date', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testSalesCountIsACount(): void
    {
        $event = Event::factory()->create();
        $column = new SalesCountColumn();

        $this->assertSame('count', $column->getKind());
        $this->assertSame(0, $column->getRawValue($event));
    }
}

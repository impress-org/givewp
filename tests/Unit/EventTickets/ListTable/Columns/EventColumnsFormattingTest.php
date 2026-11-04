<?php

namespace Give\Tests\Unit\EventTickets\ListTable\Columns;

use DateTime;
use DateTimeZone;
use Give\Donations\Models\Donation;
use Give\EventTickets\ListTable\Columns\DateColumn;
use Give\EventTickets\ListTable\Columns\SalesAmountColumn;
use Give\EventTickets\ListTable\Columns\SalesCountColumn;
use Give\EventTickets\Models\Event;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Models\EventTicketType;
use Give\EventTickets\Repositories\EventsDataRepository;
use Give\Framework\Support\ValueObjects\Money;
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
        give_update_option('enable_event_tickets', 'enabled');
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach (['date_format', 'time_format', 'timezone_string'] as $option) {
            delete_option($option);
        }

        foreach (['currency', 'currency_position', 'thousands_separator', 'decimal_separator', 'number_decimals', 'auto_format_currency', 'enable_event_tickets'] as $key) {
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

    /**
     * @since TBD
     */
    public function testSalesReadThePageDataWithoutQuerying(): void
    {
        global $wpdb;

        $eventWithSales = $this->createEventWithSales();
        $eventWithoutSales = Event::factory()->create();

        $before = $wpdb->num_queries;
        $data = EventsDataRepository::events([$eventWithSales->id, $eventWithoutSales->id]);
        // The ticket types, the check that the tickets table exists, and the tickets.
        $this->assertSame(3, $wpdb->num_queries - $before);

        $amountColumn = new SalesAmountColumn();
        $amountColumn->setListTableData($data);
        $countColumn = new SalesCountColumn();
        $countColumn->setListTableData($data);

        $before = $wpdb->num_queries;
        $amountWithSales = $amountColumn->getCellValue($eventWithSales);
        $amountRawWithSales = $amountColumn->getRawValue($eventWithSales);
        $countWithSales = $countColumn->getCellValue($eventWithSales);
        $countRawWithSales = $countColumn->getRawValue($eventWithSales);
        $amountWithoutSales = $amountColumn->getCellValue($eventWithoutSales);
        $countWithoutSales = $countColumn->getCellValue($eventWithoutSales);

        $this->assertSame(0, $wpdb->num_queries - $before);
        // Sold: 2 x $10.00 + 1 x $25.00. Unsold: 8 x $10.00 + 3 x $25.00.
        $this->assertStringContainsString('$45.00 of $200.00', html_entity_decode($amountWithSales));
        $this->assertSame(['amount' => '45.00', 'currency' => 'USD'], $amountRawWithSales);
        $this->assertSame('3 out of 14', $countWithSales);
        $this->assertSame(3, $countRawWithSales);
        $this->assertStringContainsString('$0.00 of $0.00', html_entity_decode($amountWithoutSales));
        $this->assertSame('0 out of 0', $countWithoutSales);
    }

    /**
     * @since TBD
     */
    public function testPageDataGivesTheSameValuesAsTheSingleEventQueries(): void
    {
        $event = $this->createEventWithSales();
        $data = EventsDataRepository::events([$event->id]);

        foreach ([SalesAmountColumn::class, SalesCountColumn::class] as $columnClass) {
            $withPageData = new $columnClass();
            $withPageData->setListTableData($data);
            $withoutPageData = new $columnClass();

            $this->assertSame($withoutPageData->getCellValue($event), $withPageData->getCellValue($event));
            $this->assertSame($withoutPageData->getRawValue($event), $withPageData->getRawValue($event));
        }
    }

    /**
     * An event with two ticket types: 2 of 10 sold at $10.00, and 1 of 4 sold at $25.00.
     *
     * @since TBD
     */
    private function createEventWithSales(): Event
    {
        $event = Event::factory()->create();
        $tickets = [[1000, 10, 2], [2500, 4, 1]];

        foreach ($tickets as [$price, $capacity, $sold]) {
            // The ticket factories create events of their own, so the models are created directly.
            $ticketType = EventTicketType::create([
                'eventId' => $event->id,
                'title' => 'Ticket',
                'description' => '',
                'price' => new Money($price, 'USD'),
                'capacity' => $capacity,
            ]);

            for ($i = 0; $i < $sold; $i++) {
                EventTicket::create([
                    'eventId' => $event->id,
                    'ticketTypeId' => $ticketType->id,
                    'donationId' => Donation::factory()->create()->id,
                    'amount' => new Money($price, 'USD'),
                ]);
            }
        }

        return $event;
    }
}

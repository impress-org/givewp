<?php

namespace Give\Tests\Unit\Donations\ListTable\Columns;

use DateTime;
use DateTimeZone;
use Give\Donations\ListTable\Columns\AmountColumn;
use Give\Donations\ListTable\Columns\CreatedAtColumn;
use Give\Donations\ListTable\Columns\StatusColumn;
use Give\Donations\Models\Donation;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class DonationColumnsFormattingTest extends TestCase
{
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
    public function testAmountFollowsSiteSettingsWhateverLocaleIsPassed(): void
    {
        $donation = new Donation(['amount' => new Money(133128, 'USD')]);
        $column = new AmountColumn();

        $this->assertStringContainsString('$1,331.28', $column->getCellValue($donation, 'pt_BR'));
        $this->assertStringContainsString('<div class="amount"><span>', $column->getCellValue($donation, 'pt_BR'));
        $this->assertSame(['amount' => '1331.28', 'currency' => 'USD'], $column->getRawValue($donation));
        $this->assertSame('money', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testCreatedAtUsesTheSharedDateShape(): void
    {
        $donation = new Donation([
            'createdAt' => new DateTime('2025-11-29 16:16:00', new DateTimeZone('UTC')),
        ]);
        $column = new CreatedAtColumn();

        $this->assertSame('November 29, 2025 at 4:16 pm', $column->getCellValue($donation));
        $this->assertSame('2025-11-29T16:16:00Z', $column->getRawValue($donation));
        $this->assertSame('date', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testStatusColumnIsAStatusKind(): void
    {
        $this->assertSame('status', (new StatusColumn())->getKind());
    }
}

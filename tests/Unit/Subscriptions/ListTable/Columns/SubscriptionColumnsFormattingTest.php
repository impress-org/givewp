<?php

namespace Give\Tests\Unit\Subscriptions\ListTable\Columns;

use DateTime;
use DateTimeZone;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\ListTable\Columns\AmountColumn;
use Give\Subscriptions\ListTable\Columns\RenewalDateColumn;
use Give\Subscriptions\ListTable\Columns\StatusColumn;
use Give\Subscriptions\Models\Subscription;
use Give\Campaigns\Models\Campaign;
use Give\Subscriptions\ListTable\Columns\CampaignColumn;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class SubscriptionColumnsFormattingTest extends TestCase
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
    public function testAmountFollowsTheBrowserLocaleWhenAutoFormatIsOn(): void
    {
        give_update_option('auto_format_currency', 'on');
        $subscription = new Subscription(['amount' => new Money(1234567890, 'USD')]);
        $column = new AmountColumn();

        $cell = html_entity_decode($column->getCellValue($subscription, 'en-US-u-nu-thai'));

        $this->assertStringContainsString('๑๒,๓๔๕,๖๗๘.๙๐', $cell);
        $this->assertSame(['amount' => '12345678.90', 'currency' => 'USD'], $column->getRawValue($subscription));
    }

    /**
     * @since TBD
     */
    public function testAmountFollowsSiteSettingsWhenAutoFormatIsOff(): void
    {
        give_update_option('auto_format_currency', '');
        $subscription = new Subscription(['amount' => new Money(133128, 'USD')]);

        $this->assertStringContainsString('$1,331.28', (new AmountColumn())->getCellValue($subscription, 'pt-BR'));
    }

    /**
     * @since TBD
     */
    public function testRenewalDateUsesTheSharedDateShape(): void
    {
        $subscription = new Subscription([
            'renewsAt' => new DateTime('2025-11-29 16:16:00', new DateTimeZone('UTC')),
        ]);
        $column = new RenewalDateColumn();

        $this->assertSame('November 29, 2025 at 4:16 pm', $column->getCellValue($subscription));
        $this->assertSame('2025-11-29T16:16:00Z', $column->getRawValue($subscription));
        $this->assertSame('date', $column->getKind());
    }

    /**
     * @since TBD
     */
    public function testRenewalDateWithoutADateIsTheDash(): void
    {
        $subscription = new Subscription(['renewsAt' => null]);
        $column = new RenewalDateColumn();

        $this->assertStringContainsString('givewp-list-table-dash', $column->getCellValue($subscription));
        $this->assertNull($column->getRawValue($subscription));
    }

    /**
     * @since TBD
     */
    public function testStatusColumnIsAStatusKind(): void
    {
        $this->assertSame('status', (new StatusColumn())->getKind());
    }

    /**
     * @since TBD
     */
    public function testCampaignCellEscapesTheTitle(): void
    {
        // Saving a campaign strips tags, so this title only has characters that survive it.
        $text = '"Cats" & Dogs';
        $campaign = Campaign::factory()->create(['title' => $text]);

        $cell = (new CampaignColumn())->getCellValue(new Subscription(['campaignId' => $campaign->id]));

        $this->assertStringContainsString(esc_html($text), $cell);
    }
}

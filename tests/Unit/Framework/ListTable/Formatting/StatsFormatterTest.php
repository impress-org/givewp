<?php

namespace Give\Tests\Unit\Framework\ListTable\Formatting;

use Give\Framework\ListTable\Formatting\StatsFormatter;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class StatsFormatterTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testFormatsCountsAndMoneyAsPlainText(): void
    {
        $result = StatsFormatter::withFormatted(
            ['donationsCount' => 1234, 'totalContributions' => 1331.5],
            ['totalContributions']
        );

        $this->assertSame(1234, $result['donationsCount']);
        $this->assertSame('1,234', $result['formatted']['donationsCount']);
        $this->assertStringContainsString('1,331.50', $result['formatted']['totalContributions']);
    }

    /**
     * A missing value is written as zero, so the JSON never holds the dash markup.
     *
     * @since TBD
     */
    public function testAMissingValueIsZeroAndNeverMarkup(): void
    {
        $result = StatsFormatter::withFormatted(['count' => null, 'total' => null], ['total']);

        $this->assertSame('0', $result['formatted']['count']);
        $this->assertStringNotContainsString('<', $result['formatted']['total']);
        $this->assertStringContainsString('0.00', $result['formatted']['total']);
    }

    /**
     * @since TBD
     */
    public function testWritesCountsAndMoneyForTheBrowserLocale(): void
    {
        give_update_option('auto_format_currency', 'on');

        $result = StatsFormatter::withFormatted(['count' => 1234567, 'total' => 12345678.5], ['total'], 'en-US-u-nu-thai');
        give_delete_option('auto_format_currency');

        $this->assertSame('๑,๒๓๔,๕๖๗', $result['formatted']['count']);
        $this->assertStringContainsString('๑๒,๓๔๕,๖๗๘.๕๐', $result['formatted']['total']);
    }
}

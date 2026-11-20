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
}

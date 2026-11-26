<?php

namespace Give\Tests\Unit\Framework\ListTable\Formatting;

use Give\Framework\ListTable\Formatting\CountFormatter;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class CountFormatterTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testFollowsTheBrowserLocale(): void
    {
        // Thai digits, not another language: the test container ships only English ICU data.
        $this->assertSame('๑,๒๓๔,๕๖๗', CountFormatter::format(1234567, 'en-US-u-nu-thai'));
        $this->assertSame('1,234', CountFormatter::format(1234, 'en-US'));
    }

    /**
     * @since TBD
     */
    public function testWithoutALocaleItIsWordPressOwnFormat(): void
    {
        $this->assertSame(number_format_i18n(1234), CountFormatter::format(1234));
    }
}

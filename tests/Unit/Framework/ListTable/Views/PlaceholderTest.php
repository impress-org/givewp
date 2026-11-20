<?php

namespace Give\Tests\Unit\Framework\ListTable\Views;

use Give\Framework\ListTable\Views\Placeholder;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class PlaceholderTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testDashHidesTheGlyphAndNamesTheValue(): void
    {
        $this->assertSame(
            '<span class="givewp-list-table-dash"><span aria-hidden="true">—</span><span class="screen-reader-text">No date</span></span>',
            Placeholder::dash('No date')
        );
    }

    /**
     * @since TBD
     */
    public function testLabelIsEscaped(): void
    {
        $html = Placeholder::dash('<b>No</b>');

        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('&lt;b&gt;No&lt;/b&gt;', $html);
    }
}

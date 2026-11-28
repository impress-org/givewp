<?php

namespace Give\Tests\Unit\Framework\ListTable\Views;

use Give\Framework\ListTable\Views\TruncatedText;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class TruncatedTextTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testWrapsTheMarkupAndPutsTheFullTextInTheTitle(): void
    {
        $this->assertSame(
            '<span class="givewp-truncated-text" title="A long campaign name">A long campaign name</span>',
            TruncatedText::render('A long campaign name', 'A long campaign name')
        );
    }

    /**
     * @since TBD
     */
    public function testEscapesTheTitleAndKeepsTheGivenMarkup(): void
    {
        $html = '<a href="/x">Fish &amp; "Chips"</a>';

        $this->assertSame(
            '<span class="givewp-truncated-text" title="Fish &amp; &quot;Chips&quot;">' . $html . '</span>',
            TruncatedText::render($html, 'Fish & "Chips"')
        );
    }
}

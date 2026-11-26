<?php

namespace Give\Tests\Unit\Framework\ListTable\Views;

use Give\Framework\ListTable\Views\GoalLabel;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class GoalLabelTest extends TestCase
{
    /**
     * @since TBD
     *
     * @dataProvider labels
     */
    public function testNamesTheTargetByItsGoalType(string $type, $raw, string $formatted, string $expected): void
    {
        $this->assertSame($expected, GoalLabel::make($type, $raw, $formatted));
    }

    /**
     * @since TBD
     */
    public function labels(): array
    {
        return [
            ['amount', 1000, '$1,000.00', '$1,000.00 raised'],
            ['amountFromSubscriptions', 1000, '$1,000.00', '$1,000.00 recurring raised'],
            ['donations', 1, '1', '1 donation'],
            ['donations', 5000, '5,000', '5,000 donations'],
            ['subscriptions', 2, '2', '2 recurring donations'],
            ['donors', '1', '1', '1 donor'],
            ['donors', 500, '500', '500 donors'],
            ['donorsFromSubscriptions', 500, '500', '500 recurring donors'],
            // A type an add-on or an older record uses: the target alone, as money goals read.
            ['something-else', 10, '10', '10 raised'],
        ];
    }
}

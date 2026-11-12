<?php

namespace Give\Tests\Unit\Framework\ListTable;

use Give\Framework\ListTable\StatusVocabulary;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class StatusVocabularyTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testToArrayListsOfferedValuesFirstInTheirOrderThenTheRest(): void
    {
        $vocabulary = new StatusVocabulary(['a' => 'A', 'b' => 'B', 'c' => 'C'], ['c', 'a']);

        $this->assertSame([
            ['value' => 'c', 'label' => 'C', 'filterable' => true],
            ['value' => 'a', 'label' => 'A', 'filterable' => true],
            ['value' => 'b', 'label' => 'B', 'filterable' => false],
        ], $vocabulary->toArray());
    }

    /**
     * @since TBD
     */
    public function testLabelIsNullForAnUnknownValue(): void
    {
        $this->assertNull((new StatusVocabulary(['a' => 'A'], ['a']))->label('legacy'));
    }

    /**
     * @since TBD
     */
    public function testAnOfferedValueWithoutALabelIsIgnored(): void
    {
        $vocabulary = new StatusVocabulary(['a' => 'A'], ['x', 'a']);

        $this->assertSame(['a'], $vocabulary->offeredValues());
        $this->assertCount(1, $vocabulary->toArray());
    }
}

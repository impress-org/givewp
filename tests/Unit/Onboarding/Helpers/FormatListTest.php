<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Onboarding\Helpers;

use Give\Onboarding\Helpers\FormatList;
use PHPUnit\Framework\TestCase;

final class FormatListTest extends TestCase
{
    /**
     * @return void
     */
    public function testFromKeyValue()
    {
        $data = ['foo' => 'bar'];
        $formattedList = FormatList::fromKeyValue($data);
        $expectedList = [
            [
                'value' => 'foo',
                'label' => 'bar',
            ],
        ];
        $this->assertEquals($expectedList, $formattedList);
    }

    /**
     * @return void
     */
    public function testFromValueKey()
    {
        $data = ['foo' => 'bar'];
        $formattedList = FormatList::fromValueKey($data);
        $expectedList = [
            [
                'value' => 'bar',
                'label' => 'foo',
            ],
        ];
        $this->assertEquals($expectedList, $formattedList);
    }
}

<?php

declare(strict_types=1);

namespace GiveTests\Unit\Onboarding\Helpers;

use Give\Onboarding\Helpers\FormatList;
use PHPUnit\Framework\TestCase;

final class FormatListTest extends TestCase
{

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

    public function testFromValueKey(): void
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

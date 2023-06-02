<?php

namespace Unit\Framework\FieldsAPI;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FieldsAPI\Checkbox;
use Give\Tests\TestCase;

class CheckboxTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testChecked()
    {
        $checkbox = new Checkbox('test');

        self::assertFalse($checkbox->isChecked());

        $checkbox->checked();
        self::assertTrue($checkbox->isChecked());

        $checkbox->checked(false);
        self::assertFalse($checkbox->isChecked());

        $checkbox->checked(true);
        self::assertTrue($checkbox->isChecked());
    }
}

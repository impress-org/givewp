<?php

namespace Unit\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Checkbox;
use Give\Tests\TestCase;

class CheckboxTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testShouldToggleCheckedState()
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

    /**
     * @unreleased
     */
    public function testShouldSetAndGetValue()
    {
        $checkbox = new Checkbox('test');
        $checkbox->value('test-value');
        self::assertEquals('test-value', $checkbox->getValue());
    }

    /**
     * @unreleased
     */
    public function testShouldReturnValueAsDefaultWhenChecked()
    {
        $checkbox = new Checkbox('test');

        $checkbox->value('test-value');
        $checkbox->checked();
        self::assertEquals('test-value', $checkbox->getDefaultValue());

        $checkbox->checked(false);
        self::assertNull($checkbox->getDefaultValue());
    }
}

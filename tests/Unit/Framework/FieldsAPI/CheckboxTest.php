<?php

namespace Unit\Framework\FieldsAPI;

use Give\Framework\Exceptions\Primitives\RuntimeException;
use Give\Framework\FieldsAPI\Checkbox;
use Give\Tests\TestCase;

class CheckboxTest extends TestCase
{
    /**
     * @since 2.28.0
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
     * @since 2.28.0
     */
    public function testShouldSetAndGetValue()
    {
        $checkbox = new Checkbox('test');
        $checkbox->value('test-value');
        self::assertEquals('test-value', $checkbox->getValue());
    }

    /**
     * @since 2.28.0
     */
    public function testShouldReturnValueAsDefaultWhenChecked()
    {
        $checkbox = new Checkbox('test');

        // Default value is null when not checked
        $checkbox->value('test-value');
        self::assertNull($checkbox->getDefaultValue());

        // Default value is set to the value when checked
        $checkbox->checked();
        self::assertEquals('test-value', $checkbox->getDefaultValue());

        // Default value changes when value changes
        $checkbox->value('new-value');
        self::assertEquals('new-value', $checkbox->getDefaultValue());

        // Default value is set to null when unchecked
        $checkbox->checked(false);
        self::assertNull($checkbox->getDefaultValue());
    }

    /**
     * @since 2.28.0
     */
    public function testShouldThrowRuntimeExceptionWhenDefaultValueMethodIsUsed()
    {
        $this->expectException(RuntimeException::class);

        $checkbox = new Checkbox('test');
        $checkbox->defaultValue('test-value');
    }
}

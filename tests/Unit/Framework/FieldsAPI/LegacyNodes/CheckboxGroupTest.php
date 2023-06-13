<?php

namespace Unit\Framework\FieldsAPI\LegacyNodes;

use Give\Framework\FieldsAPI\LegacyNodes\CheckboxGroup;
use Give\Tests\TestCase;

class CheckboxGroupTest extends TestCase
{
    /**
     * @since 2.19.0
     */
    public function testChecked()
    {
        // Default is true
        $checkbox = CheckboxGroup::make('test')->checked();
        self::assertTrue($checkbox->isChecked());

        // Can pass a truthy value
        $checkbox = CheckboxGroup::make('test')->checked(true);
        self::assertTrue($checkbox->isChecked());

        $checkbox = CheckboxGroup::make('test')->checked(1);
        self::assertTrue($checkbox->isChecked());

        // Can pass a falsey value
        $checkbox = CheckboxGroup::make('test')->checked(false);
        self::assertFalse($checkbox->isChecked());

        $checkbox = \Give\Framework\FieldsAPI\LegacyNodes\CheckboxGroup::make('test')->checked(0);
        self::assertFalse($checkbox->isChecked());

        // Can pass a callable
        $checkbox = CheckboxGroup::make('test')->checked(function () {
            return true;
        });
        self::assertTrue($checkbox->isChecked());
    }
}

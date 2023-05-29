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
    public function testIndeterminateSupport()
    {
        $checkbox = new Checkbox('test');

        // should not support indeterminate value by default
        self::assertFalse($checkbox->supportsIndeterminateValue());

        // should support indeterminate value if set to support
        $checkbox->supportIndeterminateValue();
        self::assertTrue($checkbox->supportsIndeterminateValue());

        // should now allow an indeterminate value
        $checkbox->indeterminate();
        self::assertTrue($checkbox->isIndeterminate());

        // support can be explicitly set to false
        $checkbox->supportIndeterminateValue(false);
        self::assertFalse($checkbox->supportsIndeterminateValue());
    }

    /**
     * @unreleased
     */
    public function testShouldThrowExceptionIfSetToIndeterminateWithSupportDisabled()
    {
        $checkbox = new Checkbox('test');

        $this->expectException(InvalidArgumentException::class);
        $checkbox->indeterminate();
    }
}

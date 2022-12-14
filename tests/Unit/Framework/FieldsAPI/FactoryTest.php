<?php

namespace Give\Tests\Unit\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Facades\Factory;
use Give\Framework\FieldsAPI\Text;
use Give\Tests\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @since 2.19.0
     */
    public function testReturnExceptionWhenMakeFieldWithEmptyName()
    {
        $this->expectException(EmptyNameException::class);
        Factory::make('text', '');
    }

    /**
     * @since 2.19.0
     */
    public function testReturnExceptionWhenMakeFieldWithEmptyNameWithMakeFunction()
    {
        $this->expectException(EmptyNameException::class);
        Text::make('');
    }

    public function testMakeTextField()
    {
        $field = Factory::make('text', 'my-text-field');

        $this->assertInstanceOf(Text::class, $field);
        $this->assertEquals('my-text-field', $field->getName());
    }
}

<?php

namespace Give\Tests\Unit\Framework\Support\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use PHPUnit\Framework\TestCase;

final class EnumTest extends TestCase
{
    public function testIsMethods()
    {
        $enum = TestEnum::FOO();

        $this->assertTrue($enum->isFoo());
        $this->assertFalse($enum->isBar());
        $this->assertFalse($enum->isMultiWord());
    }

    public function testIsOneOfMethod()
    {
        $enum = TestEnum::FOO();
        $this->assertTrue($enum->isOneOf(TestEnum::FOO(), TestEnum::BAR()));
        $this->assertFalse($enum->isOneOf(TestEnum::BAR(), TestEnum::MULTI_WORD()));
    }

    public function testCamelCaseKeys()
    {
        $foo = TestEnum::FOO();
        $multiWord = TestEnum::MULTI_WORD();

        $this->assertEquals('foo', $foo->getKeyAsCamelCase());
        $this->assertEquals('multiWord', $multiWord->getKeyAsCamelCase());
    }
}

/**
 * @method static TestEnum FOO()
 * @method static TestEnum BAR()
 * @method static TestEnum MULTI_WORD()
 * @method bool isFoo()
 * @method bool isBar()
 * @method bool isMultiWord()
 */
class TestEnum extends Enum
{
    const FOO = 'foo';
    const BAR = 'bar';
    const MULTI_WORD = 'multi-word';
}

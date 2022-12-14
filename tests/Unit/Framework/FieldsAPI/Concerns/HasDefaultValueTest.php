<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasDefaultValue;
use PHPUnit\Framework\TestCase;

final class HasDefaultValueTest extends TestCase
{

    public function testDefaultValue()
    {
        /** @var HasDefaultValue $mock */
        $mock = $this->getMockForTrait(HasDefaultValue::class);
        $mock->defaultValue('Hello world');
        $this->assertEquals( 'Hello world' , $mock->getDefaultValue() );
    }
}

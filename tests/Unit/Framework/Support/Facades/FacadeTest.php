<?php

namespace GiveTests\Unit\Framework\Support\Facades;

use Give\Framework\Support\Facades\Facade;
use GiveTests\TestCase;

final class FacadeTest extends TestCase
{
    public function testFacadeInheritance()
    {
        $this->assertEquals('hello', Hello::sayHello());
    }
}

/**
 * Class HelloGenerator
 */
class HelloGenerator
{
    public function sayHello()
    {
        return 'hello';
    }
}

/**
 * Class Hello
 * @method static sayHello()
 */
class Hello extends Facade
{
    protected function getFacadeAccessor()
    {
        return HelloGenerator::class;
    }
}

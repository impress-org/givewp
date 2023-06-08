<?php

namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasVisibilityConditions;
use Give\Vendors\StellarWP\FieldConditions\FieldCondition;
use PHPUnit\Framework\TestCase;

final class HasVisibilityConditionsTest extends TestCase
{

    public function testCanAccessVisibilityConditions()
    {
        /** @var HasVisibilityConditions $mock */
        $mock = $this->getMockForTrait(HasVisibilityConditions::class);

        $this->assertEquals([], $mock->getVisibilityConditions());
    }

    /**
     * @since 2.27.3
     */
    public function testCanCheckIfHasVisibilityConditions()
    {
        /** @var HasVisibilityConditions $mock */
        $mock = $this->getMockForTrait(HasVisibilityConditions::class);
        $this->assertFalse($mock->hasVisibilityConditions());

        $mock->showIf('foo', '=', 'bar');
        $this->assertTrue($mock->hasVisibilityConditions());
    }

    public function testCanSetVisibilityCondition()
    {
        /** @var HasVisibilityConditions $mock */
        $mock = $this->getMockForTrait(HasVisibilityConditions::class);

        $mock->showIf('foo', '=', 'bar')
            ->andShowIf('biz', '!=', 'baz')
            ->orShowIf('baz', '!=', 'foo');

        $this->assertCount(3, $mock->getVisibilityConditions());
    }

    public function testCanSetMultipleVisibilityConditions()
    {
        /** @var HasVisibilityConditions $mock */
        $mock = $this->getMockForTrait(HasVisibilityConditions::class);

        $mock->showWhen(
            new FieldCondition('foo', '=', 'bar'),
            ['baz', '!=', 'foo']
        );

        $this->assertCount(2, $mock->getVisibilityConditions());
    }
}

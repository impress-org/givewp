<?php

namespace Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasPersistence;
use Give\Framework\FieldsAPI\ValueObjects\PersistenceScope;
use Give\Tests\TestCase;


/**
 * @covers \Give\Framework\FieldsAPI\Concerns\HasPersistence
 */
class HasPersistenceTest extends TestCase
{
    /**
     * @since 2.32.0
     */
    public function testStoreAsDonorMetaMethods()
    {
        /** @var HasPersistence $mock */
        $mock = self::getMockForTrait(HasPersistence::class);

        $mock->storeAsDonorMeta();
        self::assertTrue($mock->shouldStoreAsDonorMeta());

        $mock->storeAsDonorMeta(false);
        self::assertFalse($mock->shouldStoreAsDonorMeta());
    }

    /**
     * @since 2.32.0
     */
    public function testSettingTheScopeByString()
    {
        /** @var HasPersistence $mock */
        $mock = self::getMockForTrait(HasPersistence::class);

        $mock->scope('test');
        self::assertSame('test', $mock->getScopeValue());
        self::assertTrue($mock->getScope()->is('test'));
    }

    /**
     * @since 2.32.0
     */
    public function testSettingTheScopeByInstance()
    {
        /** @var HasPersistence $mock */
        $mock = self::getMockForTrait(HasPersistence::class);

        $mock->scope(new PersistenceScope('test'));
        self::assertEquals('test', $mock->getScopeValue());
        self::assertTrue($mock->getScope()->is('test'));
    }

    public function testUsingClosureForScope()
    {
        /** @var HasPersistence $mock */
        $mock = self::getMockForTrait(HasPersistence::class);

        $callback = function () {
            return 'test';
        };
        $mock->scope($callback);

        self::assertTrue($mock->getScope()->isCallback());
        self::assertSame($callback, $mock->getScopeCallback());
    }

    /**
     * @since 2.32.0
     */
    public function testSettingTheMetaKey()
    {
        /** @var HasPersistence $mock */
        $mock = self::getMockForTrait(HasPersistence::class);

        $mock->metaKey('test');
        self::assertEquals('test', $mock->getMetaKey());
    }
}

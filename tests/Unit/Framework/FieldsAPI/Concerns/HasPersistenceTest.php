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
     * @unreleased
     */
    public function testStoreAsDonorMetaMethods()
    {
        /** @var HasPersistence $mock */
        $mock = $this->getMockForTrait(HasPersistence::class);

        $mock->storeAsDonorMeta();
        $this->assertTrue($mock->shouldStoreAsDonorMeta());

        $mock->storeAsDonorMeta(false);
        $this->assertFalse($mock->shouldStoreAsDonorMeta());
    }

    /**
     * @unreleased
     */
    public function testSettingTheScopeByString()
    {
        /** @var HasPersistence $mock */
        $mock = $this->getMockForTrait(HasPersistence::class);

        $mock->scope('test');
        $this->assertSame('test', $mock->getScopeValue());
        $this->assertTrue($mock->getScope()->is('test'));
    }

    /**
     * @unreleased
     */
    public function testSettingTheScopeByInstance()
    {
        /** @var HasPersistence $mock */
        $mock = $this->getMockForTrait(HasPersistence::class);

        $mock->scope(new PersistenceScope('test'));
        $this->assertEquals('test', $mock->getScopeValue());
        $this->assertTrue($mock->getScope()->is('test'));
    }

    /**
     * @unreleased
     */
    public function testSettingTheMetaKey()
    {
        /** @var HasPersistence $mock */
        $mock = $this->getMockForTrait(HasPersistence::class);

        $mock->metaKey('test');
        $this->assertEquals('test', $mock->getMetaKey());
    }
}

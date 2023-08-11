<?php

namespace Unit\Framework\FieldsAPI\ValueObjects;

use Give\Framework\FieldsAPI\ValueObjects\PersistenceScope;
use Give\Tests\TestCase;

/**
 * @coversDefaultClass \Give\Framework\FieldsAPI\ValueObjects\PersistenceScope
 */
class PersistenceScopeTest extends TestCase
{
    /**
     * @since 2.32.0
     */
    public function testStaticBuilders()
    {
        $donation = PersistenceScope::donation();
        $donor = PersistenceScope::donor();
        $callback = PersistenceScope::callback();

        self::assertInstanceOf(PersistenceScope::class, $donation);
        self::assertTrue($donation->isDonation());

        self::assertInstanceOf(PersistenceScope::class, $donor);
        self::assertTrue($donor->isDonor());

        self::assertInstanceOf(PersistenceScope::class, $callback);
        self::assertTrue($callback->isCallback());
    }

    /**
     * @since 2.32.0
     */
    public function testToString()
    {
        $scope = PersistenceScope::donation();
        self::assertEquals('donation', (string)$scope);
    }

    /**
     * @since 2.32.0
     */
    public function testIsMethods()
    {
        $donation = PersistenceScope::donation();

        self::assertTrue($donation->isDonation());
        self::assertFalse($donation->isDonor());
        self::assertFalse($donation->isCallback());
        self::assertTrue($donation->is(PersistenceScope::DONATION));
    }

    /**
     * @since 2.32.0
     */
    public function testConstructor()
    {
        $scope = new PersistenceScope('donation');
        self::assertTrue($scope->isDonation());
    }
}

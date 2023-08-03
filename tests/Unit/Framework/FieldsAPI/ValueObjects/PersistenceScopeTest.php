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
     * @unreleased
     */
    public function testStaticBuilders()
    {
        $donation = PersistenceScope::donation();
        $donor = PersistenceScope::donor();

        self::assertInstanceOf(PersistenceScope::class, $donation);
        self::assertTrue($donation->isDonation());

        self::assertInstanceOf(PersistenceScope::class, $donor);
        self::assertTrue($donor->isDonor());
    }

    /**
     * @unreleased
     */
    public function testToString()
    {
        $scope = PersistenceScope::donation();
        self::assertEquals('donation', (string)$scope);
    }

    /**
     * @unreleased
     */
    public function testIsMethods()
    {
        $donation = PersistenceScope::donation();

        self::assertTrue($donation->isDonation());
        self::assertFalse($donation->isDonor());
        self::assertTrue($donation->is(PersistenceScope::DONATION));
    }

    /**
     * @unreleased
     */
    public function testConstructor()
    {
        $scope = new PersistenceScope('donation');
        self::assertTrue($scope->isDonation());
    }
}

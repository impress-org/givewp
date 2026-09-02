<?php
namespace Give\Tests\Unit\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\Routes\RouteSignature;
use PHPUnit\Framework\TestCase;

/**
 * @since 2.19.0
 *
 * @coversDefaultClass RouteSignature
 */
class RouteSignatureTest extends TestCase
{
    /**
     * @since 2.19.5 add expiration
     * @since 2.19.4 replace RouteSignature args with unique donationId
     * @since 2.19.0
     *
     * @return void
     */
    public function testRouteSignatureReturnsValidString()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $expiration = $this->createExpirationTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $expiration);

        $string = "$gatewayId@$gatewayMethod:$donationId|$expiration";

        $this->assertEquals($signature->toString(), $string);
    }

    /**
     * A signature made without args has to keep hashing to what it did before args were covered, so URLs
     * already in flight through a gateway stay valid across the upgrade.
     *
     * @since 4.16.8
     *
     * @return void
     */
    public function testRouteSignatureWithoutArgsIsUnchanged()
    {
        $expiration = $this->createExpirationTimestamp();

        $withoutArgs = new RouteSignature('test-gateway', 'secureMethod', 1, $expiration);
        $withEmptyArgs = new RouteSignature('test-gateway', 'secureMethod', 1, $expiration, []);

        $this->assertSame("test-gateway@secureMethod:1|$expiration", $withoutArgs->toString());
        $this->assertSame($withoutArgs->toString(), $withEmptyArgs->toString());
    }

    /**
     * The args are what a route reads to decide which record it acts on, so editing one has to break the
     * signature rather than leave it valid.
     *
     * @since 4.16.8
     *
     * @return void
     */
    public function testRouteSignatureChangesWhenASignedArgIsEdited()
    {
        $expiration = $this->createExpirationTimestamp();

        $signed = new RouteSignature('test-gateway', 'secureMethod', 1, $expiration, ['donation-id' => 1]);
        $edited = new RouteSignature('test-gateway', 'secureMethod', 1, $expiration, ['donation-id' => 2]);

        $this->assertFalse($edited->isValid($signed->toHash()));
    }

    /**
     * Dropping a signed arg must not fall back to the argless signature.
     *
     * @since 4.16.8
     *
     * @return void
     */
    public function testRouteSignatureChangesWhenASignedArgIsDropped()
    {
        $expiration = $this->createExpirationTimestamp();

        $signed = new RouteSignature('test-gateway', 'secureMethod', 1, $expiration, ['donation-id' => 1]);
        $dropped = new RouteSignature('test-gateway', 'secureMethod', 1, $expiration);

        $this->assertFalse($dropped->isValid($signed->toHash()));
    }

    /**
     * Query args do not keep their order through a redirect, so the signature must not depend on it.
     *
     * @since 4.16.8
     *
     * @return void
     */
    public function testRouteSignatureIgnoresArgOrder()
    {
        $expiration = $this->createExpirationTimestamp();

        $one = new RouteSignature('test-gateway', 'secureMethod', 1, $expiration,
            ['donation-id' => 1, 'givewp-return-url' => 'https://example.org/thanks/']);
        $two = new RouteSignature('test-gateway', 'secureMethod', 1, $expiration,
            ['givewp-return-url' => 'https://example.org/thanks/', 'donation-id' => 1]);

        $this->assertTrue($two->isValid($one->toHash()));
    }

    /**
     * @since 2.19.5
     *
     * @return void
     */
    public function testRouteSignatureReturnsValidHash()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $expiration = $this->createExpirationTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $expiration);

        $this->assertTrue(
            hash_equals(
                $signature->toHash(),
                wp_hash($signature->toString())
            )
        );
    }

    /**
     * @since 2.19.5
     *
     * @return void
     */
    public function testRouteSignatureIsValidReturnsTrue()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $expiration = $this->createExpirationTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $expiration);

        $suppliedSignature = wp_hash("$gatewayId@$gatewayMethod:$donationId|$expiration");

        $this->assertTrue(
            $signature->isValid($suppliedSignature)
        );
    }

    /**
     * @since 2.19.5
     *
     * @return void
     */
    public function testRouteSignatureIsValidReturnsFalseFromExpiration()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $yesterday = (string)current_datetime()->modify('-1 day')->getTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $yesterday);

        $suppliedSignature = wp_hash("$gatewayId@$gatewayMethod:$donationId|$yesterday");

        $this->assertFalse(
            $signature->isValid($suppliedSignature)
        );
    }

    /**
     * @since 2.19.5
     *
     * @return void
     */
    public function testRouteSignatureIsValidReturnsFalseFromIntegrity()
    {
        $gatewayId = 'test-gateway';
        $gatewayMethod = 'secureMethod';
        $donationId = 1;
        $expiration = $this->createExpirationTimestamp();

        $signature = new RouteSignature($gatewayId, $gatewayMethod, $donationId, $expiration);

        $suppliedSignature = wp_hash("$gatewayId@$gatewayMethod:2|$expiration");

        $this->assertFalse(
            $signature->isValid($suppliedSignature)
        );
    }

    /**
     * @return string
     */
    public function createExpirationTimestamp()
    {
        return (string)current_datetime()->modify('+1 day')->getTimestamp();
    }
}

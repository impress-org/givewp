<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Routes;

use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\Routes\RouteSignature;
use PHPUnit\Framework\TestCase;

/**
 * Covers the round trip a secure route makes: the args a gateway puts on the URL come back through
 * GatewayRouteData, and the signature is rebuilt from them the way GatewayRoute does it.
 *
 * @since TBD
 */
class SecureRouteArgsTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testSignedArgsSurviveTheRoundTrip()
    {
        $request = $this->request(['donation-id' => '1', 'givewp-return-url' => 'https://example.org/thanks/']);

        $this->assertTrue($this->rebuild($request)->isValid($request['give-route-signature']));
    }

    /**
     * The attack this closes: a route URL the sender legitimately owns, repointed at another record.
     *
     * @since TBD
     */
    public function testAnEditedArgIsRejected()
    {
        $request = $this->request(['donation-id' => '1']);
        $request['donation-id'] = '2';

        $this->assertFalse($this->rebuild($request)->isValid($request['give-route-signature']));
    }

    /**
     * Offsite gateways append their own parameters to the return URL, so an arg the signature never
     * covered must not invalidate it.
     *
     * @since TBD
     */
    public function testAnUnsignedArgAddedByTheGatewayIsIgnored()
    {
        $request = $this->request(['donation-id' => '1']);
        $request['tx'] = 'PAYPAL-TXN-1';

        $this->assertTrue($this->rebuild($request)->isValid($request['give-route-signature']));
    }

    /**
     * @since TBD
     */
    public function testTheSignedArgListIsKeptOutOfQueryParams()
    {
        $data = GatewayRouteData::fromRequest($this->request(['donation-id' => '1']));

        $this->assertSame(['donation-id'], $data->routeSignatureArgKeys);
        $this->assertArrayNotHasKey('give-route-signature-args', $data->queryParams);
        $this->assertArrayNotHasKey('give-route-signature-id', $data->queryParams);
    }

    /**
     * Builds the request a secure route URL produces, the way generateSecureGatewayRouteUrl does.
     */
    private function request(array $args): array
    {
        $signature = new RouteSignature('test-gateway', 'secureMethod', 1, null, $args);

        return array_merge($args, [
            'give-listener' => 'give-gateway',
            'give-gateway-id' => 'test-gateway',
            'give-gateway-method' => 'secureMethod',
            'give-route-signature' => $signature->toHash(),
            'give-route-signature-id' => '1',
            'give-route-signature-expiration' => $signature->expiration,
            'give-route-signature-args' => implode(',', $signature->argKeys),
        ]);
    }

    /**
     * Rebuilds the signature through the same call GatewayRoute::validateSignature makes.
     */
    private function rebuild(array $request): RouteSignature
    {
        return RouteSignature::fromRouteData(GatewayRouteData::fromRequest($request));
    }
}

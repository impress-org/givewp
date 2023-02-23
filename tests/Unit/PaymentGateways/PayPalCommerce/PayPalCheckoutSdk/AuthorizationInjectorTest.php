<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk;

use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\AccessToken;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\AuthorizationInjector;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use Give\Tests\TestCase;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalHttp\HttpRequest;

/**
 * @unreleased x.x.x
 */
class AuthorizationInjectorTest extends TestCase
{
    /**
     * @unreleased
     * @throws \PHPUnit\Framework\Exception
     */
    public function testShouldNotFetchNewAccessToken()
    {
        $accessToken = AccessToken::fromArray(
            [
                'accessToken' => 'A21AAKWsfmPmn',
                'tokenType' => 'Bearer',
                'appId' => 'APP-80W2844',
                'expiresIn' => 32400,

                // Latest access token creation date to prevent refetch latest access token http request action.
                'nonce' => sprintf(
                    '%sZPeYxT6_thWGlTaamtMGYt5RQzVHx5B4dlNjLNhoF0tM',
                    date('c')
                ),
            ]
        );

        $dummyHttpRequest = new HttpRequest(null, null);

        $paypalClient = give(PayPalClient::class);
        $paypalEnvironment = $paypalClient->getEnvironment();
        $paypalHttpClient = new PayPalHttpClient($paypalEnvironment);
        $authorizationInjector = new AuthorizationInjectorMock($paypalHttpClient, $paypalEnvironment, null);
        $authorizationInjector->accessToken = $accessToken;

        $authorizationInjector->inject($dummyHttpRequest);

        $this->assertContains($accessToken->token, $dummyHttpRequest->headers['Authorization']);
    }

    /**
     * @unreleased
     * @throws \PHPUnit\Framework\Exception
     */
    public function testShouldFetchNewAccessTokenIfExpired()
    {
        $accessToken = AccessToken::fromArray(
            [
                'accessToken' => 'A21AAKWsfmPmn',
                'tokenType' => 'Bearer',
                'appId' => 'APP-80W2844',
                'expiresIn' => 32400,

                // Last year creation date, to force expiration.
                'nonce' => '2022-02-07T05:03:17ZPeYxT6_thWGlTaamtMGYt5RQzVHx5B4dlNjLNhoF0tM',
            ]
        );

        $dummyHttpRequest = new HttpRequest(null, null);

        $paypalClient = give(PayPalClient::class);
        $paypalEnvironment = $paypalClient->getEnvironment();
        $paypalHttpClient = new PayPalHttpClient($paypalEnvironment);
        $authorizationInjector = new AuthorizationInjectorMock($paypalHttpClient, $paypalEnvironment, null);
        $authorizationInjector->accessToken = $accessToken;

        $authorizationInjector->inject($dummyHttpRequest);

        $this->assertContains("$accessToken->token", $dummyHttpRequest->headers['Authorization']);
    }
}

/**
 * Mock class for AuthorizationInjector class
 *
 * @unreleased
 */
class AuthorizationInjectorMock extends AuthorizationInjector
{
    public function fetchAccessToken(): AccessToken
    {
        return AccessToken::fromObject(
            json_decode(
                json_encode(
                    [
                        'accessToken' => 'new-A21AAKWsfmPmn',
                        'tokenType' => 'Bearer',
                        'appId' => 'APP-80W2844',
                        'expiresIn' => 32400,

                        // Latest access token creation date to prevent refetch latest access token http request action.
                        'nonce' => sprintf(
                            '%sZPeYxT6_thWGlTaamtMGYt5RQzVHx5B4dlNjLNhoF0tM',
                            date('c')
                        ),
                    ]
                )
            )
        );
    }
}

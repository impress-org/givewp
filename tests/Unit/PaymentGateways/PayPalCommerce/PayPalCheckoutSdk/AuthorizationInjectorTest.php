<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk;

use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\AccessToken;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\AuthorizationInjector;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use Give\Tests\TestCase;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalHttp\HttpRequest;

/**
 * @since 2.25.0
 */
class AuthorizationInjectorTest extends TestCase
{
    /**
     * @since 2.25.0
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
     * @since 2.25.0
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
 * @since 2.25.0
 */
class AuthorizationInjectorMock extends AuthorizationInjector
{
    public function fetchAccessToken(): AccessToken
    {
        return AccessToken::fromArray(
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
        );
    }
}

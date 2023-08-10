<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk;

use Give\PaymentGateways\PayPalCommerce\RefreshToken;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalAuth;
use PayPalCheckoutSdk\Core\AccessTokenRequest;
use PayPalCheckoutSdk\Core\RefreshTokenRequest;
use PayPalHttp\HttpRequest;
use PayPalHttp\Injector;

/**
 * Class AuthorizationInjector
 *
 * This class set Authorization in http request header if missing.
 * Authorization header build with merchant access token.
 * Fresh Merchant access token will be fetched from PayPal if expired, for http request.
 *
 * @since 2.32.0 Remove unnecessary properties and methods.
 * @since 2.25.0
 *
 * @see \PayPalCheckoutSdk\Core\AuthorizationInjector
 */
class AuthorizationInjector implements Injector
{
    public $accessToken;

    /**
     * Adds an Authorization header to the request.
     *
     * @since 2.25.0
     */
    public function inject($request)
    {
        if (! $this->hasAuthHeader($request) && ! $this->isAuthRequest($request)) {
            if (is_null($this->accessToken) || $this->accessToken->isExpired()) {
                $this->accessToken = $this->fetchAccessToken();
            }
            $request->headers['Authorization'] = "Bearer {$this->accessToken->token}";
        }
    }

    /**
     * Returns an AccessToken.
     *
     * @since 2.32.0 use client credentials to fetch access token.
     * @since 2.25.0
     */
    protected function fetchAccessToken(): AccessToken
    {
        $merchantDetail = give(MerchantDetails::class)->getDetails();
        $accessToken = give(PayPalAuth::class)->getTokenFromClientCredentials(
            $merchantDetail->clientId,
            $merchantDetail->clientSecret
        );

        $this->registerRefreshTokenCronJob($accessToken);

        return AccessToken::fromArray($accessToken);
    }

    /**
     * Return true if the request is an AccessTokenRequest or RefreshTokenRequest.
     *
     * @since 2.25.0
     */
    private function isAuthRequest($request): bool
    {
        return $request instanceof AccessTokenRequest || $request instanceof RefreshTokenRequest;
    }

    /**
     * Return true if the request has an Authorization header.
     *
     * @since 2.25.0
     */
    private function hasAuthHeader(HttpRequest $request): bool
    {
        return array_key_exists("Authorization", $request->headers);
    }

    /**
     * Should save new access token and add a cron job to refresh token.
     *
     * @since 2.32.0 Get latest merchant details from database.
     * @since 2.25.0
     *
     * @return void
     */
    private function registerRefreshTokenCronJob(array $accessToken)
    {
        $refreshToken = give(RefreshToken::class);
        $merchantDetailRepository = give(MerchantDetails::class);
        $merchantDetail = $merchantDetailRepository->getDetails();

        $merchantDetail->setTokenDetails($accessToken);
        $merchantDetailRepository->save($merchantDetail);

        $refreshToken->deleteRefreshTokenCronJob();
        $refreshToken->registerCronJobToRefreshToken($accessToken['expiresIn']);
    }
}

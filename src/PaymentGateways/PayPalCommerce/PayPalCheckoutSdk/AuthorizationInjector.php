<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk;

use Give\Helpers\ArrayDataSet;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\RefreshToken;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use PayPalCheckoutSdk\Core\AccessTokenRequest;
use PayPalCheckoutSdk\Core\PayPalEnvironment;
use PayPalCheckoutSdk\Core\RefreshTokenRequest;
use PayPalHttp\HttpClient;
use PayPalHttp\HttpRequest;
use PayPalHttp\Injector;

/**
 * Class AuthorizationInjector
 *
 * This class set Authorization in http request header if missing.
 * Authorization header build with merchant access token.
 * Fresh Merchant access token will be fetched from PayPal if expired, for http request.
 *
 * @since 2.25.0
 *
 * @see \PayPalCheckoutSdk\Core\AuthorizationInjector
 */
class AuthorizationInjector implements Injector
{
    private $client;
    private $environment;
    private $refreshToken;
    public $accessToken;

    /**
     * Class constructor.
     *
     * @since 2.25.0
     */
    public function __construct(HttpClient $client, PayPalEnvironment $environment, $refreshToken)
    {
        $this->client = $client;
        $this->environment = $environment;
        $this->refreshToken = $refreshToken;
    }

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
            $request->headers['Authorization'] = 'Bearer ' . $this->accessToken->token;
        }
    }

    /**
     * Returns an AccessToken.
     *
     * @since 2.25.0
     */
    protected function fetchAccessToken(): AccessToken
    {
        $accessTokenResponse = $this->client->execute(new AccessTokenRequest($this->environment, $this->refreshToken));
        $accessToken = $accessTokenResponse->result;

        $this->registerRefreshTokenCronJob($accessToken);

        return AccessToken::fromObject($accessToken);
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
     * @since 2.25.0
     *
     * @return void
     */
    private function registerRefreshTokenCronJob($accessToken)
    {
        $refreshToken = give(RefreshToken::class);
        $merchantDetail = give(MerchantDetail::class);
        $merchantDetailRepository = give(MerchantDetails::class);
        $accessToken = ArrayDataSet::camelCaseKeys($accessToken);

        $merchantDetail->setTokenDetails($accessToken);
        $merchantDetailRepository->save($merchantDetail);

        $refreshToken->deleteRefreshTokenCronJob();
        $refreshToken->registerCronJobToRefreshToken($accessToken['expires_in']);
    }
}

<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\ConnectServer\Client\ConnectClient;
use Give\Helpers\ArrayDataSet;
use Give\Log\Log;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\Requests\GetAccessToken;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use RuntimeException;
use UnexpectedValueException;

class PayPalAuth
{
    /**
     * @since 2.9.0
     *
     * @var PayPalClient
     */
    private $payPalClient;

    /**
     * @since 2.9.0
     *
     * @var ConnectClient
     */
    private $connectClient;

    /**
     * PayPalAuth constructor.
     *
     * @since 2.9.0
     *
     * @param PayPalClient $payPalClient
     * @param ConnectClient $connectClient
     */
    public function __construct(PayPalClient $payPalClient, ConnectClient $connectClient)
    {
        $this->payPalClient = $payPalClient;
        $this->connectClient = $connectClient;
    }

    /**
     * Retrieves a token for the Client ID and Secret
     *
     * @since 2.32.0 Use PayPal client for rest api calls.
     * @since 2.25.0 Validate paypal response.
     * @since 2.9.0
     *
     * @param string $client_id
     * @param string $client_secret
     *
     * @throws RuntimeException|\Exception
     */
    public function getTokenFromClientCredentials($client_id, $client_secret): array
    {
        $auth = base64_encode("$client_id:$client_secret");

        $response = $this->payPalClient->getHttpClient()
            ->execute(new GetAccessToken(
                ['grant_type' => 'client_credentials'],
                ['Authorization' => "Basic $auth"]
            ));

        if (200 !== $response->statusCode) {
            give(Log::class)->http(
                'PayPal Commerce: Error retrieving access token with client credentials',
                [
                    'category' => 'Payment Gateway',
                    'source' => 'Paypal Commerce',
                    'response' => $response,
                ]
            );

            throw new RuntimeException('PayPal Commerce: Error retrieving access token with client credentials');
        }

        $result = (array)$response->result;

        $this->validateAccessToken($result);

        return ArrayDataSet::camelCaseKeys($result);
    }

    /**
     * Retrieves a token from the authorization code
     *
     * @since 2.32.0 Use PayPal client for rest api calls.
     * @since 2.9.0
     *
     * @param string $authCode
     * @param string $sharedId
     * @param string $nonce
     *
     * @return array|null
     * @throws RuntimeException|\Exception
     */
    public function getTokenFromAuthorizationCode($authCode, $sharedId, $nonce)
    {
        $auth = base64_encode($sharedId);

        $response = $this->payPalClient->getHttpClient()
            ->execute(new GetAccessToken(
                [
                    'grant_type' => 'authorization_code',
                    'code' => $authCode,
                    'code_verifier' => $nonce, // Seller nonce.
                ],
                ['Authorization' => "Basic $auth"]
            ));

        if (200 !== $response->statusCode) {
            give(Log::class)->http(
                'PayPal Commerce: Error retrieving access token with authorization code',
                [
                    'category' => 'Payment Gateway',
                    'source' => 'Paypal Commerce',
                    'response' => $response,
                ]
            );

            throw new RuntimeException('PayPal Commerce: Error retrieving access token with authorization code');
        }

        $result = (array)$response->result;

        $this->validateSellerAccessToken($result);

        return ArrayDataSet::camelCaseKeys($result);
    }

    /**
     * Retrieves a Partner Link for on-boarding
     *
     * @since 3.0.0 Implement $accountType. This parameter is required by GiveWP gateway server.
     * @since 2.9.0
     *
     * @return array|null
     */
    public function getSellerPartnerLink($returnUrl, $accountType)
    {
        $response = wp_remote_retrieve_body(
            wp_remote_post(
                sprintf(
                    $this->connectClient->getApiUrl('paypal?mode=%1$s&request=partner-link'),
                    $this->payPalClient->getMode()
                ),
                [
                    'body' => [
                        'return_url' => $returnUrl,
                        'account_type' => $accountType,
                    ],
                ]
            )
        );

        return empty($response) ? null : json_decode($response, true);
    }

    /**
     * Get seller on-boarding details from seller.
     *
     * @since 2.9.0
     *
     * @param string $accessToken
     *
     * @param string $merchantId
     *
     * @return array
     */
    public function getSellerOnBoardingDetailsFromPayPal($merchantId, $accessToken)
    {
        $request = wp_remote_post(
            $this->connectClient->getApiUrl(
                sprintf(
                    'paypal?mode=%1$s&request=seller-status',
                    $this->payPalClient->getMode()
                )
            ),
            [
                'body' => [
                    'merchant_id' => $merchantId,
                    'token' => $accessToken,
                ],
            ]
        );

        return json_decode(wp_remote_retrieve_body($request), true);
    }

    /**
     * Get seller rest API credentials
     *
     * @since 2.9.0
     *
     * @param string $accessToken
     *
     * @return array
     */
    public function getSellerRestAPICredentials($accessToken)
    {
        $request = wp_remote_post(
            $this->connectClient->getApiUrl(
                sprintf(
                    'paypal?mode=%1$s&request=seller-credentials',
                    $this->payPalClient->getMode()
                )
            ),
            [
                'body' => [
                    'token' => $accessToken,
                ],
            ]
        );

        return json_decode(wp_remote_retrieve_body($request), true);
    }

    /**
     * Validate PayPal access token.
     *
     * Sample PayPal access token: https://developer.paypal.com/api/rest/authentication/#link-sampleresponse
     *
     * @since 3.5.1 removed app_id from required keys as PayPal no longer supplies it
     * @since 2.25.0
     *
     * @param array $accessToken Access token response from PayPal.
     *
     * @return void
     * @throws UnexpectedValueException
     */
    private function validateAccessToken(array $accessToken)
    {
        $requiredKeys = [
            'scope',
            'access_token',
            'token_type',
            'expires_in',
            'nonce'
        ];

        if (array_diff($requiredKeys, array_keys($accessToken))) {
            give(Log::class)->error(
                'PayPal Commerce: Invalid access token',
                [
                    'category' => 'Payment Gateway',
                    'source' => 'Paypal Commerce',
                    'response' => $accessToken,
                ]
            );

            throw new UnexpectedValueException('PayPal Commerce: Error retrieving access token');
        }
    }

    /**
     * Validate PayPal seller access token.
     *
     * Sample PayPal access token: https://developer.paypal.com/docs/multiparty/seller-onboarding/build-onboarding/#link-sampleresponse
     *
     * @since 2.32.0
     *
     * @param array $sellerAccessToken Seller access token response from PayPal.
     *
     * @return void
     * @throws UnexpectedValueException
     */
    private function validateSellerAccessToken(array $sellerAccessToken)
    {
        $requiredKeys = [
            'scope',
            'access_token',
            'token_type',
            'refresh_token',
            'expires_in',
            'nonce'
        ];

        if (array_diff($requiredKeys, array_keys($sellerAccessToken))) {
            give(Log::class)->error(
                'PayPal Commerce: Invalid seller access token',
                [
                    'category' => 'Payment Gateway',
                    'source' => 'Paypal Commerce',
                    'response' => $sellerAccessToken,
                ]
            );

            throw new UnexpectedValueException('PayPal Commerce: Error retrieving seller access token');
        }
    }
}

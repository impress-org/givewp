<?php

namespace Give\PaymentGateways\PayPalCommerce\Repositories;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\ConnectServer\Client\ConnectClient;
use Give\Helpers\ArrayDataSet;
use Give\Log\Log;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;

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
     * @since 2.25.0 Validate paypal response.
     * @since 2.9.0
     *
     * @param string $client_id
     * @param string $client_secret
     *
     * @throws Exception
     */
    public function getTokenFromClientCredentials($client_id, $client_secret): array
    {
        $auth = base64_encode("$client_id:$client_secret");

        $request = wp_remote_post(
            $this->payPalClient->getApiUrl('v1/oauth2/token'),
            [
                'headers' => [
                    'Authorization' => "Basic $auth",
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'grant_type' => 'client_credentials',
                ],
            ]
        );

        if (200 !== wp_remote_retrieve_response_code($request)) {
            give(Log::class)->http(
                'PayPal Commerce: Error retrieving access token',
                [
                    'category' => 'Payment Gateway',
                    'source' => 'Paypal Commerce',
                    'response' => $request,
                ]
            );

            throw new Exception('PayPal Commerce: Error retrieving access token');
        }

        $decodedResponse = json_decode(wp_remote_retrieve_body($request), true);

        $this->validateAccessToken($decodedResponse);

        return ArrayDataSet::camelCaseKeys($decodedResponse);
    }

    /**
     * Retrieves a token from the authorization code
     *
     * @since 2.9.0
     *
     * @param string $authCode
     * @param string $sharedId
     * @param string $nonce
     *
     * @return array|null
     */
    public function getTokenFromAuthorizationCode($authCode, $sharedId, $nonce)
    {
        $response = wp_remote_retrieve_body(
            wp_remote_post(
                $this->payPalClient->getApiUrl('v1/oauth2/token'),
                [
                    'headers' => [
                        'Authorization' => sprintf(
                            'Basic %1$s',
                            base64_encode($sharedId)
                        ),
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'body' => [
                        'grant_type' => 'authorization_code',
                        'code' => $authCode,
                        'code_verifier' => $nonce, // Seller nonce.
                    ],
                ]
            )
        );

        return empty($response) ? null : ArrayDataSet::camelCaseKeys(json_decode($response, true));
    }

    /**
     * Retrieves a Partner Link for on-boarding
     *
     * @since 2.9.0
     *
     * @return array|null
     */
    public function getSellerPartnerLink($returnUrl, $country)
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
                        'country_code' => $country,
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
     * Sample paypal access token: https://developer.paypal.com/api/rest/authentication/#link-sampleresponse
     *
     * @since 2.25.0
     *
     * @param array $accessToken Access token response from PayPal.
     *
     * @return void
     * @throws Exception
     */
    private function validateAccessToken(array $accessToken)
    {
        $requiredKeys = [
            'scope',
            'access_token',
            'token_type',
            'app_id',
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

            throw new Exception('PayPal Commerce: Error retrieving access token');
        }
    }
}

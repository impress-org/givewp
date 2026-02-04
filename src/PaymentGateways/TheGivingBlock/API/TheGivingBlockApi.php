<?php

namespace Give\PaymentGateways\TheGivingBlock\API;

use Exception;
//use GiveTgb\Http\ConnectClient;
use Give\Framework\Http\ConnectServer\Client\ConnectClient;

/**
 * @unreleased
 */
class TheGivingBlockApi
{
    /**
     * @unreleased
     *
     * @see https://docs.thegivingblock.com/reference/post_v1-non-profit-onboarding-1
     */
    public static function nonProfitOnboarding(array $organizationData): array
    {
        try {
            $response = self::makeApiRequest(
                '/the-giving-block/non-profit-onboarding',
                'POST',
                [
                    'organizationData' => $organizationData,
                ]
            );

            return $response;
        } catch (Exception $e) {
            return [
                'code' => 500,
                'body' => wp_json_encode(['error' => $e->getMessage()]),
                'data' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * @unreleased
     *
     * @see https://docs.thegivingblock.com/reference/post_v1-non-profit-onboarding-organizationid-crypto-1
     */
    public static function nonProfitCryptoOnboarding(string $organizationId): array
    {
        try {
            $response = self::makeApiRequest(
                '/the-giving-block/non-profit-onboarding/crypto',
                'POST',
                [
                    'organizationId' => $organizationId,
                ]
            );

            return $response;
        } catch (Exception $e) {
            return [
                'code' => 500,
                'body' => wp_json_encode(['error' => $e->getMessage()]),
                'data' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * @unreleased
     *
     * @see https://docs.thegivingblock.com/reference/post_v1-non-profit-onboarding-organizationid-stock-1
     */
    public static function nonProfitStockOnboarding(string $organizationId): array
    {
        try {
            $response = self::makeApiRequest(
                '/the-giving-block/non-profit-onboarding/stock',
                'POST',
                [
                    'organizationId' => $organizationId,
                ]
            );

            return $response;
        } catch (Exception $e) {
            return [
                'code' => 500,
                'body' => wp_json_encode(['error' => $e->getMessage()]),
                'data' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * @unreleased
     *
     * @see https://docs.thegivingblock.com/reference/get_v1-organization-id-1
     */
    public static function getOrganizationById(string $organizationId): array
    {
        try {
            $response = self::makeApiRequest(
                '/the-giving-block/organization/' . $organizationId,
                'GET'
            );

            return $response;
        } catch (Exception $e) {
            return [
                'code' => 500,
                'body' => wp_json_encode(['error' => $e->getMessage()]),
                'data' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * TGB Connect mode (sandbox vs live). Uses GIVE_TGB_CONNECT_MODE when defined.
     *
     * @unreleased
     */
    private static function getMode(): string
    {
        if (defined('GIVE_TGB_CONNECT_MODE') && GIVE_TGB_CONNECT_MODE !== '') {
            return strtolower((string) GIVE_TGB_CONNECT_MODE);
        }
        return 'live';
    }

    /**
     * @unreleased
     */
    private static function getSslVerify(): bool
    {
        return self::getMode() !== 'sandbox';
    }

    /**
     * @unreleased
     */
    private static function getTimeout(): int
    {
        return 30;
    }

    /**
     * @unreleased
     * @throws Exception
     */
    private static function makeApiRequest(string $endpoint, string $method = 'POST', array $body = []): array
    {
        $mode = self::getMode();

        $body['mode'] = $mode;

        try {
            if ($method === 'GET') {
                if (!empty($body)) {
                    $endpoint = $endpoint . '?' . http_build_query($body);
                }

                $response = give(ConnectClient::class)->get(
                    $endpoint,
                    [
                        'headers' => [
                            'Accepts' => 'application/json',
                        ],
                        'timeout' => self::getTimeout(),
                        'sslverify' => self::getSslVerify(),
                    ]
                );
            } else {
                $response = give(ConnectClient::class)->post(
                    $endpoint,
                    [
                        'body' => $body,
                        'headers' => [
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ],
                        'timeout' => self::getTimeout(),
                        'sslverify' => self::getSslVerify(),
                    ]
                );
            }

            $code = wp_remote_retrieve_response_code($response);
            $bodyResponse = wp_remote_retrieve_body($response);
            $data = json_decode($bodyResponse, true);

            return [
                'code' => $code,
                'body' => $bodyResponse,
                'data' => $data,
            ];
        } catch (Exception $e) {
            // Log error only in development/debug mode
            // Note: error_log() is intentionally conditional to avoid production logging
            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log(sprintf('[The Giving Block] API request error. Endpoint: %s, Method: %s, Error: %s', $endpoint, $method, $e->getMessage()));
            }
            throw $e;
        }
    }
}

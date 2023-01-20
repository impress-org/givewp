<?php

namespace Give\PaymentGateways;

use Give\Framework\Exceptions\Primitives\Exception;
use WP_Error;

/**
 * @unreleased
 */
class GatewayServerClient
{

    /**
     * @unreleased
     */
    public function getServerUrl(string $path = ''): string
    {
        return self::serverUrl($path);
    }

    /**
     * @see https://developer.wordpress.org/reference/functions/wp_remote_get/
     *
     * @unreleased
     *
     * @throws Exception
     */
    public function get(string $path, $args = []): array
    {
        return self::getRequest($path, $args);
    }

    /**
     * @see https://developer.wordpress.org/reference/functions/wp_remote_post/
     *
     * @unreleased
     *
     * @throws Exception
     */
    public function post(string $path, $args = []): array
    {
        return self::postRequest($path, $args);
    }

    /**
     * @see https://developer.wordpress.org/reference/functions/wp_remote_request/
     *
     * @unreleased
     *
     * @throws Exception
     */
    public function request(string $path, $args = []): array
    {
        return self::customRequest($path, $args);
    }

    /**
     * @unreleased
     */
    public static function serverUrl(string $path = ''): string
    {
        $url = defined('GIVE_GATEWAY_SERVER_URL') ? GIVE_GATEWAY_SERVER_URL : 'https://connect.givewp.com';

        if ( ! empty($path)) {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }

    /**
     * @see https://developer.wordpress.org/reference/functions/wp_remote_get/
     *
     * @unreleased
     *
     * @throws Exception
     */
    public static function getRequest(string $path, $args = []): array
    {
        $url = self::serverUrl($path);

        $response = wp_remote_get($url, $args);

        self::validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @see https://developer.wordpress.org/reference/functions/wp_remote_post/
     *
     * @unreleased
     *
     * @throws Exception
     */
    public static function postRequest(string $path, $args = []): array
    {
        $url = self::serverUrl($path);

        $response = wp_remote_post($url, $args);

        self::validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @see https://developer.wordpress.org/reference/functions/wp_remote_request/
     *
     * @unreleased
     *
     * @throws Exception
     */
    public static function customRequest(string $path, $args = []): array
    {
        $url = self::serverUrl($path);

        $response = wp_remote_request($url, $args);

        self::validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @unreleased
     *
     * @param array|WP_Error $response
     *
     * @throws Exception
     */
    private static function validateWpErrorInResponse($response)
    {
        if (is_wp_error($response)) {
            throw new Exception(
                esc_html__(
                    sprintf(
                        'Request to gateways server failed. Error:  %1$s',
                        $response->get_error_message()
                    )
                )
            );
        }
    }
}

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
     *
     * @param string $path The path for the route on the gateway server. E.g: 'gateway-name/endpoint'
     */
    public static function url(string $path = ''): string
    {
        $url = defined('GIVE_GATEWAY_SERVER_URL') ? GIVE_GATEWAY_SERVER_URL : 'https://connect.givewp.com';

        if ( ! empty($path)) {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }

    /**
     * @unreleased
     *
     * @param string $path The path for the route on the gateway server. E.g: 'gateway-name/endpoint'
     * @param array  $args Optional. Can contains 'headers' and 'body'
     *
     * @throws Exception
     */
    public static function get(string $path, array $args = []): array
    {
        $url = self::url($path);

        $response = wp_remote_get($url, $args);

        self::validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @unreleased
     *
     * @param string $path The path for the route on the gateway server. E.g: 'gateway-name/endpoint'
     * @param array  $args Optional. Can contains 'headers' and 'body'
     *
     * @throws Exception
     */
    public static function post(string $path, array $args = []): array
    {
        $url = self::url($path);

        $response = wp_remote_post($url, $args);

        self::validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @unreleased
     *
     * @param string $path The path for the route on the gateway server. E.g: 'gateway-name/endpoint'
     * @param array  $args Optional. Can contains 'method', 'headers' and 'body'
     *
     * @throws Exception
     */
    public static function request(string $path, array $args = []): array
    {
        $url = self::url($path);

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
                        'The request to the gateway server failed. Error:  %1$s',
                        $response->get_error_message()
                    )
                )
            );
        }
    }
}

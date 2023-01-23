<?php

namespace Give\ConnectClient;

use Give\Framework\Exceptions\Primitives\Exception;
use WP_Error;

/**
 * Class ConnectClient
 *
 * @since   2.8.0
 */
class ConnectClient
{
    /**
     * Api Url
     *
     * @since 2.8.0
     *
     * @var string
     */
    public $apiUrl = 'https://connect.givewp.com';

    /**
     * @unreleased
     */
    public function __construct(string $giveConnectUrl = '')
    {
        if ($giveConnectUrl) {
            $this->apiUrl = $giveConnectUrl;
        } elseif (defined('GIVE_CONNECT_URL') && GIVE_CONNECT_URL) {
            $this->apiUrl = GIVE_CONNECT_URL;
        }
    }

    /**
     * Get rest api endpoint url for requests.
     *
     * @since      2.8.0
     *
     * @param string $endpoint The route on the server. E.g: 'gateway-name/connect'
     */
    public function getApiUrl(string $endpoint = ''): string
    {
        if ( ! empty($endpoint)) {
            return $this->apiUrl . '/' . ltrim($endpoint, '/');
        }

        return $this->apiUrl;
    }

    /**
     * @unreleased
     *
     * @param string $endpoint The route on the server. E.g: 'gateway-name/connect'
     * @param array  $args     Optional. Can contains 'headers' and 'body'
     *
     * @throws Exception
     */
    public function get(string $endpoint, array $args = []): array
    {
        $url = $this->getApiUrl($endpoint);

        $response = wp_remote_get($url, $args);

        $this->validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @unreleased
     *
     * @param string $endpoint The route on the server. E.g: 'gateway-name/connect'
     * @param array  $args     Optional. Can contains 'headers' and 'body'
     *
     * @throws Exception
     */
    public function post(string $endpoint, array $args = []): array
    {
        $url = $this->getApiUrl($endpoint);

        $response = wp_remote_post($url, $args);

        $this->validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @unreleased
     *
     * @param string $endpoint The route on the server. E.g: 'gateway-name/connect'
     * @param array  $args     Optional. Can contains 'method', 'headers' and 'body'
     *
     * @throws Exception
     */
    public function request(string $endpoint, array $args = []): array
    {
        $url = $this->getApiUrl($endpoint);

        $response = wp_remote_request($url, $args);

        $this->validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @unreleased
     *
     * @param array|WP_Error $response
     *
     * @throws Exception
     */
    private function validateWpErrorInResponse($response)
    {
        if (is_wp_error($response)) {
            throw new Exception(
                esc_html__(
                    sprintf(
                        'The request to the %1$s failed. Error:  %2$s',
                        $this->apiUrl,
                        $response->get_error_message()
                    )
                )
            );
        }
    }
}

<?php

namespace Give\Framework\Http\ConnectServer\Client;

use Give\Framework\Http\ConnectServer\Client\Exceptions\RequestException;
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
    public $apiUrl;

    /**
     * @since 2.25.0
     */
    public function __construct($giveConnectUrl)
    {
        $this->apiUrl = $giveConnectUrl;
    }

    /**
     * Get rest api endpoint url for requests.
     *
     * @since 2.25.0 Set default endpoint value as empty
     * @since      2.8.0
     *
     * @param string $endpoint The route on the server. E.g: 'gateway-name/connect'
     */
    public function getApiUrl(string $endpoint = ''): string
    {
        if ( ! empty($endpoint)) {
            return trailingslashit($this->apiUrl) . ltrim($endpoint, '/');
        }

        return $this->apiUrl;
    }

    /**
     * @since 2.25.0
     *
     * @param string $endpoint The route on the server. E.g: 'gateway-name/connect'
     * @param array  $args     Optional. Can contains 'headers' and 'body'
     *
     * @throws RequestException
     */
    public function get(string $endpoint, array $args = []): array
    {
        $url = $this->getApiUrl($endpoint);

        $response = wp_remote_get($url, $args);

        $this->validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @since 2.25.0
     *
     * @param string $endpoint The route on the server. E.g: 'gateway-name/connect'
     * @param array  $args     Optional. Can contains 'headers' and 'body'
     *
     * @throws RequestException
     */
    public function post(string $endpoint, array $args = []): array
    {
        $url = $this->getApiUrl($endpoint);

        $response = wp_remote_post($url, $args);

        $this->validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @since 2.25.0
     *
     * @param string $endpoint The route on the server. E.g: 'gateway-name/connect'
     * @param array  $args     Optional. Can contains 'method', 'headers' and 'body'
     *
     * @throws RequestException
     */
    public function request(string $endpoint, array $args = []): array
    {
        $url = $this->getApiUrl($endpoint);

        $response = wp_remote_request($url, $args);

        $this->validateWpErrorInResponse($response);

        return $response;
    }

    /**
     * @since 2.25.0
     *
     * @param array|WP_Error $response
     *
     * @throws RequestException
     */
    private function validateWpErrorInResponse($response)
    {
        if (is_wp_error($response)) {
            throw new RequestException(
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

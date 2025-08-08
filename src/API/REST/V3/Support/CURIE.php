<?php

namespace Give\API\REST\V3\Support;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @since 4.4.0
 *
 * WordPress version 4.5 introduced support for Compact URIs, or CURIEs. This makes it possible to
 * reference links by a much simpler identifier than the full URL which could easily be quite lengthy.
 *
 * This will convert link URLs from https://api.mypluginurl.com/my_link` to my_plugin:my_linkin the API response.
 * The full URL must still be used when adding links using WP_REST_Response::add_link() `.
 */
class CURIE
{
    /**
     * @var string
     */
    private static $baseUrl = 'https://relations.givewp.com/';

    /**
     * @since 4.4.0
     *
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/modifying-responses/#registering-a-curie
     */
    public function registerCURIE($curies): array
    {
        $curies[] = [
            'name' => 'givewp',
            'href' => trailingslashit(self::$baseUrl) . '{rel}',
            'templated' => true,
        ];

        return $curies;
    }

    /**
     * @since 4.4.0
     *
     * To use the $response->add_link() with a custom link, you need to use a URI that is under your control, so GiveWP
     * uses it to generate the URL, which is transformed into givewp:$rel when generating the response by using a CURIE.
     *
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/modifying-responses/#adding-links-to-the-api-response
     *
     * @throws Exception
     */
    public static function relationUrl(string $rel): string
    {
        if (wp_http_validate_url($rel)) {
            throw new Exception(__('The $rel value should be a unique identifier, not a full URL.', 'give'));
        }

        return trailingslashit(self::$baseUrl) . $rel;
    }
}

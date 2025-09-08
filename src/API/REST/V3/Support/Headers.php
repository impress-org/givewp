<?php

namespace Give\API\REST\V3\Support;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Helper class to manage pagination headers for REST API responses.
 *
 * @since 4.8.0
 */
class Headers
{
    /**
     * Add pagination headers to a REST response.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Response $response The response object to add headers to
     * @param WP_REST_Request $request The request object
     * @param int $totalItems Total number of items
     * @param int $perPage Number of items per page
     * @param string $routeBase The route base for building pagination URLs
     *
     * @return WP_REST_Response The response with headers added
     */
    public static function addPagination(
        WP_REST_Response $response,
        WP_REST_Request $request,
        int $totalItems,
        int $perPage,
        string $routeBase
    ): WP_REST_Response {
        $page = $request->get_param('page');
        $totalPages = (int) ceil($totalItems / $perPage);

        // Add total headers
        $response->header('X-WP-Total', $totalItems);
        $response->header('X-WP-TotalPages', $totalPages);

        // Build base URL for pagination links
        $base = add_query_arg(
            map_deep($request->get_query_params(), function ($value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                return urlencode($value);
            }),
            rest_url($routeBase)
        );

        // Add prev link header
        if ($page > 1) {
            $prevPage = $page - 1;

            if ($prevPage > $totalPages) {
                $prevPage = $totalPages;
            }

            $response->link_header('prev', add_query_arg('page', $prevPage, $base));
        }

        // Add next link header
        if ($totalPages > $page) {
            $nextPage = $page + 1;
            $response->link_header('next', add_query_arg('page', $nextPage, $base));
        }

        return $response;
    }
}

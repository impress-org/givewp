<?php

namespace Give\Framework\ListTable;

use WP_REST_Request;
use WP_REST_Response;

/**
 * The totals of a list table can be asked for apart from its rows: a rows request sent with
 * `withCount=false` skips the total, and `<route>/count` answers it with the same filters.
 * The rows never depend on the total, so a slow count cannot hide data.
 *
 * @since TBD
 */
class ListCount
{
    /**
     * The REST argument that turns the total off for a rows request.
     *
     * @since TBD
     *
     * @return array<string, array<string, mixed>>
     */
    public static function arguments(): array
    {
        return [
            'withCount' => [
                'type' => 'boolean',
                'required' => false,
                'default' => true,
                'description' => 'Send false to skip the total: the response then has hasNextPage instead of totalItems and totalPages. Ask for the total on the /count route.',
            ],
        ];
    }

    /**
     * Whether the response must carry the total. True unless the request sent withCount=false.
     *
     * @since TBD
     */
    public static function isRequested(WP_REST_Request $request): bool
    {
        return $request->get_param('withCount') !== false;
    }

    /**
     * How many rows a page read asks for: one more than the page when the total is skipped, to
     * see whether another page follows.
     *
     * @since TBD
     */
    public static function limit(WP_REST_Request $request): int
    {
        return (int)$request->get_param('perPage') + (self::isRequested($request) ? 0 : 1);
    }

    /**
     * Drops the extra row a page read asked for.
     *
     * @since TBD
     *
     * @return array{0: array, 1: bool} the rows of the page and whether another page follows
     */
    public static function trim(WP_REST_Request $request, array $rows): array
    {
        if (self::isRequested($request)) {
            return [$rows, false];
        }

        $perPage = (int)$request->get_param('perPage');

        return [array_slice($rows, 0, $perPage), count($rows) > $perPage];
    }

    /**
     * The totals part of a response.
     *
     * @since TBD
     *
     * @return array{totalItems: int, totalPages: int}
     */
    public static function totals(int $total, WP_REST_Request $request): array
    {
        return [
            'totalItems' => $total,
            'totalPages' => (int)ceil($total / max(1, (int)$request->get_param('perPage'))),
        ];
    }

    /**
     * The response of the count route.
     *
     * @since TBD
     */
    public static function response(int $total, WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response(self::totals($total, $request));
    }
}

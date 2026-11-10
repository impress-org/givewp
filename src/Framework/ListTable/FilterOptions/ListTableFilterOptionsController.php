<?php

namespace Give\Framework\ListTable\FilterOptions;

use Give\Framework\ListTable\Layouts\ListTableScreens;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Read-only options for the Campaign, Form and Donor list filters.
 *
 * An option is a record id and a plain text label. Donor options show the name only.
 *
 * @since TBD
 */
class ListTableFilterOptionsController
{
    public const NAMESPACE = 'givewp/v3';
    public const BASE = 'list-table-filter-options';
    public const MAX_INCLUDE = 50;

    /**
     * The highest page asked for. A larger one would overflow the offset and give page one back.
     *
     * @since TBD
     */
    public const MAX_PAGE = 10000;

    /**
     * @var ListTableScreens
     */
    private $screens;

    /**
     * @var FilterOptionSources
     */
    private $sources;

    /**
     * @since TBD
     */
    public function __construct(ListTableScreens $screens, FilterOptionSources $sources)
    {
        $this->screens = $screens;
        $this->sources = $sources;
    }

    /**
     * The route path the client calls, without a source.
     *
     * @since TBD
     */
    public static function path(): string
    {
        return '/' . self::NAMESPACE . '/' . self::BASE;
    }

    /**
     * @since TBD Limit `page` to MAX_PAGE.
     * @since TBD
     */
    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/' . self::BASE . '/(?P<source>campaign|form|donor)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getItems'],
                'permission_callback' => [$this, 'permissionsCheck'],
                'args' => [
                    'screen' => [
                        'description' => __('The list table screen key the filter is on.', 'give'),
                        'type' => 'string',
                    ],
                    'search' => [
                        'description' => __('Matches the start of any word of the label.', 'give'),
                        'type' => 'string',
                        'default' => '',
                        'maxLength' => 100,
                        'validate_callback' => 'rest_validate_request_arg',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'page' => [
                        'description' => __('The page of options. A page holds 20 options.', 'give'),
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1,
                        'maximum' => self::MAX_PAGE,
                    ],
                    'include' => [
                        'description' => __('Ids to return the labels of. Search and page are ignored.', 'give'),
                        'type' => 'array',
                        'default' => [],
                        'maxItems' => self::MAX_INCLUDE,
                        'items' => ['type' => 'integer', 'minimum' => 1],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Signed in (401), then the screen if one is given (404, 400, 403), then the source's own permission (403).
     *
     * @since TBD
     *
     * @return true|WP_Error
     */
    public function permissionsCheck(WP_REST_Request $request)
    {
        if (!is_user_logged_in()) {
            return new WP_Error(
                'rest_forbidden',
                __('You must be signed in to load filter options.', 'give'),
                ['status' => 401]
            );
        }

        $source = (string)$request->get_param('source');
        $screen = $request->get_param('screen');

        if (!$this->sources->has($source)) {
            return $this->forbidden();
        }

        if ($screen !== null && $screen !== '') {
            $screen = (string)$screen;

            if (!$this->screens->has($screen)) {
                return new WP_Error(
                    'give_list_table_unknown_screen',
                    __('This list table screen does not exist.', 'give'),
                    ['status' => 404]
                );
            }

            if (!$this->sources->isOfferedOn($source, $screen)) {
                return new WP_Error(
                    'give_list_table_invalid_filter_source',
                    __('This list does not offer that filter.', 'give'),
                    ['status' => 400]
                );
            }

            return $this->screens->currentUserCanView($screen) ? true : $this->forbidden();
        }

        return $this->sources->get($source)->currentUserCanView() ? true : $this->forbidden();
    }

    /**
     * @since TBD
     */
    public function getItems(WP_REST_Request $request): WP_REST_Response
    {
        $source = $this->sources->get((string)$request->get_param('source'));
        $include = array_map('intval', (array)$request->get_param('include'));

        if ($include) {
            return new WP_REST_Response(['options' => $source->labels($include), 'hasMore' => false], 200);
        }

        $result = $source->search((string)$request->get_param('search'), (int)$request->get_param('page'));

        return new WP_REST_Response(['options' => $result['options'], 'hasMore' => $result['hasMore']], 200);
    }

    /**
     * @since TBD
     */
    private function forbidden(): WP_Error
    {
        return new WP_Error(
            'rest_forbidden',
            __('You do not have permission to use this filter.', 'give'),
            ['status' => 403]
        );
    }
}

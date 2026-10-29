<?php

namespace Give\Framework\ListTable\Layouts;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Reads, replaces and resets the signed-in user's layout for one list table screen.
 *
 * The route has no user parameter, so a request can only ever touch the caller's own record.
 *
 * @since TBD
 */
class ListTableLayoutController
{
    public const NAMESPACE = 'givewp/v3';
    public const BASE = 'list-table-layouts';

    /**
     * @var ListTableScreens
     */
    private $screens;

    /**
     * @var ListTableLayoutStore
     */
    private $store;

    /**
     * @var ListTableLayoutValidator
     */
    private $validator;

    /**
     * @since TBD
     */
    public function __construct(
        ListTableScreens $screens,
        ListTableLayoutStore $store,
        ListTableLayoutValidator $validator
    ) {
        $this->screens = $screens;
        $this->store = $store;
        $this->validator = $validator;
    }

    /**
     * The route path the client calls, without a screen key.
     *
     * @since TBD
     */
    public static function path(): string
    {
        return '/' . self::NAMESPACE . '/' . self::BASE;
    }

    /**
     * @since TBD
     */
    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/' . self::BASE . '/(?P<screen>[a-z][a-z0-9-]{0,39})', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getItem'],
                'permission_callback' => [$this, 'permissionsCheck'],
            ],
            [
                'methods' => 'PUT',
                'callback' => [$this, 'updateItem'],
                'permission_callback' => [$this, 'permissionsCheck'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'deleteItem'],
                'permission_callback' => [$this, 'permissionsCheck'],
            ],
            'args' => [
                'screen' => [
                    'description' => __('The list table screen key.', 'give'),
                    'type' => 'string',
                    'required' => true,
                ],
            ],
        ]);
    }

    /**
     * Signed in (401), then a known screen (404), then the screen's capability (403).
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
                __('You must be signed in to save a list layout.', 'give'),
                ['status' => 401]
            );
        }

        $screen = (string)$request->get_param('screen');

        if (!$this->screens->has($screen)) {
            return new WP_Error(
                'give_list_table_unknown_screen',
                __('This list table screen does not exist.', 'give'),
                ['status' => 404]
            );
        }

        if (!$this->screens->currentUserCanView($screen)) {
            return new WP_Error(
                'rest_forbidden',
                __('You do not have permission to use this list.', 'give'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * @since TBD
     */
    public function getItem(WP_REST_Request $request): WP_REST_Response
    {
        $screen = $request->get_param('screen');

        return $this->response($screen, $this->store->get(get_current_user_id(), $screen));
    }

    /**
     * @since TBD
     *
     * @return WP_REST_Response|WP_Error
     */
    public function updateItem(WP_REST_Request $request)
    {
        $layout = $this->validator->validate($request->get_json_params());

        if (is_wp_error($layout)) {
            return $layout;
        }

        $screen = $request->get_param('screen');

        $this->store->save(get_current_user_id(), $screen, $layout);

        return $this->response($screen, $layout);
    }

    /**
     * @since TBD
     */
    public function deleteItem(WP_REST_Request $request): WP_REST_Response
    {
        $screen = $request->get_param('screen');

        $this->store->delete(get_current_user_id(), $screen);

        return $this->response($screen, null);
    }

    /**
     * @since TBD
     */
    private function response(string $screen, ?array $layout): WP_REST_Response
    {
        return new WP_REST_Response(['screen' => $screen, 'layout' => $layout], 200);
    }
}

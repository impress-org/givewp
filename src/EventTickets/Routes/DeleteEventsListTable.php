<?php

namespace Give\EventTickets\Routes;

use Give\EventTickets\ListTable\EventTicketsListTable;
use Give\EventTickets\Repositories\EventRepository;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 3.6.0
 */
class DeleteEventsListTable
{
    /**
     * @var string
     */
    protected $endpoint = 'events-tickets/events/list-table';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @var EventTicketsListTable
     */
    protected $listTable;

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'ids' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => function ($ids) {
                            foreach ($this->splitString($ids) as $id) {
                                if ( ! filter_var($id, FILTER_VALIDATE_INT)) {
                                    return false;
                                }
                            }

                            return true;
                        },
                    ],
                ],
            ]
        );
    }

    /**
     * @since 3.6.0
     * @throws Exception
     */
    public function handleRequest(WP_REST_Request $request): WP_Rest_Response
    {
        $ids = $this->splitString($request->get_param('ids'));
        $errors = [];
        $successes = [];

        foreach ($ids as $id) {
            $soldTicketsCount = give(EventRepository::class)->getById($id)->eventTickets()->count() ?? 0;

            if ($soldTicketsCount > 0) {
                $errors[] = $id;
                continue;
            }

            $eventDeleted = give(EventRepository::class)->getById($id)->delete();
            $eventDeleted ? $successes[] = $id : $errors[] = $id;
        }

        return new WP_REST_Response(array('errors' => $errors, 'successes' => $successes));
    }


    /**
     * Split string
     *
     * @since 3.6.0
     *
     * @return string[]
     */
    protected function splitString(string $ids): array
    {
        if (strpos($ids, ',')) {
            return array_map('trim', explode(',', $ids));
        }

        return [trim($ids)];
    }

        /**
     * @since 3.6.0
     *
     * @return bool|\WP_Error
     */
    public function permissionsCheck()
    {
        return current_user_can('delete_posts')?: new \WP_Error(
            'rest_forbidden',
            esc_html__("You don't have permission to delete Events", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
    }
}

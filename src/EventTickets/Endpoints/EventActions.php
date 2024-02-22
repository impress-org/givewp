<?php

namespace Give\EventTickets\Endpoints;

use Give\DonationForms\V2\Endpoints\Endpoint;
use Give\EventTickets\Repositories\EventRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class EventActions extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/event-tickets/(?P<action>[\S]+)';

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
                    'methods' => ['POST', 'UPDATE', 'DELETE'],
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'action' => [
                        'type' => 'string',
                        'required' => true,
                        'enum' => [
                            'delete',
                        ],
                    ],
                    'ids' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => function ($ids) {
                            foreach ($this->splitString($ids) as $id) {
                                if ( ! $this->validateInt($id)) {
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
     * @inheritDoc
     *
     * @unreleased
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('edit_give_forms')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You don\'t have permission to edit Events', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @unreleased
     */
    public function handleRequest(WP_REST_Request $request): WP_Rest_Response
    {
        $ids = $this->splitString($request->get_param('ids'));
        $errors = [];
        $successes = [];

        switch ($request->get_param('action')) {
            case 'delete':
                foreach ($ids as $id) {
                    $eventDeleted = give(EventRepository::class)->delete($id);
                    $eventDeleted ? $successes[] = $id : $errors[] = $id;
                }

                break;
        }

        return new WP_REST_Response(array('errors' => $errors, 'successes' => $successes));
    }


    /**
     * Split string
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
}

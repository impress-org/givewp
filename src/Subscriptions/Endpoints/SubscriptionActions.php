<?php

namespace Give\Subscriptions\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 *
 */
class SubscriptionActions extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/subscriptions/(?P<action>[\S]+)';

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
                    'methods' => ['POST'],
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
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
                    'status' => [
                        'type' => 'string',
                        'required' => false,
                        'enum' => [
                            'active',
                            'expired',
                            'completed',
                            'cancelled',
                            'pending',
                            'failing',
                            'suspended',
                            'abandoned',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $ids = $this->splitString($request->get_param('ids'));
        $errors = $successes = [];

                foreach ($ids as $id) {
                    $updated = give_recurring_update_subscription_status( $id, $request->get_param('status'));

                    $updated ? $successes[] = $id : $errors[] = $id;
                }



        return new WP_REST_Response([
            'errors' => $errors,
            'successes' => $successes
        ]);
    }


    /**
     * Split string
     *
     *
     * @param string $ids
     *
     * @return string[]
     */
    protected function splitString($ids)
    {
        if (strpos($ids, ',')) {
            return array_map('trim', explode(',', $ids));
        }

        return [trim($ids)];
    }
}

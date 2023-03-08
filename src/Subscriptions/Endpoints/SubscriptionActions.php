<?php

namespace Give\Subscriptions\Endpoints;

use Exception;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.24.0
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
                    'methods' => ['POST', 'DELETE'],
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'action' => [
                        'type' => 'string',
                        'required' => true,
                        'enum' => [
                            'delete',
                            'setStatus',
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
                    'status' => [
                        'type' => 'string',
                        'required' => false,
                        'enum' => array_values(SubscriptionStatus::toArray()),
                    ],
                ],
            ]
        );
    }

    /**
     * @since 2.25.2
     *
     * @inheritDoc
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('edit_give_payments')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You don\'t have permission to edit Subscriptions', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @since 2.24.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $ids = $this->splitString($request->get_param('ids'));
        $errors = $successes = [];

        switch ($request->get_param('action')) {
            case 'delete':
                foreach ($ids as $id) {
                    $subscription = Subscription::find($id);

                    if ( ! $subscription) {
                        $errors[] = $id;
                        continue;
                    }

                    try {
                        $subscription->delete();
                        $successes[] = $id;
                    } catch (Exception $e) {
                        $errors[] = $id;
                    }
                }

                break;

            case 'setStatus':
                foreach ($ids as $id) {
                    $subscription = Subscription::find($id);

                    if ( ! $subscription) {
                        $errors[] = $id;
                        continue;
                    }

                    try {
                        $subscription->status = new SubscriptionStatus($request->get_param('status'));
                        $subscription->save();
                        $successes[] = $id;
                    } catch (Exception $e) {
                        $errors[] = $id;
                    }
                }

                break;
        }

        return new WP_REST_Response([
            'errors' => $errors,
            'successes' => $successes
        ]);
    }


    /**
     * Split string
     *
     * @since 2.24.0
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

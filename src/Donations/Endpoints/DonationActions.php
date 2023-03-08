<?php

namespace Give\Donations\Endpoints;

use Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class DonationActions extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donations/(?P<action>[\S]+)';

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
                            'resendEmailReceipt',
                        ],
                    ],
                    'ids' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => function ($ids) {
                            foreach ($this->splitString($ids) as $id) {
                                if (!$this->validateInt($id)) {
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
                            'publish', // Completed
                            'pending',
                            'processing',
                            'refunded',
                            'revoked',
                            'failed',
                            'cancelled',
                            'abandoned',
                            'preapproval',
                        ],
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
                esc_html__('You don\'t have permission to edit Donations', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @since 2.20.0
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
                    try {
                        give_delete_donation($id);
                        $successes[] = $id;
                    } catch (Exception $e) {
                        $errors[] = $id;
                    }
                }

                break;

            case 'setStatus':
                foreach ($ids as $id) {
                    $updated = give_update_payment_status($id, $request->get_param('status'));
                    $updated ? $successes[] = $id : $errors[] = $id;
                }

                break;

            case 'resendEmailReceipt':
                foreach ($ids as $id) {
                    try {
                        do_action('give_donation-receipt_email_notification', $id);
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
     * @param string $ids
     * @since 2.20.0
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

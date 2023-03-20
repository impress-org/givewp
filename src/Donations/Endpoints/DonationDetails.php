<?php

namespace Give\Donations\Endpoints;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/*
 * @unreleased
 */
class DonationDetails extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donation-details/(?P<id>[\d]+)';

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'id' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => function ($id) {
                            if (!$this->validateInt($id)) {
                                return false;
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
                            'preapproval'
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
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
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response|WP_Error
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $id = $request->get_param('id');

        $donation = give()->donations->getById($id);

        if (!$donation) {
            return new WP_Error(
                'donation_not_found',
                __('Donation not found.', 'give'),
                ['status' => 404]
            );
        }

        try {
            $status = $request->get_param('status');
            if ($status) {
                $donation->status = new DonationStatus($status);
            }

            $donation->save();
        } catch (Exception $e) {
            return new WP_Error(
                'donation_update_failed',
                __('Donation update failed.', 'give'),
                ['status' => 500]
            );
        }

        return new WP_REST_Response(
            [
                'success' => true
            ]
        );
    }
}

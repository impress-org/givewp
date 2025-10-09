<?php

namespace Give\Donors\Endpoints;

use Exception;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorStatus;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class UpdateStatus extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donors/status';

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
                    'methods' => 'POST',
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
                        'enum' => [
                            DonorStatus::ACTIVE,
                            DonorStatus::TRASH,
                        ],
                        'required' => true,
                    ],
                ],
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function permissionsCheck()
    {
        if (current_user_can('manage_options') || current_user_can('edit_give_payments')) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__('You don\'t have permission to update Donors status', 'give'),
            ['status' => $this->authorizationStatusCode()]
        );
    }

    /**
     * @unreleased
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $ids = array_map('intval', $this->splitString($request->get_param('ids')));
        $status = $request->get_param('status');
        $errors = $successes = [];

        foreach ($ids as $id) {
            try {
                /**
                 * Fires before updating donor status.
                 *
                 * @param int  $id     The ID of the donor.
                 * @param bool $status Confirm Donor related donations deletion.
                 */
                do_action('give_pre_donor_status_update', $id, $status);
                $donor = Donor::find($id);
                $donorStatus = new DonorStatus($status);
                $donor->status = $donorStatus;
                $donor->save();
                $successes[] = $id;
            } catch (Exception $e) {
                $errors[] = $id;
            }
        }

        return new WP_REST_Response([
            'errors' => $errors,
            'successes' => $successes,
        ]);
    }
}

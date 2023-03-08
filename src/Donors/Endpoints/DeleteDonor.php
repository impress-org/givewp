<?php

namespace Give\Donors\Endpoints;

use Exception;
use Give\Donors\Models\Donor;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class DeleteDonor extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donors/delete';

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
                    'methods' => 'DELETE',
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
                    'deleteDonationsAndRecords' => [
                        'type' => 'boolean',
                        'required' => 'false',
                        'default' => 'false',
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
        if ( ! current_user_can('edit_give_donors')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You don\'t have permission to edit Donors', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @since 2.20.0
     * @since 2.23.1 Cast `$ids` as integers.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $ids = array_map('intval', $this->splitString($request->get_param('ids')));
        $delete_donation = $request->get_param('deleteDonationsAndRecords');
        $errors = $successes = [];

        foreach ($ids as $id) {
            try {
                /**
                 * Fires before deleting donor.
                 *
                 * @since 2.20.0
                 *
                 * @param int  $donor_id        The ID of the donor.
                 * @param bool $delete_donor    Confirm Donor Deletion.
                 * @param bool $delete_donation Confirm Donor related donations deletion.
                 */
                do_action('give_pre_delete_donor', $id, true, $delete_donation);
                $donor = Donor::find($id);
                if ($delete_donation) {
                    foreach ($donor->donations as $donation) {
                        $donation->delete();
                    }
                } else {
                    give_update_payment_meta($id, '_give_payment_donor_id', 0);
                }
                $donor->delete();
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


    /**
     * Split string
     *
     * @since 2.20.0
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

<?php

namespace Give\Donors\Endpoints;

use Exception;
use Give\Donors\Models\Donor;
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
                                if (!$this->validateInt($id)) {
                                    return false;
                                }
                            }

                            return true;
                        },
                    ],
                    'deleteDonationsAndRecords' => [
                        'type' => 'boolean',
                        'required' => 'false',
                        'default' => 'false'
                    ]
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @since 2.20.0
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $ids = $this->splitString($request->get_param('ids'));
        $delete_donation = $request->get_param('deleteDonationsAndRecords');
        $errors = $successes = [];

        foreach ($ids as $id) {
            try {
                /**
                 * Fires before deleting donor.
                 *
                 * @param int  $donor_id     The ID of the donor.
                 * @param bool $delete_donor Confirm Donor Deletion.
                 * @param bool $delete_donation  Confirm Donor related donations deletion.
                 *
                 * @since 2.20.0
                 */
                do_action( 'give_pre_delete_donor', $id, true, $delete_donation );
                $donor = Donor::find($id);
                if ($delete_donation) {
                    foreach( $donor->donations as $donation ) {
                        $donation->delete();
                    }
                }
                else {
                    give_update_payment_meta( $id, '_give_payment_donor_id', 0 );
                }
                $donor->delete();
                $successes[] = $id;
            } catch (Exception $e) {
                $errors[] = $id;
            }
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

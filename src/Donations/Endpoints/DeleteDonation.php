<?php

namespace Give\Donations\Endpoints;

use Exception;
use Give\Framework\Database\DB;
use WP_REST_Request;
use WP_REST_Response;

class DeleteDonation extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donations/delete';

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
                    ]
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @unreleased
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $ids = $this->splitString($request->get_param('ids'));
        $errors = $successes = [];

        foreach ($ids as $id) {

            DB::query('START TRANSACTION');

            try {
                // Delete donation
                DB::table('posts')
                    ->where('id', $id)
                    ->where('post_type', 'give_payment')
                    ->delete();

                // Delete donation meta
                DB::table('give_donationmeta')
                    ->where('donation_id', $id)
                    ->delete();

                DB::query('COMMIT');

                $successes[] = $id;
            } catch (Exception $e) {
                DB::query('ROLLBACK');
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
     * @unreleased
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

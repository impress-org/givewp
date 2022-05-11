<?php

namespace Give\Donations\Endpoints;

use Give\Donations\DataTransferObjects\DonationResponseData;
use Give\Donations\DonationsListTable;
use WP_REST_Request;
use WP_REST_Response;

class ListDonations extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donations';

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
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 1,
                        'minimum' => 1
                    ],
                    'perPage' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 30,
                        'minimum' => 1
                    ],
                    'form' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'start' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                    'end' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                    'donor' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $data = [];
        $donations = give()->donations->getDonationsForRequest($request);
        $donationsCount = give()->donations->getTotalDonationsCountForRequest($request);
        $totalPages = (int)ceil($donationsCount / $request->get_param('perPage'));

        foreach ($donations as $donation) {
            $data[] = DonationResponseData::fromObject($donation)->toArray();
        }

        /**
         * @var DonationsListTable $table
         */
        $table = give(DonationsListTable::class);
        $table->items($data);

        return new WP_REST_Response(
            [
                'table' => $table->getTableDefinitions(),
                'items' => $table->getItems(),
                'totalItems' => $donationsCount,
                'totalPages' => $totalPages
            ]
        );
    }
}

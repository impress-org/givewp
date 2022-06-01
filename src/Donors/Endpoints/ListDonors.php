<?php

namespace Give\Donors\Endpoints;

use Give\Donors\Controllers\DonorsRequestController;
use Give\Donors\DataTransferObjects\DonorResponseData;
use WP_REST_Request;
use WP_REST_Response;

class ListDonors extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donors';

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
                    'donations' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false
                    ],
                    'start' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                    'form' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'end' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     * @since 2.20.0
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $data = [];
        $controller = new DonorsRequestController($request);
        $donors = $controller->getDonors();
        $donorsCount = $controller->getTotalDonorsCount();
        $pageCount = (int)ceil($donorsCount / $request->get_param('perPage'));

        foreach ($donors as $donor) {
            $data[] = DonorResponseData::fromObject($donor)->toArray();
        }

        return new WP_REST_Response(
            [
                'items' => $data,
                'totalItems' => $donorsCount,
                'totalPages' => $pageCount
            ]
        );
    }
}

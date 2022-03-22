<?php

namespace Give\Donations\Endpoints;

use Give\Donations\DataTransferObjects\DonationResponseData;
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
                        'required' => false
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
    public function handleRequest(WP_REST_Request $request)
    {
        $data = [];
        $donations = give()->donations->getDonationsForRequest($request);
        $donationsCount = give()->donations->getTotalDonationsCountForRequest($request);
        $pageCount = (int)ceil($donationsCount / $request->get_param('perPage'));

        foreach ($donations as $donation) {
            $data[] = DonationResponseData::fromObject($donation)->toArray();
        }

        return new WP_REST_Response(
            [
                'items' => $data,
                'itemsCount' => $donationsCount,
                'pageCount' => $pageCount
            ]
        );
    }
}

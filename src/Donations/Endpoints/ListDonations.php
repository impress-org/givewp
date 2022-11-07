<?php

namespace Give\Donations\Endpoints;

use Give\Donations\Controllers\DonationsRequestController;
use Give\Donations\DataTransferObjects\DonationResponseData;
use Give\Donations\ListTable\DonationsListTable;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\ListTable\ListTable;
use Give\Framework\QueryBuilder\QueryBuilder;
use WP_REST_Request;
use WP_REST_Response;

class ListDonations extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donations';

    /**
     * @var WP_REST_Request
     */
    protected $request;

    /**
     * @var ListTable
     */
    protected $listTable;

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
                    'sortColumn' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'sortDirection' => [
                        'type' => 'string',
                        'required' => false,
                        'enum' => [
                            'asc',
                            'desc'
                        ],
                    ],
                    'locale' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => get_locale(),
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
        $this->request = $request;
        $this->listTable = new DonationsListTable($this->request->get_param('locale'));

        $donations = $this->getDonations();
        $donationsCount = $this->getTotalDonationsCount();
        $totalPages = (int)ceil($donationsCount / $this->request->get_param('perPage'));

        $this->listTable->items($donations);

        return new WP_REST_Response(
            [
                'items' => $this->listTable->getItems(),
                'totalItems' => $donationsCount,
                'totalPages' => $totalPages
            ]
        );
    }
}

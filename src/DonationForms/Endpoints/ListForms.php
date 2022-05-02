<?php

namespace Give\DonationForms\Endpoints;

use Give\DonationForms\Controllers\DonationFormsRequestController;
use Give\DonationForms\DataTransferObjects\DonationFormsResponseData;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.19.0
 */
class ListForms extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/forms';

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
                    'status' => [
                        'type' => 'string',
                        'required' => false,
                        'default' => 'any',
                        'enum' => [
                            'publish',
                            'future',
                            'draft',
                            'pending',
                            'trash',
                            'auto-draft',
                            'inherit',
                            'any'
                        ]
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false
                    ]
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $data = [];
        $controller = new DonationFormsRequestController($request);
        $forms = $controller->getForms();
        $totalForms = $controller->getTotalFormsCount();
        $totalPages = (int)ceil($totalForms / $request->get_param('perPage'));

        foreach ($forms as $form) {
            $data[] = DonationFormsResponseData::fromObject($form)->toArray();
        }

        return new WP_REST_Response(
            [
                'items' => $data,
                'totalItems' => $totalForms,
                'totalPages' => $totalPages,
                'trash' => defined('EMPTY_TRASH_DAYS') && EMPTY_TRASH_DAYS > 0,
            ]
        );
    }
}

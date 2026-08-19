<?php

namespace Give\DonationForms\Routes;

use Give\DonationForms\Controllers\DonationFormsRequestController;
use Give\DonationForms\Routes\Permissions\DonationFormPermissions;
use Give\DonationForms\ValueObjects\DonationFormsRoute as Route;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @since 4.2.0
 */
class DonationFormsEntityRoute
{
    protected DonationFormsRequestController $controller;

    /**
     * @since 4.2.0
     */
    public function __construct(DonationFormsRequestController $controller)
    {
        $this->controller = $controller;
    }


    /**
     * @since 4.2.0
     */
    public function __invoke()
    {
        $this->registerGetForm();
        $this->registerGetForms();
        $this->registerAssociateFormsWithCampaign();
    }

    /**
     * Get Form route
     *
     * @since 4.10.1 Changed permission callback to use validationForGetItem method
     * @since 4.2.0
     */
    public function registerGetForm()
    {
        register_rest_route(
            Route::NAMESPACE,
            Route::FORM,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->controller->getForm($request);
                    },
                    'permission_callback' => function (WP_REST_Request $request) {
                        return DonationFormPermissions::validationForGetItem($request);
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ]
        );
    }

    /**
     * Get Forms route
     *
     * @since 4.10.1 Changed permission callback to use validationForGetItems method
     * @since 4.2.0
     */
    public function registerGetForms()
    {
        register_rest_route(
            Route::NAMESPACE,
            Route::FORMS,
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->controller->getForms($request);
                    },
                    'permission_callback' => function (WP_REST_Request $request) {
                        return DonationFormPermissions::validationForGetItems($request);
                    },
                ],
                'args' => [
                    'status' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => ['publish', 'future', 'draft', 'pending', 'trash', 'upgraded', 'private', 'orphaned'],
                        ],
                        'default' => ['publish'],
                    ],
                    'ids' => [
                        'type' => 'array',
                        'default' => [],
                    ],
                    'page' => [
                        'type' => 'integer',
                        'default' => 1,
                        'minimum' => 1,
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'default' => 30,
                        'minimum' => 1,
                        'maximum' => 100,
                    ]
                ],
            ]
        );
    }


    /**
     * Associate donation forms with campaign
     *
     * @since 4.10.1 Changed permission callback to use validationForAssociateForms method
     * @since 4.2.0
     */
    public function registerAssociateFormsWithCampaign()
    {
        register_rest_route(
            Route::NAMESPACE,
            Route::ASSOCIATE_FORMS_WITH_CAMPAIGN,
            [
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => function (WP_REST_Request $request) {
                        return $this->controller->associateFormsWithCampaign($request);
                    },
                    'permission_callback' => function (WP_REST_Request $request) {
                        return DonationFormPermissions::validationForAssociateForms($request);
                    },
                ],
                'args' => [
                    'formIDs' => [
                        'type' => 'array',
                        'required' => true,
                    ],
                    'campaignId' => [
                        'type' => 'integer',
                        'required' => true,
                    ]
                ],
            ]
        );
    }
}

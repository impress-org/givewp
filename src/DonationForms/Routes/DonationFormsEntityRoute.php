<?php

namespace Give\DonationForms\Routes;

use Give\DonationForms\Controllers\DonationFormsRequestController;
use Give\DonationForms\ValueObjects\DonationFormsRoute as Route;
use WP_REST_Request;
use WP_REST_Server;

/**
 * @unreleased
 */
class DonationFormsEntityRoute
{
    protected DonationFormsRequestController $controller;

    /**
     * @unreleased
     */
    public function __construct(DonationFormsRequestController $controller)
    {
        $this->controller = $controller;
    }


    /**
     * @unreleased
     */
    public function __invoke()
    {
        $this->registerGetForm();
        $this->registerGetForms();
    }

    /**
     * Get Form route
     *
     * @unreleased
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
                    'permission_callback' => function () {
                        return '__return_true';
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
     * @unreleased
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
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'status' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => ['publish', 'future', 'draft', 'pending', 'trash', 'upgraded', 'private', 'orphaned'],
                        ],
                        'default' => 'publish',
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
}

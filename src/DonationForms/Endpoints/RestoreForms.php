<?php

namespace Give\DonationForms\Endpoints;

/**
 * @unreleased
 */


class RestoreForms extends MutateForms
{
    protected $endpoint = 'admin/forms/restore';

    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'int',
                        'required' => false,
                        'validate_callback' => [$this, 'validateInt'],
                    ],
                    'perPage' => [
                        'type' => 'int',
                        'required' => false,
                        'validate_callback' => [$this, 'validateInt'],
                    ],
                    'status' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateStatus']
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => 'false',
                        'validate_callback' => [$this, 'validateSearch'],
                        'sanitize_callback' => [$this, 'sanitizeSearch']
                    ],
                    'ids' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validateIDString'],
                    ]
                ],
            ],
        );
    }

    protected function mutate($id)
    {
        return wp_untrash_post($id);
    }
}

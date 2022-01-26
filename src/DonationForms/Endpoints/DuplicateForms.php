<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

require_once( GIVE_PLUGIN_DIR . '/includes/admin/forms/class-give-form-duplicator.php');

/**
 * @unreleased
 */


class DuplicateForms extends MutateForms
{
    protected $endpoint = 'admin/forms/duplicate';

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
                    'ids' => [
                        'type' => 'string',
                        'required' => true,
                        'validate_callback' => [$this, 'validateIDString'],
                    ]
                ],
            ],
        );
    }

    /**
     * Check user permissions
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('create_posts')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You dont have the right permissions to duplicate forms.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    protected function mutate($id)
    {
        return \Give_Form_Duplicator::handler($id);
    }
}

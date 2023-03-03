<?php

namespace Give\DonationForms\Endpoints;

use Give\API\RestRoute;
use WP_Error;

/**
 * @since 2.19.0
 */
abstract class Endpoint implements RestRoute
{

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @param  string  $value
     *
     * @return bool
     */
    public function validateInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * Check user permissions
     *
     * @unreleased Check for different capabilities for different HTTP methods
     * @since      2.19.0
     *
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'UPDATE', 'DELETE'], true)) {
            if ( ! current_user_can('edit_give_forms')) {
                return new WP_Error(
                    'rest_forbidden',
                    esc_html__('You don\'t have permission to edit Donation Forms', 'give'),
                    ['status' => $this->authorizationStatusCode()]
                );
            }
        } elseif ( ! current_user_can('edit_posts')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You dont have the right permissions to view Donation Forms', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    // Sets up the proper HTTP status code for authorization.
    public function authorizationStatusCode()
    {
        if (is_user_logged_in()) {
            return 403;
        }

        return 401;
    }
}

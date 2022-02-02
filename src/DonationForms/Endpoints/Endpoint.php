<?php

namespace Give\DonationForms\Endpoints;

use Give\API\RestRoute;
use WP_Error;

/**
 * @unreleased
 */

abstract class Endpoint implements RestRoute
{

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @param string $value
     *
     * @return bool
     */
    public function validateInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * @param string $param
     *
     * @return bool
     */
    public function validateStatus($param)
    {
        return in_array($param, [
            'publish',
            'future',
            'draft',
            'pending',
            'trash',
            'auto-draft',
            'inherit',
            'any'
        ], true);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function sanitizeSearch($value)
    {
        return filter_var($value, FILTER_SANITIZE_STRING);
    }

    /**
     * Check user permissions
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('edit_posts')) {
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

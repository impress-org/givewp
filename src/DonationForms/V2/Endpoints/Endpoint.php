<?php

namespace Give\DonationForms\V2\Endpoints;

use Give\API\RestRoute;
use Give\Framework\Permissions\Facades\UserPermissions;
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
     * @since 3.14.0
     * @param  string  $id
     * @return bool
     */
    public function validatePostType(string $id)
    {
        return get_post_type($id) === 'give_forms';
    }

    /**
     * Check user permissions
     * @since 4.14.0 update permission capability to use facade
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if (UserPermissions::donationForms()->canView()) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            __("You don't have permission to view forms", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
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

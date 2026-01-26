<?php

namespace Give\API\Endpoints\Migrations;

use Give\API\RestRoute;
use Give\Framework\Permissions\Facades\UserPermissions;
use WP_Error;

/**
 * Class Endpoint
 * @package Give\API\Endpoints\Migrations
 *
 * @since 4.14.0 update permission capability to use facade
 * @since 2.10.0
 */
abstract class Endpoint implements RestRoute
{

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * Check user permissions
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if ( ! UserPermissions::settings()->canManage()) {
            return new WP_Error(
                'rest_forbidden',
                __('You don\'t have the right permissions to view Migrations', 'give'),
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

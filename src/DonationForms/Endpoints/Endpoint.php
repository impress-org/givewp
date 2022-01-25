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

    // Sets up the proper HTTP status code for authorization.
    public function authorizationStatusCode()
    {
        if (is_user_logged_in()) {
            return 403;
        }

        return 401;
    }
}

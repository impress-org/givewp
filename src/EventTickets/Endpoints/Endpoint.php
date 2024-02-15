<?php

namespace Give\EventTickets\Endpoints;

use Give\API\RestRoute;
use WP_Error;
use WP_REST_Request;

abstract class Endpoint implements RestRoute
{
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @unreleased
     */
    public function validateInt(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * @unreleased
     */
    public function validateDate(string $param, WP_REST_Request $request, string $key): bool
    {
        // Check that date is valid, and formatted YYYY-MM-DD
        [$year, $month, $day] = explode('-', $param);
        $valid = checkdate($month, $day, $year);

        // If checking end date, check that it is after start date
        if ('end' === $key) {
            $start = date_create($request->get_param('start'));
            $end = date_create($request->get_param('end'));
            $valid = $start <= $end ? $valid : false;
        }

        return $valid;
    }

    /**
     * Check user permissions
     *
     * @unreleased
     *
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if (!current_user_can('edit_posts')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You dont have the right permissions to view Donors', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * Sets up the proper HTTP status code for authorization.
     *
     * @unreleased
     */
    public function authorizationStatusCode(): int
    {
        if (is_user_logged_in()) {
            return 403;
        }

        return 401;
    }
}

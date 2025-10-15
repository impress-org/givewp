<?php

namespace Give\Subscriptions\Endpoints;

use Give\API\RestRoute;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use WP_Error;
use WP_REST_Request;

abstract class Endpoint implements RestRoute
{
    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @param  string  $value
     * @since 2.20.0
     *
     * @return bool
     */
    public function validateInt($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * @param string $param
     * @param WP_REST_Request $request
     * @param string $key
     * @since 2.20.0
     *
     * @return bool
     */
    public function validateDate($param, $request, $key)
    {
        if (empty($param)) {
            return true;
        }

        if ($this->isValidPeriod($param)) {
            return true;
        }

        // Check that date is valid, and formatted YYYY-MM-DD
        list($year, $month, $day) = explode('-', $param);
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
     * Validate status parameter values against SubscriptionStatus constants.
     *
     * @unreleased
     *
     * @param string $param The status parameter value (comma-separated list)
     * @param WP_REST_Request $request The REST request object
     * @param string $key The parameter key
     *
     * @return bool|WP_Error True if valid, WP_Error if invalid
     */
    public function validateStatus($param, $request, $key)
    {
        if (empty($param)) {
            return true;
        }

        $statuses = array_map('trim', explode(',', $param));
        $validStatuses = array_values(SubscriptionStatus::toArray());

        foreach ($statuses as $status) {
            if (!in_array($status, $validStatuses, true)) {
                return new WP_Error(
                    'rest_invalid_param',
                    sprintf(
                        /* translators: 1: parameter name, 2: invalid status value, 3: comma-separated list of valid statuses */
                        __('%1$s has an invalid status value: %2$s. Valid values are: %3$s', 'give'),
                        $key,
                        $status,
                        implode(', ', $validStatuses)
                    ),
                    ['status' => 400]
                );
            }
        }

        return true;
    }

    /**
     * Check user permissions
     * @since 4.3.1 updates permissions
     * @since 2.20.0
     *
     * @return bool|WP_Error
     */
    public function permissionsCheck()
    {
        if (current_user_can('manage_options') || current_user_can('edit_give_payments')) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__("You don't have permission to view Subscriptions", 'give'),
            ['status' => is_user_logged_in() ? 403 : 401]
        );
    }

    /**
     * Sets up the proper HTTP status code for authorization.
     * @since 2.20.0
     *
     * @return int
     */
    public function authorizationStatusCode()
    {
        if (is_user_logged_in()) {
            return 403;
        }

        return 401;
    }

    /**
     * @unreleased
     */
    protected function isValidPeriod(?string $period): bool
    {
        return !empty($period) && in_array($period, ['90d', '30d', '7d']);
    }
}

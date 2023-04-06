<?php

namespace Give\DonorDashboards\Tabs\Contracts;

use Exception;
use Give\API\RestRoute;
use Give\DonorDashboards\Helpers as DonorDashboardHelpers;
use Give\Log\Log;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.10.0
 */
abstract class Route implements RestRoute
{
    /**
     * Returns string to complete Route endpoint
     * Full route will be donor-profile/{endpoint}
     *
     * @since 2.10.0
     * @return string
     *
     */
    abstract public function endpoint();

    /**
     * Returns arguments for Route
     * For more information, see: https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/#arguments
     *
     * @since 2.10.0
     * @return array
     *
     */
    abstract public function args();

    /**
     * Handles route request, and returns response
     *
     * @since 2.10.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response|array
     *
     */
    abstract public function handleRequest(WP_REST_Request $request);

    /** @var string */
    protected $root = 'donor-dashboard/';

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            "{$this->root}{$this->endpoint()}",
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequestWithDonorIdCheck'],
                    'permission_callback' => function () {
                        return DonorDashboardHelpers::isDonorLoggedIn();
                    },
                ],
                'args' => $this->args(),
            ]
        );
    }

    /**
     * @since 2.26.0 add try/catch to handleRequest
     */
    public function handleRequestWithDonorIdCheck(WP_REST_Request $request)
    {
        // Check that the provided donor ID is valid
        if (!Give()->donors->get_donor_by('id', give()->donorDashboard->getId())) {
            Log::error(
                esc_html__('An error occurred while validating donor ID on request.', 'give'),
                [
                    'source' => 'Donor Dashboard',
                    'Donor ID' => give()->donorDashboard->getId(),
                    'Current User ID' => get_current_user_id(),
                    'Email Access Token' => give()->email_access->token_email,
                    'Session Email' => give()->session->get('give_email'),
                    'Session Expiration' => give()->session->get_session_expiration(),
                    'Error' => __('Donor ID could not be validated for request.', 'give'),
                ]
            );

            return new WP_REST_Response(
                [
                    'status' => 400,
                    'response' => 'invalid_donor_id',
                    'body_response' => [
                        'message' => html_entity_decode(
                            esc_html__(
                                'An error occurred while retrieving your donation records. Contact a site administrator and have them search the logs at Donations > Tools > Logs for a more specific cause of the problem.',
                                'give'
                            )
                        ),
                    ],
                ]
            );
        }

        try {
            $response = $this->handleRequest($request);

            return rest_ensure_response($response ?? []);
        } catch (Exception $exception) {
            $error = new WP_Error('error', $exception->getMessage());

            return rest_ensure_response($error);
        }
    }
}

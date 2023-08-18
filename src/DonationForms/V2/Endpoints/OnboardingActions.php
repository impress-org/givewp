<?php

namespace Give\DonationForms\V2\Endpoints;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
 */
class OnboardingActions extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/onboarding/options';

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => ['POST'],
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'option' => [
                        'type' => 'string',
                        'required' => true,
                        'enum' => [
                            'show_migration_onboarding',
                            'show_onboarding_banner',
                            'migration_onboarding_completed',
                            'transfer_onboarding_completed',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function permissionsCheck()
    {
        if ( ! current_user_can('edit_give_forms')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You don\'t have permission to edit Donation Forms', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        give_update_option($request->get_param('option'), 'true');

        return new WP_REST_Response("Option {$request->get_param('option')} updated");
    }
}

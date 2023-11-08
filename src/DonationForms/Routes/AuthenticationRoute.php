<?php

namespace Give\DonationForms\Routes;

use Give\DonationForms\DataTransferObjects\AuthenticationData;
use Give\DonationForms\DataTransferObjects\DonateRouteData;
use Give\DonationForms\DataTransferObjects\UserData;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use WP_User;

/**
 * @since 3.0.0
 */
class AuthenticationRoute
{
    use HandleHttpResponses;

    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function __invoke(array $request)
    {
        $routeData = DonateRouteData::fromRequest(give_clean($_GET));

        $routeData->validateSignature();

        $user = $this->authenticate(AuthenticationData::fromRequest($request));

        wp_send_json_success(UserData::fromUser($user));

        exit;
    }

    /**
     * @since 3.0.0
     */
    protected function authenticate(AuthenticationData $auth): WP_User
    {
        $userOrError = wp_signon([
            'user_login' => $auth->login,
            'user_password' => $auth->password,
        ]);

        if (is_wp_error($userOrError)) {
            wp_send_json_error([
                'type' => 'authentication_error',
                'message' => __('The login/password does not match or is incorrect.', 'give'),
            ], 401);
            exit;
        }

        return $userOrError;
    }
}

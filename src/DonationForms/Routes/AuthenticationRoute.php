<?php

namespace Give\DonationForms\Routes;

use Give\DonationForms\DataTransferObjects\AuthenticationData;
use Give\DonationForms\DataTransferObjects\DonateRouteData;
use Give\DonationForms\DataTransferObjects\UserData;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use WP_User;

/**
 * @unreleased
 */
class AuthenticationRoute
{
    use HandleHttpResponses;

    /**
     * @unreleased
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
     * @unreleased
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
                'message' => __('The login/password does not match or is incorrect.', 'givewp'),
            ], 401);
            exit;
        }

        return $userOrError;
    }
}

<?php

namespace Give\DonationForms\Routes;

use Give\DonationForms\Actions\AuthenticateFormRequestWithToken;
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
     * @since TBD Return an auth token so embedded forms can authenticate without cookies.
     * @since 3.0.0
     *
     * @return void
     */
    public function __invoke(array $request)
    {
        $routeData = DonateRouteData::fromRequest(give_clean($_GET));

        $routeData->validateSignature();

        $user = $this->authenticate(AuthenticationData::fromRequest($request));

        wp_send_json_success(
            get_object_vars(UserData::fromUser($user)) + [
                AuthenticateFormRequestWithToken::TOKEN_KEY => $this->generateAuthToken($user),
            ]
        );

        exit;
    }

    /**
     * The token is the logged_in auth cookie value: signed by core, session
     * backed, and revoked with the session. It carries the login where the
     * cookie cannot, which is inside a cross-site iframe.
     *
     * @since TBD
     */
    protected function generateAuthToken(WP_User $user): string
    {
        return wp_generate_auth_cookie($user->ID, time() + HOUR_IN_SECONDS, 'logged_in');
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

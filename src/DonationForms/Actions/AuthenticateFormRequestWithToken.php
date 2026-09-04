<?php

namespace Give\DonationForms\Actions;

/**
 * Signs the donor in for a donate or validate request from a signed auth
 * token instead of the login cookie.
 *
 * A donation form embedded on another website cannot rely on cookies: the
 * browser drops WordPress auth cookies set from a cross-site iframe response.
 * The authentication route therefore also returns the auth cookie value, which
 * WordPress already signs, expires, and backs with a session token. The form
 * sends it back with the donation and the route validates it the same way core
 * validates the cookie.
 *
 * This runs from the two form routes rather than on determine_current_user so
 * it works even when another plugin resolves the current user before this
 * plugin has loaded, and so the token is never accepted anywhere else.
 *
 * @since TBD
 */
class AuthenticateFormRequestWithToken
{
    const TOKEN_KEY = 'authToken';

    /**
     * @since TBD
     */
    public function __invoke(array $request): void
    {
        if (is_user_logged_in()) {
            return;
        }

        $token = $request[self::TOKEN_KEY] ?? '';

        if (!is_string($token) || $token === '') {
            return;
        }

        $userId = wp_validate_auth_cookie($token, 'logged_in');

        if ($userId) {
            wp_set_current_user($userId);
        }
    }
}

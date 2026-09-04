<?php

namespace Give\DonationForms\Actions;

/**
 * Signs the donor in for a donate or validate request from a signed auth
 * token instead of the login cookie.
 *
 * A donation form embedded on another website cannot rely on cookies: the
 * browser drops WordPress auth cookies set from a cross-site iframe response.
 * The authentication route therefore also returns a token built with the same
 * core functions as the auth cookie, which WordPress signs, expires, and backs
 * with a session token, under a plugin-specific scheme. The form sends it back
 * with the donation and the route validates it the same way core validates
 * the cookie.
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
     * A plugin-specific salt scheme. WordPress derives the salt from the scheme
     * name, so the token verifies only here and is never a valid login cookie
     * if it leaks.
     */
    const SCHEME = 'givewp_embedded_form';

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

        /*
         * Core extends the expiry by an hour for POST requests, which is meant
         * for a form that sat open in a browser. This token is a short-lived
         * credential, so its own expiry is the limit.
         */
        $parts = wp_parse_auth_cookie($token, self::SCHEME);

        if (!$parts || (int)$parts['expiration'] < time()) {
            return;
        }

        $userId = wp_validate_auth_cookie($token, self::SCHEME);

        if ($userId) {
            wp_set_current_user($userId);
        }
    }
}

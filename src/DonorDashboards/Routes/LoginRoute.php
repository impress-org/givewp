<?php

namespace Give\DonorDashboards\Routes;

use Give\API\RestRoute;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.10.0
 */
class LoginRoute implements RestRoute
{

    /** @var string */
    protected $endpoint = 'donor-dashboard/login';

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
                    'methods' => 'POST',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'login' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'password' => [
                        'type' => 'string',
                        'required' => true,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );
    }

    /**
     * Handles login request.
     *
     * Authentication is delegated to wp_signon() so that the request runs
     * through WordPress's `authenticate` filter chain. This lets brute-force
     * protection plugins (Wordfence, Login LockDown, Jetpack Protect, Solid
     * Security, etc.) intercept and block attempts, and ensures the core
     * `wp_login_failed` action fires on failure so plugins that hook it (Limit
     * Login Attempts, etc.) can count the attempt. Core authentication failures
     * (wrong password, unknown username/email) return a single generic response
     * so the endpoint cannot be used to enumerate valid accounts. A lockout from
     * a protection plugin is surfaced as a 429 with that plugin's message so a
     * rate-limited user understands why the login was refused.
     *
     * @since TBD Route through wp_signon() and return a generic failure
     *            response to prevent brute-force-protection bypass (CWE-307)
     *            and user enumeration (CWE-204); surface lockout messages as 429.
     * @since 2.10.0
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $login = $request->get_param('login');
        $password = $request->get_param('password');

        // wp_signon() runs the `authenticate` filter chain (so lockout plugins
        // can intercept and block), fires `wp_login_failed` on failure, and sets
        // the auth cookie on success.
        $user = wp_signon(
            [
                'user_login' => $login,
                'user_password' => $password,
                'remember' => false,
            ]
        );

        if (is_wp_error($user)) {
            // Core authentication errors (wrong password, unknown username/email)
            // are collapsed into a single generic message so the endpoint cannot
            // be used to enumerate accounts. Any other error code originates from
            // a brute-force protection plugin (lockout) — surface its message so
            // a locked-out user understands why the login was refused.
            $coreAuthErrorCodes = [
                'incorrect_password',
                'invalid_username',
                'invalid_email',
                'empty_username',
                'empty_password',
            ];

            $isLockout = ! in_array($user->get_error_code(), $coreAuthErrorCodes, true);

            if ($isLockout) {
                $lockoutMessage = wp_strip_all_tags($user->get_error_message());

                return new WP_REST_Response(
                    [
                        'status' => 429,
                        'response' => 'too_many_attempts',
                        'body_response' => [
                            'message' => '' !== $lockoutMessage
                                ? $lockoutMessage
                                : __('Too many failed login attempts. Please try again later.', 'give'),
                        ],
                    ],
                    429
                );
            }

            return new WP_REST_Response(
                [
                    'status' => 401,
                    'response' => 'login_failed',
                    'body_response' => [
                        'message' => __('The provided credentials are invalid.', 'give'),
                    ],
                ],
                401
            );
        }

        // wp_signon() sets the auth cookie and fires `wp_login`, but does not set
        // the current user for the active request. Set it so the rest of this
        // request is authenticated, then fire only GiveWP's own hook so existing
        // integrations keep working, without duplicating the cookie or `wp_login`.
        wp_set_current_user($user->ID, $user->user_login);
        do_action('give_log_user_in', $user->ID, $user->user_login, $password);

        return new WP_REST_Response(
            [
                'status' => 200,
                'response' => 'login_successful',
                'body_response' => [
                    'login' => $user->user_login,
                    'id' => $user->ID,
                ],
            ]
        );
    }
}

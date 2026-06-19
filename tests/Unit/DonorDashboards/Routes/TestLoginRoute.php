<?php

namespace Give\Tests\Unit\DonorDashboards\Routes;

use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;

/**
 * Security tests for the Donor Dashboard login REST endpoint.
 *
 * These cover the brute-force-bypass (CWE-307) and user-enumeration (CWE-204)
 * hardening of /give-api/v2/donor-dashboard/login.
 *
 * @since 4.15.5
 *
 * @coversDefaultClass \Give\DonorDashboards\Routes\LoginRoute
 */
class TestLoginRoute extends RestApiTestCase
{
    use RefreshDatabase;

    /** @var string */
    const ROUTE = '/give-api/v2/donor-dashboard/login';

    /** @var string */
    const KNOWN_USERNAME = 'givewp_donor';

    /** @var string */
    const KNOWN_EMAIL = 'givewp_donor@example.test';

    /** @var string */
    const KNOWN_PASSWORD = 'correct horse battery staple';

    /**
     * @since 4.15.5
     *
     * @return int The created user's ID.
     */
    private function createKnownUser(): int
    {
        return wp_insert_user([
            'user_login' => self::KNOWN_USERNAME,
            'user_email' => self::KNOWN_EMAIL,
            'user_pass' => self::KNOWN_PASSWORD,
            'role' => 'subscriber',
        ]);
    }

    /**
     * @since 4.15.5
     */
    private function login(string $login, string $password)
    {
        $request = new WP_REST_Request('POST', self::ROUTE);
        $request->set_param('login', $login);
        $request->set_param('password', $password);

        return $this->dispatchRequest($request)->get_data();
    }

    /**
     * Valid credentials must still authenticate successfully. This guards the
     * fix against breaking the happy path.
     *
     * @since 4.15.5
     */
    public function testValidCredentialsLogInSuccessfully()
    {
        $this->createKnownUser();

        $data = $this->login(self::KNOWN_USERNAME, self::KNOWN_PASSWORD);

        $this->assertSame(200, $data['status']);
        $this->assertSame('login_successful', $data['response']);
        $this->assertTrue(is_user_logged_in());
    }

    /**
     * A failed login attempt must fire the `wp_login_failed` action so that
     * action-based lockout plugins (Limit Login Attempts, etc.) can count it.
     *
     * Vulnerable behavior: the endpoint calls wp_check_password() directly and
     * never fires wp_login_failed, so failures are invisible to those plugins.
     *
     * @since 4.15.5
     */
    public function testFailedLoginFiresWpLoginFailedAction()
    {
        $this->createKnownUser();

        $fired = false;
        add_action('wp_login_failed', function () use (&$fired) {
            $fired = true;
        });

        $this->login(self::KNOWN_USERNAME, 'wrong-password');

        $this->assertTrue(
            $fired,
            'wp_login_failed should fire on a failed Donor Dashboard login so lockout plugins can count it.'
        );
    }

    /**
     * A failed login attempt must run through the `authenticate` filter chain
     * so that filter-based lockout plugins (Wordfence, Login LockDown, Jetpack
     * Protect, Solid Security) can intercept and block it.
     *
     * Vulnerable behavior: wp_check_password() never invokes `authenticate`.
     *
     * @since 4.15.5
     */
    public function testFailedLoginRunsThroughAuthenticateFilter()
    {
        $this->createKnownUser();

        $ran = false;
        add_filter('authenticate', function ($user) use (&$ran) {
            $ran = true;

            return $user;
        }, 5);

        $this->login(self::KNOWN_USERNAME, 'wrong-password');

        $this->assertTrue(
            $ran,
            'The authenticate filter must run so security plugins can intercept the attempt.'
        );
    }

    /**
     * A filter-based lockout plugin must be able to block the login by short
     * circuiting the `authenticate` filter with a WP_Error.
     *
     * @since 4.15.5
     */
    public function testAuthenticateFilterCanBlockLogin()
    {
        $this->createKnownUser();

        // Simulate a lockout plugin refusing the attempt.
        add_filter('authenticate', function () {
            return new \WP_Error('too_many_retries', 'Locked out.');
        }, 30);

        $data = $this->login(self::KNOWN_USERNAME, self::KNOWN_PASSWORD);

        $this->assertNotSame(
            'login_successful',
            $data['response'],
            'A lockout plugin returning WP_Error from authenticate must prevent login, even with correct credentials.'
        );
        $this->assertFalse(is_user_logged_in());
    }

    /**
     * When the failure comes from a lockout plugin, the endpoint surfaces that
     * plugin's message and a 429 status so the user understands they are rate
     * limited.
     *
     * The error code used here is an arbitrary, made-up code: the endpoint
     * detects a lockout by the code NOT being one of WordPress's core
     * authentication error codes, so this works for any protection plugin
     * regardless of whether one is actually installed.
     *
     * @since 4.15.5
     */
    public function testLockoutSurfacesPluginMessage()
    {
        $this->createKnownUser();

        add_filter('authenticate', function () {
            return new \WP_Error('lockout_plugin_block', 'Too many failed login attempts. Please try again later.');
        }, 30);

        $data = $this->login(self::KNOWN_USERNAME, 'wrong-password');

        $this->assertSame(429, $data['status']);
        $this->assertSame('too_many_attempts', $data['response']);
        $this->assertStringContainsString(
            'Too many failed login attempts',
            $data['body_response']['message'],
            'A lockout failure should surface the protection plugin\'s message.'
        );
    }

    /**
     * Core authentication failures (wrong password / unknown account) must NOT
     * surface the underlying WP_Error message, since those messages differ per
     * case and would reintroduce user enumeration (CWE-204).
     *
     * @since 4.15.5
     */
    public function testCoreAuthErrorsStayGeneric()
    {
        $this->createKnownUser();

        $data = $this->login(self::KNOWN_USERNAME, 'wrong-password');

        $this->assertSame(401, $data['status']);
        $this->assertSame('login_failed', $data['response']);
        $this->assertSame(
            __('The provided credentials are invalid.', 'give'),
            $data['body_response']['message'],
            'A wrong-password failure must use the generic message, not the core WP_Error text.'
        );
    }

    /**
     * The response for a non-existent user and the response for a wrong password
     * must be indistinguishable, otherwise an attacker can enumerate valid
     * usernames/emails (CWE-204).
     *
     * Vulnerable behavior: distinct 'unidentified_login' vs 'incorrect_password'
     * responses reveal whether an account exists.
     *
     * @since 4.15.5
     */
    public function testFailureResponsesDoNotRevealAccountExistence()
    {
        $this->createKnownUser();

        $existingUserWrongPassword = $this->login(self::KNOWN_USERNAME, 'wrong-password');
        $nonExistentUser = $this->login('no-such-user-here', 'wrong-password');

        $this->assertSame(
            $existingUserWrongPassword['response'],
            $nonExistentUser['response'],
            'The response code must be identical whether or not the account exists.'
        );
        $this->assertSame(
            $existingUserWrongPassword['body_response']['message'],
            $nonExistentUser['body_response']['message'],
            'The error message must be identical whether or not the account exists.'
        );
    }

    /**
     * The failure message must not echo back the submitted login value, which
     * is both an enumeration aid and a reflected-input smell.
     *
     * @since 4.15.5
     */
    public function testFailureMessageDoesNotEchoSubmittedLogin()
    {
        $this->createKnownUser();

        $data = $this->login('some-unique-probe-value', 'wrong-password');

        $this->assertStringNotContainsString(
            'some-unique-probe-value',
            $data['body_response']['message'],
            'The failure message must not reflect the submitted login value.'
        );
    }
}

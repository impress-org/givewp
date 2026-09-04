<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\AuthenticateFormRequestWithToken;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_Session_Tokens;

/**
 * @since TBD
 */
class AuthenticateFormRequestWithTokenTest extends TestCase
{
    use RefreshDatabase;

    public function tearDown(): void
    {
        wp_set_current_user(0);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testValidTokenSignsTheDonorIn(): void
    {
        $userId = $this->factory()->user->create();

        (new AuthenticateFormRequestWithToken())($this->requestWith($this->tokenFor($userId)));

        $this->assertSame($userId, get_current_user_id());
    }

    /**
     * @since TBD
     */
    public function testLoggedInUserIsKept(): void
    {
        $userId = $this->factory()->user->create();
        $otherUserId = $this->factory()->user->create();
        wp_set_current_user($userId);

        (new AuthenticateFormRequestWithToken())($this->requestWith($this->tokenFor($otherUserId)));

        $this->assertSame($userId, get_current_user_id());
    }

    /**
     * @since TBD
     */
    public function testMissingTokenLeavesTheRequestAnonymous(): void
    {
        (new AuthenticateFormRequestWithToken())(['formId' => 1]);
        $this->assertSame(0, get_current_user_id());

        (new AuthenticateFormRequestWithToken())($this->requestWith(''));
        $this->assertSame(0, get_current_user_id());

        (new AuthenticateFormRequestWithToken())($this->requestWith(['array']));
        $this->assertSame(0, get_current_user_id());
    }

    /**
     * @since TBD
     */
    public function testExpiredTokenIsRejected(): void
    {
        $userId = $this->factory()->user->create();

        (new AuthenticateFormRequestWithToken())($this->requestWith($this->tokenFor($userId, -HOUR_IN_SECONDS)));

        $this->assertSame(0, get_current_user_id());
    }

    /**
     * @since TBD
     */
    public function testTamperedTokenIsRejected(): void
    {
        $userId = $this->factory()->user->create();

        (new AuthenticateFormRequestWithToken())($this->requestWith(substr_replace($this->tokenFor($userId), 'x', -1)));
        $this->assertSame(0, get_current_user_id());

        (new AuthenticateFormRequestWithToken())($this->requestWith('not-a-token'));
        $this->assertSame(0, get_current_user_id());
    }

    /**
     * @since TBD
     */
    public function testTokenIsNotAValidLoginCookie(): void
    {
        $userId = $this->factory()->user->create();
        $token = $this->tokenFor($userId);

        $this->assertFalse(wp_validate_auth_cookie($token, 'logged_in'));
        $this->assertFalse(wp_validate_auth_cookie($token, 'auth'));
        $this->assertFalse(wp_validate_auth_cookie($token, 'secure_auth'));
    }

    /**
     * @since TBD
     */
    public function testLoginCookieIsNotAValidToken(): void
    {
        $userId = $this->factory()->user->create();
        $cookie = wp_generate_auth_cookie($userId, time() + HOUR_IN_SECONDS, 'logged_in');

        (new AuthenticateFormRequestWithToken())($this->requestWith($cookie));

        $this->assertSame(0, get_current_user_id());
    }

    /**
     * @since TBD
     */
    public function testRevokedSessionIsRejected(): void
    {
        $userId = $this->factory()->user->create();
        $token = $this->tokenFor($userId);
        WP_Session_Tokens::get_instance($userId)->destroy_all();

        (new AuthenticateFormRequestWithToken())($this->requestWith($token));

        $this->assertSame(0, get_current_user_id());
    }

    private function tokenFor(int $userId, int $ttl = HOUR_IN_SECONDS): string
    {
        return wp_generate_auth_cookie($userId, time() + $ttl, AuthenticateFormRequestWithToken::SCHEME);
    }

    /**
     * @param mixed $token
     */
    private function requestWith($token): array
    {
        return ['formId' => 1, AuthenticateFormRequestWithToken::TOKEN_KEY => $token];
    }
}

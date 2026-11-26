<?php

namespace Give\Tests\Unit\DonorDashboards\Tabs\EditProfileTab;

use Give\Donors\Models\Donor;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;

/**
 * @since 4.16.6
 *
 * @coversDefaultClass \Give\DonorDashboards\Tabs\EditProfileTab\PasswordRoute
 */
class TestPasswordRoute extends RestApiTestCase
{
    use RefreshDatabase;

    /** @var string */
    const ROUTE = '/give-api/v2/donor-dashboard/password';

    /** @var int */
    private $userId;

    /** @var Donor */
    private $donor;

    /**
     * @since 4.16.6
     */
    public function setUp(): void
    {
        parent::setUp();

        unset($_GET['give_nl'], $_COOKIE['give_nl']);

        $uniqueId = uniqid('', true);

        $this->userId = wp_insert_user([
            'user_login' => "givewp_test_donor_{$uniqueId}",
            'user_email' => "givewp_test_donor_{$uniqueId}@example.test",
            'user_pass' => 'original-password',
            'role' => 'subscriber',
        ]);

        $this->donor = Donor::factory()->create([
            'userId' => $this->userId,
        ]);

        wp_set_current_user($this->userId);
    }

    /**
     * @since 4.16.6
     */
    public function tearDown(): void
    {
        unset($_GET['give_nl'], $_COOKIE['give_nl']);

        if ($this->userId > 0 && get_user_by('id', $this->userId)) {
            wp_delete_user($this->userId);
        }

        parent::tearDown();
    }

    /**
     * @since 4.16.6
     */
    public function testEmptyPasswordReturnsError()
    {
        $request = new WP_REST_Request('POST', self::ROUTE);
        $request->set_param('newPassword', '');
        $request->set_param('donor_id', $this->donor->id);

        $data = $this->dispatchRequest($request)->get_data();

        $this->assertSame(400, $data['status']);
        $this->assertSame('invalid_password', $data['response']);
    }

    /**
     * @since 4.16.6
     */
    public function testWhitespacePasswordReturnsError()
    {
        $request = new WP_REST_Request('POST', self::ROUTE);
        $request->set_param('newPassword', '   ');
        $request->set_param('donor_id', $this->donor->id);

        $data = $this->dispatchRequest($request)->get_data();

        $this->assertSame(400, $data['status']);
        $this->assertSame('invalid_password', $data['response']);
    }

    /**
     * @since 4.16.6
     */
    public function testValidPasswordReturnsSuccess()
    {
        $request = new WP_REST_Request('POST', self::ROUTE);
        $request->set_param('newPassword', 'new-valid-password');
        $request->set_param('donor_id', $this->donor->id);

        $data = $this->dispatchRequest($request)->get_data();

        $this->assertTrue($data['success']);
    }

    /**
     * @since 4.16.6
     */
    public function testValidPasswordUpdatesUserPassword()
    {
        $request = new WP_REST_Request('POST', self::ROUTE);
        $request->set_param('newPassword', 'updated-password');
        $request->set_param('donor_id', $this->donor->id);

        $this->dispatchRequest($request);

        $user = get_user_by('id', $this->userId);
        $this->assertNotFalse(
            wp_check_password('updated-password', $user->user_pass, $this->userId),
            'User password should be updated to the new value.'
        );
    }

    /**
     * @since 4.20.0
     */
    public function testPasswordUpdateRefusedWithoutLinkedUserSession()
    {
        wp_set_current_user(0);

        $token = wp_generate_password(32, false);
        give()->email_access->set_verify_key($this->donor->id, $this->donor->email, $token);
        $_GET['give_nl'] = $token;

        $request = new WP_REST_Request('POST', self::ROUTE);
        $request->set_param('newPassword', 'should-not-apply');
        $request->set_param('donor_id', $this->donor->id);

        $data = $this->dispatchRequest($request)->get_data();

        $this->assertSame(403, $data['status']);
        $this->assertSame('account_login_required', $data['response']);

        $user = get_user_by('id', $this->userId);
        $this->assertFalse(
            wp_check_password('should-not-apply', $user->user_pass, $this->userId),
            'User password should remain unchanged without the linked user account session.'
        );
    }
}

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

    /** @var int */
    private $nonDonorUserId = 0;

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

        if ($this->nonDonorUserId > 0 && get_user_by('id', $this->nonDonorUserId)) {
            wp_delete_user($this->nonDonorUserId);
        }

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
     * An email-access token must not authorize a password change.
     *
     * @since TBD
     */
    public function testEmailAccessTokenCannotUpdatePassword()
    {
        wp_set_current_user(0);
        give_update_option('email_access', 'enabled');
        Give()->email_access->init();
        Give()->email_access->set_verify_key($this->donor->id, $this->donor->email, 'email-access-token');
        $_GET['give_nl'] = 'email-access-token';

        $request = new WP_REST_Request('POST', self::ROUTE);
        $request->set_param('newPassword', 'unauthorized-password');
        $request->set_param('donor_id', $this->donor->id);

        $response = $this->dispatchRequest($request);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
        $this->assertFalse(
            wp_check_password('unauthorized-password', get_user_by('id', $this->userId)->user_pass, $this->userId),
            'An email-access token must not update the linked WordPress password.'
        );
    }

    /**
     * An authenticated non-donor must not combine their session with an email-access token.
     *
     * @since TBD
     */
    public function testAuthenticatedNonDonorCannotUpdatePasswordWithEmailAccessToken()
    {
        wp_set_current_user(0);
        give_update_option('email_access', 'enabled');
        Give()->email_access->init();
        Give()->email_access->set_verify_key($this->donor->id, $this->donor->email, 'email-access-token');

        $this->nonDonorUserId = wp_insert_user([
            'user_login' => 'givewp_non_donor_' . wp_generate_uuid4(),
            'user_email' => wp_generate_uuid4() . '@example.test',
            'user_pass' => 'unauthorized-password',
            'role' => 'subscriber',
        ]);

        Give()->donors->delete_by_user_id($this->nonDonorUserId);
        $this->assertFalse(Give()->donors->get_donor_by('user_id', $this->nonDonorUserId));

        wp_set_current_user($this->nonDonorUserId);
        $_GET['give_nl'] = 'email-access-token';

        $request = new WP_REST_Request('POST', self::ROUTE);
        $request->set_param('newPassword', 'unauthorized-password');
        $request->set_param('donor_id', $this->donor->id);

        $response = $this->dispatchRequest($request);

        $this->assertErrorResponse('rest_forbidden', $response, 403);
        $this->assertFalse(
            wp_check_password('unauthorized-password', get_user_by('id', $this->userId)->user_pass, $this->userId),
            'An unrelated authenticated user must not update the linked WordPress password.'
        );
    }
}

<?php

namespace Give\Tests\Unit\DonorDashboards\Tabs\EditProfileTab;

use Give\Donors\Models\Donor;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;

/**
 * @since TBD
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
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

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
     * @since TBD
     */
    public function tearDown(): void
    {
        if ($this->userId > 0 && get_user_by('id', $this->userId)) {
            wp_delete_user($this->userId);
        }

        parent::tearDown();
    }

    /**
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
     * @since TBD
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
}

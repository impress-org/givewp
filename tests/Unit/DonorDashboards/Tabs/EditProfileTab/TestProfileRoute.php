<?php

namespace Give\Tests\Unit\DonorDashboards\Tabs\EditProfileTab;

use Give\DonorDashboards\Profile;
use Give\Donors\Models\Donor;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;

/**
 * @since 4.20.0
 *
 * @coversDefaultClass \Give\DonorDashboards\Tabs\EditProfileTab\ProfileRoute
 */
class TestProfileRoute extends RestApiTestCase
{
    use RefreshDatabase;

    /** @var string */
    const ROUTE = '/give-api/v2/donor-dashboard/profile';

    /** @var int */
    private $userId;

    /** @var Donor */
    private $donor;

    /**
     * @since TBD Create the test donor without an avatar.
     * @since 4.20.0
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
            'avatarId' => null,
        ]);

        wp_set_current_user($this->userId);

        // Profile::__construct() resolves and caches the current donor once; the container
        // keeps that singleton across test methods in the same process, so re-register it here
        // to pick up each test's own current user instead of an earlier test's stale donor.
        give()->singleton('donorDashboard', Profile::class);

        // The route's own `id` arg schema predates WP 5.5 and still declares 'int' instead of
        // 'integer', which core's REST schema validator flags on every request to this route —
        // unrelated to this test; acknowledge it rather than let it fail the test.
        $this->setExpectedIncorrectUsage('rest_validate_value_from_schema');
        $this->setExpectedIncorrectUsage('rest_sanitize_value_from_schema');
    }

    /**
     * @since 4.20.0
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
     * @since 4.20.0
     */
    public function testPrimaryAddressDropsKeysOutsideTheKnownSet()
    {
        $request = $this->profileRequest([
            'primaryAddress' => [
                'line1' => '1 Main St',
                'city' => 'Springfield',
                'state' => 'IL',
                'country' => 'US',
                'zip' => '62701',
                'script' => 'alert(1)',
            ],
        ]);

        $data = $this->dispatchRequest($request)->get_data();

        $billing = $data['profile']['addresses']['billing'][0];

        $this->assertArrayNotHasKey('script', (array) $billing);
        $this->assertSame('1 Main St', $billing['line1']);
        $this->assertSame('Springfield', $billing['city']);
    }

    /**
     * @since 4.20.0
     */
    public function testAdditionalAddressDropsKeysOutsideTheKnownSet()
    {
        $request = $this->profileRequest([
            'primaryAddress' => ['line1' => '1 Main St'],
            'additionalAddresses' => [
                [
                    'line1' => '2 Side St',
                    'city' => 'Shelbyville',
                    'onfocus' => 'alert(1)',
                ],
            ],
        ]);

        $data = $this->dispatchRequest($request)->get_data();

        $additional = $data['profile']['addresses']['billing'][1];

        $this->assertArrayNotHasKey('onfocus', (array) $additional);
        $this->assertSame('2 Side St', $additional['line1']);
    }

    /**
     * @since 4.20.0
     */
    public function testPrimaryAddressKeepsAllKnownFields()
    {
        $request = $this->profileRequest([
            'primaryAddress' => [
                'line1' => '1 Main St',
                'line2' => 'Apt 2',
                'city' => 'Springfield',
                'state' => 'IL',
                'country' => 'US',
                'zip' => '62701',
            ],
        ]);

        $data = $this->dispatchRequest($request)->get_data();

        $billing = $data['profile']['addresses']['billing'][0];

        $this->assertSame('1 Main St', $billing['line1']);
        $this->assertSame('Apt 2', $billing['line2']);
        $this->assertSame('Springfield', $billing['city']);
        $this->assertSame('IL', $billing['state']);
        $this->assertSame('US', $billing['country']);
        $this->assertSame('62701', $billing['zip']);
    }

    /**
     * @since TBD
     */
    public function testAvatarIdRefusedForAttachmentTheDonorDoesNotOwn()
    {
        wp_set_current_user(0);

        $token = wp_generate_password(32, false);
        give()->email_access->set_verify_key($this->donor->id, $this->donor->email, $token);
        $_GET['give_nl'] = $token;

        // Profile::__construct() resolves and caches the current donor once; re-register the
        // singleton now that the token session is in place so it resolves this test's donor.
        give()->singleton('donorDashboard', \Give\DonorDashboards\Profile::class);

        $attachmentId = $this->createAttachment(0);

        $this->dispatchRequest($this->profileRequest(['avatarId' => $attachmentId]));

        $this->assertEmpty(
            $this->getStoredAvatarId(),
            'Avatar ID should not be stored for an attachment the donor does not own.'
        );
    }

    /**
     * @since TBD
     */
    public function testAvatarIdStoredForDonorStampedUpload()
    {
        wp_set_current_user(0);

        $token = wp_generate_password(32, false);
        give()->email_access->set_verify_key($this->donor->id, $this->donor->email, $token);
        $_GET['give_nl'] = $token;

        give()->singleton('donorDashboard', \Give\DonorDashboards\Profile::class);

        $attachmentId = $this->createAttachment(0);
        update_post_meta($attachmentId, '_give_donor_avatar_donor_id', $this->donor->id);

        $this->dispatchRequest($this->profileRequest(['avatarId' => $attachmentId]));

        $this->assertSame(
            $attachmentId,
            (int) $this->getStoredAvatarId(),
            'Avatar ID should be stored for an upload stamped with the donor ID.'
        );
    }

    /**
     * @since TBD
     */
    public function testAvatarIdStoredForOwnAuthoredAttachment()
    {
        $attachmentId = $this->createAttachment($this->userId);

        $this->dispatchRequest($this->profileRequest(['avatarId' => $attachmentId]));

        $this->assertSame(
            $attachmentId,
            (int) $this->getStoredAvatarId(),
            'Avatar ID should be stored for the logged-in donor\'s own upload.'
        );
    }

    /**
     * Read the donor's stored avatar ID straight from the database, bypassing any meta cache
     * left over from an earlier test in the same process.
     *
     * @since TBD
     */
    private function getStoredAvatarId()
    {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->prefix}give_donormeta WHERE donor_id = %d AND meta_key = '_give_donor_avatar_id' LIMIT 1",
                $this->donor->id
            )
        );
    }

    /**
     * @since TBD
     */
    private function createAttachment(int $authorId): int
    {
        return wp_insert_attachment(
            [
                'post_title' => 'Test avatar image',
                'post_status' => 'inherit',
                'post_mime_type' => 'image/png',
                'post_author' => $authorId,
            ]
        );
    }

    /**
     * @since 4.20.0
     *
     * @param array $overrides
     */
    private function profileRequest(array $overrides): WP_REST_Request
    {
        $data = array_merge(
            [
                'titlePrefix' => '',
                'firstName' => 'Jane',
                'lastName' => 'Doe',
                'company' => '',
                'primaryEmail' => $this->donor->email,
                'additionalEmails' => [],
                'primaryAddress' => [],
                'additionalAddresses' => [],
                'avatarId' => null,
                'isAnonymous' => 0,
            ],
            $overrides
        );

        $request = new WP_REST_Request('POST', self::ROUTE);
        $request->set_param('data', wp_json_encode($data));
        $request->set_param('id', $this->donor->id);

        return $request;
    }
}

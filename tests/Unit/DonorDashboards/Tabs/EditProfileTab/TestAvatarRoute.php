<?php

namespace Give\Tests\Unit\DonorDashboards\Tabs\EditProfileTab;

use Give\DonorDashboards\Profile;
use Give\Donors\Models\Donor;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since TBD
 *
 * @coversDefaultClass \Give\DonorDashboards\Tabs\EditProfileTab\AvatarRoute
 */
class TestAvatarRoute extends RestApiTestCase
{
    use RefreshDatabase;

    /** @var string */
    const ROUTE = '/give-api/v2/donor-dashboard/avatar';

    /** @var Donor */
    private $donor;

    /** @var array<int> */
    private $attachmentIds = [];

    /** @var array<string> */
    private $tempFiles = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        unset($_GET['give_nl'], $_COOKIE['give_nl'], $_FILES);

        $this->donor = Donor::factory()->create([
            'avatarId' => null,
        ]);

        // Profile::__construct() resolves and caches the current donor once; the container
        // keeps that singleton across test methods in the same process, so re-register it here
        // to pick up each test's own session instead of an earlier test's stale donor.
        give()->singleton('donorDashboard', Profile::class);
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        unset($_GET['give_nl'], $_COOKIE['give_nl'], $_FILES);

        foreach ($this->attachmentIds as $attachmentId) {
            if (get_post($attachmentId)) {
                wp_delete_attachment($attachmentId, true);
            }
        }

        foreach ($this->tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testUploadRefusedWhenStoredAvatarIsNotOwned()
    {
        $this->actAsDonorViaEmailToken();

        $otherDonor = Donor::factory()->create();
        $foreignAttachmentId = $this->createAttachment(0);
        update_post_meta($foreignAttachmentId, '_give_donor_avatar_donor_id', $otherDonor->id);
        give()->donor_meta->update_meta($this->donor->id, '_give_donor_avatar_id', $foreignAttachmentId);

        $data = $this->dispatchUpload()->get_data();

        $this->assertSame(401, $data['status']);
        $this->assertSame('unauthorized', $data['response']);
        $this->assertNotNull(
            get_post($foreignAttachmentId),
            'The stored avatar should not be deleted when it is not owned by the donor.'
        );
    }

    /**
     * @since TBD
     */
    public function testUploadReplacesOwnedStoredAvatar()
    {
        $this->actAsDonorViaEmailToken();

        $oldAttachmentId = $this->createAttachment(0);
        update_post_meta($oldAttachmentId, '_give_donor_avatar_donor_id', $this->donor->id);
        give()->donor_meta->update_meta($this->donor->id, '_give_donor_avatar_id', $oldAttachmentId);

        $data = $this->dispatchUpload()->get_data();

        $this->assertArrayHasKey('id', $data);
        $this->attachmentIds[] = (int) $data['id'];

        $this->assertNull(get_post($oldAttachmentId), 'The previous avatar should be deleted.');
        $this->assertSame(
            (int) $this->donor->id,
            (int) get_post_meta((int) $data['id'], '_give_donor_avatar_donor_id', true),
            'The new upload should be stamped with the donor ID.'
        );
    }

    /**
     * @since TBD
     */
    public function testUploadReplacesPreStampStoredAvatar()
    {
        $this->actAsDonorViaEmailToken();

        // Avatar stored before uploads were stamped: no stamp and no author to match.
        $oldAttachmentId = $this->createAttachment(0);
        give()->donor_meta->update_meta($this->donor->id, '_give_donor_avatar_id', $oldAttachmentId);

        $data = $this->dispatchUpload()->get_data();

        $this->assertArrayHasKey('id', $data);
        $this->attachmentIds[] = (int) $data['id'];

        $this->assertNull(get_post($oldAttachmentId), 'The previous avatar should be deleted.');
    }

    /**
     * @since TBD
     */
    private function actAsDonorViaEmailToken(): void
    {
        wp_set_current_user(0);

        $token = wp_generate_password(32, false);
        give()->email_access->set_verify_key($this->donor->id, $this->donor->email, $token);
        $_GET['give_nl'] = $token;

        // Profile::__construct() resolves and caches the current donor once; re-register the
        // singleton now that the token session is in place so it resolves this test's donor.
        give()->singleton('donorDashboard', Profile::class);
    }

    /**
     * @since TBD
     */
    private function createAttachment(int $authorId): int
    {
        $attachmentId = wp_insert_attachment(
            [
                'post_title' => 'Test avatar image',
                'post_status' => 'inherit',
                'post_mime_type' => 'image/png',
                'post_author' => $authorId,
            ]
        );

        $this->attachmentIds[] = $attachmentId;

        return $attachmentId;
    }

    /**
     * @since TBD
     */
    private function dispatchUpload(): WP_REST_Response
    {
        if (! function_exists('uopz_set_return')) {
            $this->markTestSkipped('The uopz extension is required to simulate an uploaded file.');
        }

        $tempFile = trailingslashit(sys_get_temp_dir()) . 'give-test-avatar-' . uniqid('', true) . '.png';
        file_put_contents(
            $tempFile,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2ZQAAAABJRU5ErkJggg==')
        );
        $this->tempFiles[] = $tempFile;

        $_FILES['async-upload'] = [
            'name' => 'avatar.png',
            'type' => 'image/png',
            'tmp_name' => $tempFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tempFile),
        ];

        // wp_handle_upload() only accepts files that arrived over HTTP; let the test double act as one.
        uopz_set_return('is_uploaded_file', true);
        uopz_set_return('move_uploaded_file', function ($from, $to) {
            return rename($from, $to);
        }, true);
        // The route passes the upload URL to getimagesize(), which the test env cannot serve.
        uopz_set_return('getimagesize', function () {
            return [1, 1, IMAGETYPE_PNG, 'width="1" height="1"', 'mime' => 'image/png'];
        }, true);

        try {
            return $this->dispatchRequest(new WP_REST_Request('POST', self::ROUTE));
        } finally {
            uopz_unset_return('is_uploaded_file');
            uopz_unset_return('move_uploaded_file');
            uopz_unset_return('getimagesize');
        }
    }
}

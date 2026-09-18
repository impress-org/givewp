<?php

namespace Give\Tests\Unit\DonorDashboards\Repositories;

use Give\DonorDashboards\Profile;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorPendingEmailRepository;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.14.2
 */
final class TestProfile extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.14.2
     */
    public function testAvatarBelongsToCurrentUserShouldReturnTrue(): void
    {
        $user = self::factory()->user->create_and_get();

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
        ]);

        wp_set_current_user($donor->userId);

        $attachment = self::factory()->attachment->create_and_get([
            'post_author' => $donor->userId,
            'post_title' => 'test',
            'post_content' => 'test',
            'post_status' => 'inherit',
            'post_mime_type' => 'image/jpeg',
        ]);

        give()->donor_meta->update_meta($donor->id, '_give_donor_avatar_id', $attachment->ID);

        $profileRepository = new Profile();

        $this->assertTrue($profileRepository->avatarBelongsToCurrentUser());
    }

    /**
     * @since 3.14.2
     */
    public function testAvatarBelongsToCurrentUserShouldReturnTrueWithAvatarParam(): void
    {
        $user = self::factory()->user->create_and_get();

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
        ]);

        wp_set_current_user($donor->userId);

        $attachment = self::factory()->attachment->create_and_get([
            'post_author' => $donor->userId,
            'post_title' => 'test',
            'post_content' => 'test',
            'post_status' => 'inherit',
            'post_mime_type' => 'image/jpeg',
        ]);

        give()->donor_meta->update_meta($donor->id, '_give_donor_avatar_id', $attachment->ID);

        $profileRepository = new Profile();

        $this->assertTrue($profileRepository->avatarBelongsToCurrentUser($attachment->ID));
    }

    /**
     * @since 3.14.2
     */
    public function testAvatarBelongsToCurrentUserShouldReturnFalse(): void
    {
        $user = self::factory()->user->create_and_get();

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
        ]);

        wp_set_current_user($donor->userId);

        $attachment = self::factory()->attachment->create_and_get([
            'post_author' => $donor->userId + 1, // Different user
            'post_title' => 'test',
            'post_content' => 'test',
            'post_status' => 'inherit',
            'post_mime_type' => 'image/jpeg',
        ]);

        give()->donor_meta->update_meta($donor->id, '_give_donor_avatar_id', $attachment->ID);


        $profileRepository = new Profile();

        $this->assertFalse($profileRepository->avatarBelongsToCurrentUser());
    }

    /**
     * @since TBD SVUL-118: the Donor Dashboard profile update is the same untrusted write path.
     */
    public function testUpdateDivertsNewAdditionalEmailsToPending(): void
    {
        $user = self::factory()->user->create_and_get();

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
            'email' => 'primary@givewp.com',
            'additionalEmails' => [],
        ]);

        wp_set_current_user($donor->userId);

        $profileRepository = new Profile($donor->id);

        $profileRepository->update([
            'primaryEmail' => 'primary@givewp.com',
            'additionalEmails' => ['hijack@givewp.com'],
        ]);

        $updatedDonor = Donor::find($donor->id);

        $this->assertSame([], $updatedDonor->additionalEmails);

        $pending = (new DonorPendingEmailRepository())->all($donor->id);
        $this->assertCount(1, $pending);
        $this->assertSame('hijack@givewp.com', $pending[0]['email']);
    }
}

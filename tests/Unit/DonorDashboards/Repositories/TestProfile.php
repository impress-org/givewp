<?php

namespace Give\Tests\Unit\DonorDashboards\Repositories;

use Give\DonorDashboards\Profile;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
final class TestProfile extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
}

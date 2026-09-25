<?php

namespace Give\Tests\Unit\DonorDashboards\Repositories;

use Give\DonorDashboards\Profile;
use Give\Donors\Models\Donor;
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
     * @since 4.17.0 Non-admins cannot add unverified emails via dashboard.
     */
    public function testUpdateShouldNotAddNewUnverifiedEmails(): void
    {
        $user = self::factory()->user->create_and_get();

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
            'email' => 'primary@test.com',
            'additionalEmails' => ['existing@test.com'],
        ]);

        wp_set_current_user($user->ID);

        $profile = new Profile();
        $profile->update([
            'primaryEmail' => 'primary@test.com',
            'additionalEmails' => ['newemail@test.com'],
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $updatedDonor = Donor::find($donor->id);

        // New email should not be stored
        $this->assertNotContains('newemail@test.com', $updatedDonor->additionalEmails);
        // Existing email should be removed (not in the incoming list)
        $this->assertEmpty($updatedDonor->additionalEmails);
    }

    /**
     * @since 4.17.0 Non-admins can remove existing emails via dashboard.
     */
    public function testUpdateShouldRemoveExistingEmails(): void
    {
        $user = self::factory()->user->create_and_get();

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
            'email' => 'primary@test.com',
            'additionalEmails' => ['existing1@test.com', 'existing2@test.com'],
        ]);

        wp_set_current_user($user->ID);

        $profile = new Profile();
        $profile->update([
            'primaryEmail' => 'primary@test.com',
            'additionalEmails' => ['existing1@test.com'],
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $updatedDonor = Donor::find($donor->id);

        // Should keep only the one in the list
        $this->assertEquals(['existing1@test.com'], $updatedDonor->additionalEmails);
    }

    /**
     * @since 4.17.0 Non-admins can swap primary and additional emails via dashboard.
     */
    public function testUpdateShouldSwapPrimaryAndAdditionalEmails(): void
    {
        $user = self::factory()->user->create_and_get();

        /** @var Donor $donor */
        $donor = Donor::factory()->create([
            'userId' => $user->ID,
            'email' => 'primary@test.com',
            'additionalEmails' => ['secondary@test.com'],
        ]);

        wp_set_current_user($user->ID);

        $profile = new Profile();
        $profile->update([
            'primaryEmail' => 'secondary@test.com',
            'additionalEmails' => ['secondary@test.com', 'primary@test.com'],
            'firstName' => 'John',
            'lastName' => 'Doe',
        ]);

        $updatedDonor = Donor::find($donor->id);

        // Primary should be updated
        $this->assertEquals('secondary@test.com', $updatedDonor->email);
        // Both emails should be in additional (old primary + new additional from the list)
        $this->assertContains('primary@test.com', $updatedDonor->additionalEmails);
        $this->assertContains('secondary@test.com', $updatedDonor->additionalEmails);
    }
}

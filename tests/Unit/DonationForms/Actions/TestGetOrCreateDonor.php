<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Exception;
use Give\DonationForms\Actions\GetOrCreateDonor;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestGetOrCreateDonor extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.9.0 Add phone support to GetOrCreateDonor action
     * @since 3.2.0
     *
     * @throws Exception
     */
    public function testShouldReturnExistingDonorWithMatchingEmail(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $action = new GetOrCreateDonor();
        $donorFromActionWithMatchingEmail = $action(null, $donor->email, $donor->firstName, $donor->lastName,
            $donor->prefix, $donor->phone);

        $this->assertEquals($donor->toArray(), $donorFromActionWithMatchingEmail->toArray());
        $this->assertFalse($action->donorCreated);
    }

    /**
     * @since 3.9.0 Add phone support to GetOrCreateDonor action
     * @since 3.2.0
     *
     * @throws Exception
     */
    public function testShouldReturnExistingDonorWithMatchingUserId(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $action = new GetOrCreateDonor();
        $donorFromActionWithMatchingUserId = $action($donor->userId, $donor->email, 'billing first name',
            'billing last name', null, null);

        $this->assertEquals($donor->toArray(), $donorFromActionWithMatchingUserId->toArray());
        $this->assertFalse($action->donorCreated);
    }

    /**
     * @since 3.9.0 Add phone support to GetOrCreateDonor action
     * @since 3.2.0
     * @throws Exception
     */
    public function testShouldReturnExistingDonorWithoutAddingSubmittedEmail(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $action = new GetOrCreateDonor();
        $donorFromAction = $action($donor->userId, 'newDonor@givewp.com', 'billing first name',
            'billing last name', null, null);

        $this->assertEquals($donor->additionalEmails, Donor::find($donor->id)->additionalEmails);
        $this->assertFalse($donorFromAction->hasEmail('newDonor@givewp.com'));
        $this->assertFalse($action->donorCreated);
    }

    /**
     * @since 3.9.0 Add phone support to GetOrCreateDonor action
     * @since 3.2.0
     *
     * @throws Exception
     */
    public function testShouldReturnExistingDonorWithUserIdAndNotUpdateAdditionalEmails(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $donorWithExistingEmail = Donor::factory()->create();
        $action = new GetOrCreateDonor();
        $donorFromActionWithMatchingUserId = $action($donor->userId, $donorWithExistingEmail->email,
            'billing first name', 'billing last name', null, null);

        $this->assertEquals($donor->toArray(), $donorFromActionWithMatchingUserId->toArray());
        $this->assertFalse($action->donorCreated);
    }

    /**
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldLinkLoggedInUserToExistingDonorWithMatchingAccountEmail(): void
    {
        $user = $this->factory()->user->create_and_get(['role' => 'subscriber']);
        $donor = Donor::factory()->create(['email' => $user->user_email, 'userId' => null]);

        $action = new GetOrCreateDonor();
        $donorFromAction = $action($user->ID, $user->user_email, $donor->firstName, $donor->lastName, null, null);

        $this->assertSame($donor->id, $donorFromAction->id);
        $this->assertSame($user->ID, $donorFromAction->userId);
        $this->assertSame($user->ID, Donor::find($donor->id)->userId);
        $this->assertFalse($action->donorCreated);
    }

    /**
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldNotLinkLoggedInUserToExistingDonorWhenAccountEmailDiffers(): void
    {
        $user = $this->factory()->user->create_and_get(['role' => 'subscriber']);
        $donor = Donor::factory()->create(['userId' => null]);

        $action = new GetOrCreateDonor();
        $donorFromAction = $action($user->ID, $donor->email, $donor->firstName, $donor->lastName, null, null);

        $this->assertSame($donor->id, $donorFromAction->id);
        $this->assertEmpty(Donor::find($donor->id)->userId);
    }

    /**
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldNotRelinkExistingDonorConnectedToAnotherUser(): void
    {
        $user = $this->factory()->user->create_and_get(['role' => 'subscriber']);
        $otherUserId = $this->factory()->user->create();
        $donor = Donor::factory()->create(['email' => $user->user_email, 'userId' => $otherUserId]);

        $action = new GetOrCreateDonor();
        $action($user->ID, $user->user_email, $donor->firstName, $donor->lastName, null, null);

        $this->assertSame($otherUserId, Donor::find($donor->id)->userId);
    }

    /**
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldNotLinkGuestToExistingDonorWithMatchingUserEmail(): void
    {
        $user = $this->factory()->user->create_and_get(['role' => 'subscriber']);
        $donor = Donor::factory()->create(['email' => $user->user_email, 'userId' => null]);

        $action = new GetOrCreateDonor();
        $action(null, $user->user_email, $donor->firstName, $donor->lastName, null, null);

        $this->assertEmpty(Donor::find($donor->id)->userId);
    }

    /**
     * @since 3.9.0 Add phone support to GetOrCreateDonor action
     * @since 3.2.0
     *
     * @throws Exception
     */
    public function testShouldReturnNewDonor(): void
    {
        $action = new GetOrCreateDonor();
        $donorFromAction = $action(null, 'billMurray@givewp.com', 'Bill', 'Murray', 'Mr.', '+120155501234');

        $this->assertSame('Bill Murray', $donorFromAction->name);
        $this->assertSame('Bill', $donorFromAction->firstName);
        $this->assertSame('Murray', $donorFromAction->lastName);
        $this->assertSame('Mr.', $donorFromAction->prefix);
        $this->assertSame('billMurray@givewp.com', $donorFromAction->email);
        $this->assertSame('+120155501234', $donorFromAction->phone);
        $this->assertTrue($action->donorCreated);
    }
}

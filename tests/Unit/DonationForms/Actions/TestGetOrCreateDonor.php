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
    public function testShouldReturnExistingDonorWithUserIdAndUpdateAdditionalEmails(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $action = new GetOrCreateDonor();
        $donorFromActionWithMatchingUserId = $action($donor->userId, 'newDonor@givewp.com', 'billing first name',
            'billing last name', null, null);
        $donor->additionalEmails = array_merge($donor->additionalEmails ?? [], ['newDonor@givewp.com']);
        $donor->save();

        $this->assertEquals($donor->toArray(), $donorFromActionWithMatchingUserId->toArray());
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

    /**
     * Regression for #8286: a returning donor whose stored phone is empty must
     * not be saved when the submitted form provides no phone value.
     *
     * @since TBD
     *
     * @throws Exception
     */
    public function testShouldNotSaveDonorWhenStoredAndIncomingPhoneAreEmpty(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $donor->phone = '';
        $donor->save();

        $updateCount = 0;
        $updatingAction = function () use (&$updateCount) {
            $updateCount++;
        };
        add_action('givewp_donor_updating', $updatingAction);

        try {
            $action = new GetOrCreateDonor();
            $result = $action($donor->userId, $donor->email, $donor->firstName, $donor->lastName,
                $donor->prefix, null);

            $this->assertSame($donor->id, $result->id);
            $this->assertFalse($action->donorCreated);
            $this->assertSame(0, $updateCount, 'Donor save should be skipped when no phone is submitted.');
            $this->assertSame('', give()->donors->getById($donor->id)->phone);
        } finally {
            remove_action('givewp_donor_updating', $updatingAction);
        }
    }
}

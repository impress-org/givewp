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
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldReturnExistingDonorWithMatchingEmail(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $donorFromActionWithMatchingEmail = (new GetOrCreateDonor)(null, $donor->email, $donor->firstName, $donor->lastName, $donor->prefix);

        $this->assertEquals($donor->toArray(), $donorFromActionWithMatchingEmail->toArray());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldReturnExistingDonorWithMatchingUserId(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $donorFromActionWithMatchingUserId = (new GetOrCreateDonor)($donor->userId, $donor->email, 'billing first name', 'billing last name', null);

        $this->assertEquals($donor->toArray(), $donorFromActionWithMatchingUserId->toArray());
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testShouldReturnExistingDonorWithUserIdAndUpdateAdditionalEmails(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $donorFromActionWithMatchingUserId = (new GetOrCreateDonor)($donor->userId, 'newDonor@givewp.com', 'billing first name', 'billing last name', null);
        $donor->additionalEmails = array_merge($donor->additionalEmails ?? [], ['newDonor@givewp.com']);
        $donor->save();

        $this->assertEquals($donor->toArray(), $donorFromActionWithMatchingUserId->toArray());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldReturnExistingDonorWithUserIdAndNotUpdateAdditionalEmails(): void
    {
        $donor = Donor::factory()->create(['userId' => 1]);
        $donorWithExistingEmail = Donor::factory()->create();
        $donorFromActionWithMatchingUserId = (new GetOrCreateDonor)($donor->userId, $donorWithExistingEmail->email, 'billing first name', 'billing last name', null);

        $this->assertEquals($donor->toArray(), $donorFromActionWithMatchingUserId->toArray());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldReturnNewDonor(): void
    {
        $donorFromAction = (new GetOrCreateDonor)(null, 'billMurray@givewp.com', 'Bill', 'Murray', 'Mr.');

        $this->assertSame('Bill Murray', $donorFromAction->name);
        $this->assertSame('Bill', $donorFromAction->firstName);
        $this->assertSame('Murray', $donorFromAction->lastName);
        $this->assertSame('Mr.', $donorFromAction->prefix);
        $this->assertSame('billMurray@givewp.com', $donorFromAction->email);
    }
}
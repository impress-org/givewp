<?php

namespace Give\Tests\Unit\Donors\Actions;

use Give\Donors\Actions\VerifyAdditionalEmail;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorPendingEmailRepository;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 *
 * @coversDefaultClass VerifyAdditionalEmail
 */
class TestVerifyAdditionalEmail extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testVerifyShouldAppendEmailAndDeletePendingEntry(): void
    {
        $donor = Donor::factory()->create(['additionalEmails' => []]);
        $repository = new DonorPendingEmailRepository();
        $token = $repository->add($donor->id, 'newlyverified@givewp.com');

        $status = (new VerifyAdditionalEmail())->verify($token);

        $this->assertSame(VerifyAdditionalEmail::STATUS_VERIFIED, $status);
        $this->assertTrue(Donor::find($donor->id)->hasEmail('newlyverified@givewp.com'));
        $this->assertSame([], $repository->all($donor->id));
    }

    /**
     * @since TBD
     */
    public function testVerifyShouldReturnInvalidForUnknownToken(): void
    {
        $this->assertSame(
            VerifyAdditionalEmail::STATUS_INVALID,
            (new VerifyAdditionalEmail())->verify('unknownunknownunknown')
        );
    }

    /**
     * @since TBD
     */
    public function testVerifyShouldReturnInvalidForExpiredToken(): void
    {
        $donor = Donor::factory()->create();

        give()->donor_meta->add_meta($donor->id, DonorMetaKeys::PENDING_EMAIL, [
            'token' => 'expiredtokenexpiredtoke',
            'email' => 'expired@givewp.com',
            'createdAt' => time() - DAY_IN_SECONDS - 1,
        ]);

        $this->assertSame(
            VerifyAdditionalEmail::STATUS_INVALID,
            (new VerifyAdditionalEmail())->verify('expiredtokenexpiredtoke')
        );
        $this->assertFalse(Donor::find($donor->id)->hasEmail('expired@givewp.com'));
    }

    /**
     * @since TBD Closes the race: another donor claimed the email after the request was made.
     */
    public function testVerifyShouldReturnClaimedWhenEmailOwnedByAnotherDonor(): void
    {
        $donor = Donor::factory()->create(['additionalEmails' => []]);
        $repository = new DonorPendingEmailRepository();
        $token = $repository->add($donor->id, 'scooped@givewp.com');

        Donor::factory()->create(['email' => 'scooped@givewp.com']);

        $status = (new VerifyAdditionalEmail())->verify($token);

        $this->assertSame(VerifyAdditionalEmail::STATUS_CLAIMED, $status);
        $this->assertFalse(Donor::find($donor->id)->hasEmail('scooped@givewp.com'));
        $this->assertSame([], $repository->all($donor->id));
    }

    /**
     * @since TBD
     */
    public function testVerifyShouldNotDuplicateWhenDonorAlreadyHasTheEmail(): void
    {
        $donor = Donor::factory()->create(['email' => 'already@givewp.com', 'additionalEmails' => []]);
        $repository = new DonorPendingEmailRepository();

        give()->donor_meta->add_meta($donor->id, DonorMetaKeys::PENDING_EMAIL, [
            'token' => 'alreadytokenalreadytok',
            'email' => 'already@givewp.com',
            'createdAt' => time(),
        ]);

        $status = (new VerifyAdditionalEmail())->verify('alreadytokenalreadytok');

        $this->assertSame(VerifyAdditionalEmail::STATUS_VERIFIED, $status);

        $rows = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', DonorMetaKeys::ADDITIONAL_EMAILS)
            ->getAll();

        $this->assertEmpty($rows);
        $this->assertSame([], $repository->all($donor->id));
    }
}

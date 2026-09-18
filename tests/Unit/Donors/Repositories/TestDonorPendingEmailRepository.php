<?php

namespace Give\Tests\Unit\Donors\Repositories;

use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorPendingEmailRepository;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 *
 * @coversDefaultClass DonorPendingEmailRepository
 */
class TestDonorPendingEmailRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testAddShouldStorePendingEmailAndReturnToken(): void
    {
        $donor = Donor::factory()->create();

        $token = (new DonorPendingEmailRepository())->add($donor->id, 'pending@givewp.com');

        $this->assertIsString($token);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{24}$/', $token);

        $pending = (new DonorPendingEmailRepository())->all($donor->id);
        $this->assertCount(1, $pending);
        $this->assertSame('pending@givewp.com', $pending[0]['email']);
        $this->assertSame($token, $pending[0]['token']);
    }

    /**
     * @since TBD
     */
    public function testAddShouldReturnNullWhenEmailAlreadyPending(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorPendingEmailRepository();

        $this->assertIsString($repository->add($donor->id, 'pending@givewp.com'));
        $this->assertNull($repository->add($donor->id, 'pending@givewp.com'));
        $this->assertCount(1, $repository->all($donor->id));
    }

    /**
     * @since TBD
     */
    public function testAddShouldReturnNullWhenPendingLimitReached(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorPendingEmailRepository();

        for ($i = 1; $i <= 5; $i++) {
            $this->assertIsString($repository->add($donor->id, "pending{$i}@givewp.com"));
        }

        $this->assertNull($repository->add($donor->id, 'pending6@givewp.com'));
        $this->assertCount(5, $repository->all($donor->id));
    }

    /**
     * @since TBD
     */
    public function testAddShouldReturnNullWhenEmailOwnedByAnyDonor(): void
    {
        $donor = Donor::factory()->create();
        Donor::factory()->create(['email' => 'claimed-primary@givewp.com']);
        Donor::factory()->create(['additionalEmails' => ['claimed-additional@givewp.com']]);

        $repository = new DonorPendingEmailRepository();

        $this->assertNull($repository->add($donor->id, 'claimed-primary@givewp.com'));
        $this->assertNull($repository->add($donor->id, 'claimed-additional@givewp.com'));
        $this->assertSame([], $repository->all($donor->id));
    }

    /**
     * @since TBD
     */
    public function testAddShouldReturnNullForInvalidEmail(): void
    {
        $donor = Donor::factory()->create();

        $this->assertNull((new DonorPendingEmailRepository())->add($donor->id, 'not-an-email'));
    }

    /**
     * @since TBD
     */
    public function testTokensShouldBeUnique(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorPendingEmailRepository();

        $tokens = [
            $repository->add($donor->id, 'one@givewp.com'),
            $repository->add($donor->id, 'two@givewp.com'),
            $repository->add($donor->id, 'three@givewp.com'),
        ];

        $this->assertCount(3, array_unique($tokens));
    }

    /**
     * @since TBD
     */
    public function testAllShouldPruneExpiredEntries(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorPendingEmailRepository();

        $this->assertIsString($repository->add($donor->id, 'fresh@givewp.com'));

        give()->donor_meta->add_meta($donor->id, DonorMetaKeys::PENDING_EMAIL, [
            'token' => 'expiredtokenexpiredtoke',
            'email' => 'expired@givewp.com',
            'createdAt' => time() - DAY_IN_SECONDS - 1,
        ]);

        $pending = $repository->all($donor->id);

        $this->assertCount(1, $pending);
        $this->assertSame('fresh@givewp.com', $pending[0]['email']);
    }

    /**
     * @since TBD
     */
    public function testFindByTokenShouldReturnEntry(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorPendingEmailRepository();
        $token = $repository->add($donor->id, 'findme@givewp.com');

        $match = $repository->findByToken($token);

        $this->assertNotNull($match);
        $this->assertSame($donor->id, $match['donorId']);
        $this->assertSame('findme@givewp.com', $match['entry']['email']);
        $this->assertSame($token, $match['entry']['token']);
    }

    /**
     * @since TBD
     */
    public function testFindByTokenShouldReturnNullForUnknownToken(): void
    {
        Donor::factory()->create();

        $this->assertNull((new DonorPendingEmailRepository())->findByToken('unknownunknownunknown'));
    }

    /**
     * @since TBD
     */
    public function testFindByTokenShouldIgnoreExpiredTokens(): void
    {
        $donor = Donor::factory()->create();

        give()->donor_meta->add_meta($donor->id, DonorMetaKeys::PENDING_EMAIL, [
            'token' => 'expiredtokenexpiredtoke',
            'email' => 'expired@givewp.com',
            'createdAt' => time() - DAY_IN_SECONDS - 1,
        ]);

        $this->assertNull((new DonorPendingEmailRepository())->findByToken('expiredtokenexpiredtoke'));
    }

    /**
     * @since TBD
     */
    public function testDeleteShouldRemoveEntry(): void
    {
        $donor = Donor::factory()->create();
        $repository = new DonorPendingEmailRepository();
        $repository->add($donor->id, 'deleteme@givewp.com');

        $entry = $repository->all($donor->id)[0];

        $this->assertTrue($repository->delete($donor->id, $entry));
        $this->assertSame([], $repository->all($donor->id));
    }
}

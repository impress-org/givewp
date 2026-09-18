<?php

namespace Give\Tests\Unit\Donors\Repositories;

use Give\DonationForms\Actions\GetOrCreateDonor;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorPendingEmailRepository;
use Give\Donors\Repositories\DonorRepository;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 *
 * @coversDefaultClass DonorRepository
 */
class TestDonorRepositoryAdditionalEmails extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, string>
     */
    private $sentMailTo = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->sentMailTo = [];

        add_filter('pre_wp_mail', function ($null, $attributes) {
            $this->sentMailTo[] = $attributes['to'];

            return false;
        }, 10, 2);
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        remove_all_filters('pre_wp_mail');

        parent::tearDown();
    }

    /**
     * @since TBD
     *
     * @return string[] additional emails stored in the database for the donor
     */
    private function storedAdditionalEmails(int $donorId): array
    {
        $rows = DB::table('give_donormeta')
            ->select(['meta_value', 'email'])
            ->where('donor_id', $donorId)
            ->where('meta_key', DonorMetaKeys::ADDITIONAL_EMAILS)
            ->getAll();

        return array_column((array)$rows, 'email');
    }

    /**
     * @since TBD
     */
    public function testUpdateByUntrustedUserDivertsNewEmailsToPending(): void
    {
        $donor = Donor::factory()->create([
            'additionalEmails' => ['stored@givewp.com'],
        ]);

        $donor->additionalEmails = ['stored@givewp.com', 'new@givewp.com'];

        (new DonorRepository())->update($donor);

        $this->assertSame(['stored@givewp.com'], $this->storedAdditionalEmails($donor->id));
        $this->assertSame(['stored@givewp.com'], $donor->additionalEmails);

        $pending = (new DonorPendingEmailRepository())->all($donor->id);
        $this->assertCount(1, $pending);
        $this->assertSame('new@givewp.com', $pending[0]['email']);

        $this->assertSame(['new@givewp.com'], $this->sentMailTo);
    }

    /**
     * @since TBD Proves the guard fires for the exact SVUL-118 attacker: a Subscriber who owns the donor record.
     */
    public function testUpdateByOwnerSubscriberDivertsNewEmailsToPending(): void
    {
        $userId = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($userId);

        $donor = Donor::factory()->create(['userId' => $userId]);

        $donor->additionalEmails = ['hijack@givewp.com'];

        (new DonorRepository())->update($donor);

        $this->assertSame([], $this->storedAdditionalEmails($donor->id));
        $this->assertSame([], $donor->additionalEmails);

        $pending = (new DonorPendingEmailRepository())->all($donor->id);
        $this->assertCount(1, $pending);
        $this->assertSame('hijack@givewp.com', $pending[0]['email']);
    }

    /**
     * @since TBD
     */
    public function testUpdateByAdminWritesEmailsDirectly(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));

        $donor = Donor::factory()->create();

        $donor->additionalEmails = ['direct@givewp.com'];

        (new DonorRepository())->update($donor);

        $this->assertSame(['direct@givewp.com'], $this->storedAdditionalEmails($donor->id));
        $this->assertSame([], (new DonorPendingEmailRepository())->all($donor->id));
        $this->assertSame([], $this->sentMailTo);
    }

    /**
     * @since TBD
     */
    public function testUpdateByUntrustedUserAllowsRemovingStoredEmail(): void
    {
        $donor = Donor::factory()->create([
            'additionalEmails' => ['keep@givewp.com', 'remove@givewp.com'],
        ]);

        $donor->additionalEmails = ['keep@givewp.com'];

        (new DonorRepository())->update($donor);

        $this->assertSame(['keep@givewp.com'], $this->storedAdditionalEmails($donor->id));
        $this->assertSame([], (new DonorPendingEmailRepository())->all($donor->id));
    }

    /**
     * @since TBD
     */
    public function testUpdateByUntrustedUserDropsEmailsOwnedByAnotherDonor(): void
    {
        Donor::factory()->create(['email' => 'victim@givewp.com']);

        $donor = Donor::factory()->create();

        $donor->additionalEmails = ['victim@givewp.com'];

        (new DonorRepository())->update($donor);

        $this->assertSame([], $this->storedAdditionalEmails($donor->id));
        $this->assertSame([], (new DonorPendingEmailRepository())->all($donor->id));
        $this->assertSame([], $this->sentMailTo);
    }

    /**
     * @since TBD
     */
    public function testAddVerifiedAdditionalEmailBypassesVerification(): void
    {
        $userId = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($userId);

        $donor = Donor::factory()->create(['userId' => $userId, 'additionalEmails' => []]);

        $this->assertTrue((new DonorRepository())->addVerifiedAdditionalEmail($donor, 'trusted@givewp.com'));
        $this->assertTrue($donor->hasEmail('trusted@givewp.com'));
        $this->assertSame(['trusted@givewp.com'], $this->storedAdditionalEmails($donor->id));
        $this->assertSame([], (new DonorPendingEmailRepository())->all($donor->id));
        $this->assertSame([], $this->sentMailTo);
    }

    /**
     * @since TBD
     */
    public function testAddVerifiedAdditionalEmailRejectsEmailTheDonorAlreadyHas(): void
    {
        $donor = Donor::factory()->create(['email' => 'primary@givewp.com', 'additionalEmails' => []]);

        $this->assertFalse((new DonorRepository())->addVerifiedAdditionalEmail($donor, 'primary@givewp.com'));
        $this->assertSame([], $this->storedAdditionalEmails($donor->id));
    }

    /**
     * @since TBD Regression: the checkout flow (payment email context) keeps attaching emails directly.
     */
    public function testGetOrCreateDonorAttachesCheckoutEmailDirectly(): void
    {
        $userId = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($userId);

        $donor = Donor::factory()->create([
            'userId' => $userId,
            'email' => 'original@givewp.com',
            'additionalEmails' => [],
        ]);

        $action = new GetOrCreateDonor();

        $result = $action($userId, 'checkout@givewp.com', 'Murray', 'Bill', null, null);

        $this->assertSame($donor->id, $result->id);
        $this->assertTrue($result->hasEmail('checkout@givewp.com'));
        $this->assertSame(['checkout@givewp.com'], $this->storedAdditionalEmails($donor->id));
        $this->assertSame([], (new DonorPendingEmailRepository())->all($donor->id));
        $this->assertSame([], $this->sentMailTo);
    }
}

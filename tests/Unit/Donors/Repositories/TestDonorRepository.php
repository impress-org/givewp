<?php

namespace Give\Tests\Unit\Donors\Repositories;

use Exception;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorRepository;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Repositories\SubscriptionRepository;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 2.19.6
 *
 * @coversDefaultClass SubscriptionRepository
 */
class TestDonorRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetByIdShouldReturnDonor()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $repository = new DonorRepository();

        /** @var Donor $donorFromRepository */
        $donorFromRepository = $repository->queryById($donor->id)->get();

        /** @var Donor $donorQuery */
        $donorQuery = $repository->prepareQuery()
            ->where('id', $donorFromRepository->id)
            ->get();

        $this->assertEquals($donor->id, $donorQuery->id);
    }

    /**
     * @since 3.7.0 Test "phone" property
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldAddDonorToDatabase()
    {
        $donor = new Donor(Donor::factory()->definition());
        $donor->additionalEmails = ["chrisFarley2@givewp.com", "chrisFarley3@givewp.com"];

        $repository = new DonorRepository();

        $repository->insert($donor);

        /** @var Donor $query */
        $query = $repository->prepareQuery()
            ->where('id', $donor->id)
            ->get();


        // simulate asserting database has values
        $this->assertInstanceOf(Donor::class, $donor);
        $this->assertEquals($query->createdAt->format('Y-m-d H:i:s'), $donor->createdAt->format('Y-m-d H:i:s'));
        $this->assertEquals($query->id, $donor->id);
        $this->assertEquals($query->name, $donor->name);
        $this->assertEquals($query->firstName, $donor->firstName);
        $this->assertEquals($query->lastName, $donor->lastName);
        $this->assertEquals($query->email, $donor->email);
        $this->assertEquals($query->additionalEmails, $donor->additionalEmails);
        $this->assertEquals($query->phone, $donor->phone);
    }

    /**
     * @since 3.7.0
     *
     * @throws Exception
     */
    public function testInsertShouldAddDonorWithEmptyPhoneToDatabase()
    {
        $donorMissingPhone = new Donor([
            'name' => 'Bill Murray',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonorRepository();

        $repository->insert($donorMissingPhone);

        /** @var Donor $query */
        $query = $repository->prepareQuery()
            ->where('id', $donorMissingPhone->id)
            ->get();

        $this->assertEmpty($query->phone);
    }

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donorMissingFirstName = new Donor([
            'name' => 'Bill Murray',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonorRepository();

        $repository->insert($donorMissingFirstName);
    }

    /**
     * @since 3.7.0 Test "phone" property
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldUpdateDonorValuesInTheDatabase()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();
        $repository = new DonorRepository();

        $donor->firstName = "Chris";
        $donor->lastName = "Farley";
        $donor->additionalEmails = ["chrisFarley2@givewp.com", "chrisFarley3@givewp.com"];
        $donor->phone = '+1555444333';

        $repository->update($donor);

        /** @var object $query */
        $query = $repository->prepareQuery()
            ->where('id', $donor->id)
            ->get();

        // assert updated values from the database
        $this->assertEquals("Chris", $query->firstName);
        $this->assertEquals("Farley", $query->lastName);
        $this->assertEquals($donor->additionalEmails, $query->additionalEmails);
        $this->assertEquals($donor->phone, $query->phone);
    }

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldFailValidationAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donorMissingFirstName = new Donor([
            'name' => 'Bill Murray',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonorRepository();

        $repository->update($donorMissingFirstName);
    }

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteShouldRemoveDonorFromTheDatabase()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $repository = new DonorRepository();

        $repository->delete($donor);

        $donorQuery = $repository->prepareQuery()
            ->where('id', $donor->id)
            ->get();

        $donorMetaQuery =
            DB::table('give_donormeta')
                ->where('donor_id', $donor->id)
                ->getAll();

        $this->assertNull($donorQuery);
        $this->assertEmpty($donorMetaQuery);
    }

    /**
     * @since 3.20.0
     * @throws \Give\Framework\Exceptions\Primitives\Exception|Exception
     */
    public function testInsertShouldSafelyStoreMetaValues(): void
    {
        $name = (object)['first' => 'Jon', 'last' => 'Doe'];

        $serializedFirstName = serialize($name);

        $donor = new Donor(array_merge(Donor::factory()->definition(), [
            'firstName' => $serializedFirstName,
        ]));

        $repository = new DonorRepository();

        $repository->insert($donor);

        $metaValue = give()->donor_meta->get_meta($donor->id, DonorMetaKeys::FIRST_NAME, true);
        $metaQuery = DB::table('give_donormeta')
            ->where('donor_id', $donor->id)
            ->where('meta_key', DonorMetaKeys::FIRST_NAME)
            ->get();

        $this->assertSame($serializedFirstName, $metaValue);
        $this->assertSame(serialize($serializedFirstName), $metaQuery->meta_value);
    }

    /**
     * Test totalAmountDonated method calculates the correct sum of intended donation amounts
     *
     * @since 3.21.0
     * @throws Exception
     */
    public function testTotalAmountDonatedShouldCalculateCorrectSum(): void
    {
        // Create a donor
        $donor = Donor::factory()->create();

        // Create donations with different amounts and fees
        $donation1 = \Give\Donations\Models\Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(10000, 'USD'), // $100.00
            'feeAmountRecovered' => new Money(290, 'USD'), // $2.90
            'status' => DonationStatus::COMPLETE()
        ]);

        $donation2 = \Give\Donations\Models\Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(5000, 'USD'), // $50.00
            'feeAmountRecovered' => new Money(145, 'USD'), // $1.45
            'status' => DonationStatus::COMPLETE()
        ]);

        $donation3 = \Give\Donations\Models\Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(2500, 'USD'), // $25.00
            'feeAmountRecovered' => new Money(0, 'USD'), // No fee
            'status' => DonationStatus::COMPLETE()
        ]);

        // Create a donation with different status (should be ignored)
        \Give\Donations\Models\Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(1000, 'USD'), // $10.00
            'feeAmountRecovered' => new Money(0, 'USD'),
            'status' => DonationStatus::PENDING()
        ]);

        $repository = new DonorRepository();
        $totalIntended = $repository->totalAmountDonated($donor->id);

        // Expected: (100.00 - 2.90) + (50.00 - 1.45) + (25.00 - 0) = 97.10 + 48.55 + 25.00 = 170.65
        // The Money objects store values as decimal amounts, not cents
        $this->assertEqualsWithDelta(170.65, $totalIntended, 0.01); // Allow for floating point precision
    }

    /**
     * Test totalAmountDonated returns zero when donor has no donations
     *
     * @since 3.21.0
     * @throws Exception
     */
    public function testTotalAmountDonatedShouldReturnZeroForDonorWithNoDonations(): void
    {
        $donor = Donor::factory()->create();

        $repository = new DonorRepository();
        $totalIntended = $repository->totalAmountDonated($donor->id);

        $this->assertEquals(0.0, $totalIntended);
    }

    /**
     * Test totalAmountDonated handles null fee amounts correctly
     *
     * @since 3.21.0
     * @throws Exception
     */
    public function testTotalAmountDonatedShouldHandleNullFeeAmounts(): void
    {
        $donor = Donor::factory()->create();

        // Create a donation without fee meta (simulating old donations)
        $donation = \Give\Donations\Models\Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(5000, 'USD'), // $50.00
            'status' => DonationStatus::COMPLETE()
        ]);

        // Remove the fee meta to simulate null/missing fee
        DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', DonationMetaKeys::FEE_AMOUNT_RECOVERED)
            ->delete();

        $repository = new DonorRepository();
        $totalIntended = $repository->totalAmountDonated($donor->id);

        // Should equal the full amount since fee is null (treated as 0)
        $this->assertEquals(50.00, $totalIntended);
    }

    /**
     * Test totalAmountDonated only includes valid donation statuses
     *
     * @since 3.21.0
     * @throws Exception
     */
    public function testTotalAmountDonatedShouldOnlyIncludeValidStatuses(): void
    {
        $donor = Donor::factory()->create();

        // Valid statuses
        \Give\Donations\Models\Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(1000, 'USD'),
            'feeAmountRecovered' => new Money(0, 'USD'),
            'status' => DonationStatus::COMPLETE()
        ]);

        \Give\Donations\Models\Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(2000, 'USD'),
            'feeAmountRecovered' => new Money(0, 'USD'),
            'status' => DonationStatus::RENEWAL()
        ]);

        // Invalid statuses (should be ignored)
        \Give\Donations\Models\Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(5000, 'USD'),
            'feeAmountRecovered' => new Money(0, 'USD'),
            'status' => DonationStatus::PENDING()
        ]);

        \Give\Donations\Models\Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(3000, 'USD'),
            'feeAmountRecovered' => new Money(0, 'USD'),
            'status' => DonationStatus::FAILED()
        ]);

        $repository = new DonorRepository();
        $totalIntended = $repository->totalAmountDonated($donor->id);

        // Should only include 'publish' and 'give_subscription' donations
        $this->assertEquals(30.00, $totalIntended);
    }
}

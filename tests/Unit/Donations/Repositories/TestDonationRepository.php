<?php

namespace Give\Tests\Unit\Donations\Repositories;

use DateTime;
use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @coversDefaultClass DonationRepository
 */
final class TestDonationRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetByIdShouldReturnDonation()
    {
        $donationFactory = Donation::factory()->create();
        $repository = new DonationRepository();

        $donation = $repository->getById($donationFactory->id);

        $this->assertInstanceOf(Donation::class, $donation);
        $this->assertEquals($donationFactory->id, $donation->id);
    }

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldAddDonationToDatabase()
    {
        $donation = new Donation(Donation::factory()->definition());

        $repository = new DonationRepository();

        $repository->insert($donation);

        /** @var Donation $query */
        $query = $repository->getById($donation->id);

        $this->assertEquals($query->getAttributes(), $donation->getAttributes());
    }

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenMissingKeyAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationMissingAmount = new Donation([
            'createdAt' => Temporal::getCurrentDateTime(),
            'status' => DonationStatus::PENDING(),
            'gatewayId' => TestGateway::id(),
            'donorId' => 1,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonationRepository();

        $repository->insert($donationMissingAmount);
    }

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenDonorDoesNotExistAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationWithInvalidDonor = new Donation([
            'createdAt' => Temporal::getCurrentDateTime(),
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'currency' => 'USD',
            'amount' => 50,
            'formId' => 1,
            'donorId' => 2,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonationRepository();

        $repository->insert($donationWithInvalidDonor);
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

        $donationMissingAmount = new Donation([
            'createdAt' => Temporal::getCurrentDateTime(),
            'status' => DonationStatus::PENDING(),
            'gatewayId' => TestGateway::id(),
            'donorId' => 1,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonationRepository();

        $repository->update($donationMissingAmount);
    }

    /**
     * @since 3.2.0 added honorific property
     * @since 2.23.1 add company to test to catch cases where missing meta was not updated
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldUpdateDonationValuesInTheDatabase()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        $repository = new DonationRepository();

        // update donation values
        $donation->amount = new Money(10000, 'USD');
        $donation->honorific = "Mr.";
        $donation->firstName = "Ron";
        $donation->lastName = "Swanson";
        $donation->company = 'Very Good Building';
        $donation->email = "ron@swanson.com";

        // call update method
        $repository->update($donation);

        /** @var object $query */
        $query = $repository->prepareQuery()
            ->where('ID', $donation->id)
            ->get();

        // assert updated values from the database
        $this->assertNotEquals(50, $query->amount);
        $this->assertMoneyEquals(new Money(10000, 'USD'), $query->amount);
        $this->assertSame('Mr.', $query->honorific);
        $this->assertEquals('Ron', $query->firstName);
        $this->assertEquals('Swanson', $query->lastName);
        $this->assertEquals('ron@swanson.com', $query->email);
        $this->assertSame('Very Good Building', $query->company);
    }

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteShouldRemoveDonationFromTheDatabase()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        $repository = new DonationRepository();

        $repository->delete($donation);

        $donationQuery = $repository->prepareQuery()
            ->where('ID', $donation->id)
            ->get();

        $donationCoreMetaQuery =
            DB::table('give_donationmeta')
                ->where('donation_id', $donation->id)
                ->getAll();

        $this->assertNull($donationQuery);
        $this->assertEmpty($donationCoreMetaQuery);
    }

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testFirstDonationLatestDonationSortOrder()
    {
        $this->refreshDatabase();
        Donation::factory()->create(['createdAt' => new DateTime('2022-10-20 00:00:00')]);
        Donation::factory()->create(['createdAt' => new DateTime('2022-10-21 00:00:00')]);

        $repository = new DonationRepository();
        $firstDonationId = $repository->getFirstDonation()->id;
        $lastDonationId = $repository->getLatestDonation()->id;

        $this->assertGreaterThan($firstDonationId, $lastDonationId);
    }

    /**
     * @since 2.25.0
     *
     * @see https://github.com/impress-org/givewp/issues/6654
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateDonationShouldClearPostCache()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::PENDING(),
            'type' => DonationType::SINGLE(),
        ]);

        $donation->status = DonationStatus::COMPLETE();
        $donation->save();

        $donationStatus = $donation->status->getValue();
        $postStatus = get_post_status($donation->id);

        $this->assertEquals($donationStatus, $postStatus);
    }
}

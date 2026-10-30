<?php

namespace Give\Tests\Unit\Donations\Repositories;

use DateTime;
use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Donations\Models\Donation;
use Give\Donations\Properties\BillingAddress;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationMetaKeys;
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

    /**
     * @since 3.20.0
     * @throws \Give\Framework\Exceptions\Primitives\Exception
     */
    public function testInsertShouldSafelyStoreMetaValues(): void
    {
        $name = (object)['first' => 'Jon', 'last' => 'Doe'];

        $serializedFirstName = serialize($name);

        $donation = new Donation(array_merge(Donation::factory()->definition(), [
            'firstName' => $serializedFirstName,
        ]));

        $repository = new DonationRepository();

        $repository->insert($donation);

        $metaValue = give()->payment_meta->get_meta($donation->id, DonationMetaKeys::FIRST_NAME, true);
        $metaQuery = DB::table('give_donationmeta')
            ->where('donation_id', $donation->id)
            ->where('meta_key', DonationMetaKeys::FIRST_NAME)
            ->get();

        $this->assertSame($serializedFirstName, $metaValue);
        $this->assertSame(serialize($serializedFirstName), $metaQuery->meta_value);
    }

    /**
     * @since 4.8.1
     */
    public function testInsertShouldSetCampaignIdIfNull()
    {
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->create([
            'campaignId' => null,
            'formId' => $campaign->defaultFormId,
        ]);

        $repository = new DonationRepository();
        $repository->insert($donation);
        $campaign = give()->campaigns->getByFormId($donation->formId);

        $this->assertNotNull($donation->campaignId);
        $this->assertSame($campaign->id, $donation->campaignId);
    }

    /**
     * @since 4.12.0
     *
     * @throws Exception
     */
    public function testTrashShouldMoveDonationToTrash(): void
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'donorId' => $donor->id,
            'status' => DonationStatus::COMPLETE(),
        ]);

        $repository = new DonationRepository();

        $repository->trash($donation);

        $postStatus = DB::table('posts')
            ->where('ID', $donation->id)
            ->value('post_status');

        $trashMetaStatus = give()->payment_meta->get_meta($donation->id, '_wp_trash_meta_status', true);
        $trashMetaTime = give()->payment_meta->get_meta($donation->id, '_wp_trash_meta_time', true);

        $this->assertEquals('trash', $postStatus);
        $this->assertEquals(DonationStatus::COMPLETE, $trashMetaStatus);
        $this->assertNotEmpty($trashMetaTime);
    }

    /**
     * @since 4.12.0
     *
     * @throws Exception
     */
    public function testUnTrashShouldRestorePreviousStatus(): void
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'donorId' => $donor->id,
            'status' => DonationStatus::PENDING(),
        ]);

        $repository = new DonationRepository();

        // First trash the donation
        $repository->trash($donation);

        // Then untrash it
        $repository->unTrash($donation);

        $postStatus = DB::table('posts')
            ->where('ID', $donation->id)
            ->value('post_status');

        $this->assertEquals(DonationStatus::PENDING, $postStatus);
    }

    /**
     * @since 4.12.0
     *
     * @throws Exception
     */
    public function testUnTrashShouldDefaultToCompleteWhenNoPreviousStatus(): void
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'donorId' => $donor->id,
            'status' => DonationStatus::COMPLETE(),
        ]);

        // Manually set the post status to trash without using the trash method
        // This simulates a donation that was trashed without the proper meta being saved
        DB::table('posts')
            ->where('ID', $donation->id)
            ->update(['post_status' => 'trash']);

        $repository = new DonationRepository();

        $repository->unTrash($donation);

        $postStatus = DB::table('posts')
            ->where('ID', $donation->id)
            ->value('post_status');

        $this->assertEquals(DonationStatus::COMPLETE, $postStatus);
    }

    /**
     * Clearing an optional field on the model must clear it in storage. Before, update() skipped
     * keys the model had no value for, so the old meta row survived and the field came back on
     * the next load.
     *
     * @since TBD
     */
    public function testUpdateRemovesMetaForOptionalFieldsClearedOnTheModel()
    {
        $donation = $this->donationWithEveryOptionalField();
        $this->assertSame(10, $this->optionalMetaCount($donation->id));

        $donation->feeAmountRecovered = null;
        $donation->company = null;
        $donation->honorific = null;
        $donation->comment = null;
        $donation->billingAddress = null;
        $donation->save();

        $this->assertSame(0, $this->optionalMetaCount($donation->id));

        $reloaded = Donation::find($donation->id);
        $this->assertNull($reloaded->feeAmountRecovered);
        $this->assertNull($reloaded->company);
        $this->assertNull($reloaded->comment);
        $this->assertNull($reloaded->honorific);
        $this->assertNull($reloaded->billingAddress->country);
    }

    /**
     * An update that touches something else leaves every optional field exactly as it was.
     *
     * @since TBD
     */
    public function testUpdateKeepsOptionalFieldsThatWereNotCleared()
    {
        $donation = $this->donationWithEveryOptionalField();

        $donation->status = DonationStatus::REFUNDED();
        $donation->save();

        $this->assertSame(10, $this->optionalMetaCount($donation->id));

        $reloaded = Donation::find($donation->id);
        $this->assertSame('refunded', $reloaded->status->getValue());
        $this->assertSame(250, (int)$reloaded->feeAmountRecovered->formatToMinorAmount());
        $this->assertSame('Analytical Engines', $reloaded->company);
        $this->assertSame('Dr', $reloaded->honorific);
        $this->assertSame('Keep it up', $reloaded->comment);
        $this->assertSame('GB', $reloaded->billingAddress->country);
        $this->assertSame('1 Main St', $reloaded->billingAddress->address1);
    }

    /**
     * Only the ten optional keys are ever removed. The keys the engine relies on and the
     * subscription keys are untouched by an update that clears everything optional.
     *
     * @since TBD
     */
    public function testUpdateNeverRemovesRequiredOrSubscriptionMeta()
    {
        $donation = $this->donationWithEveryOptionalField();
        give()->payment_meta->update_meta($donation->id, DonationMetaKeys::SUBSCRIPTION_ID, 42);
        give()->payment_meta->update_meta($donation->id, DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION, 1);
        give()->payment_meta->update_meta($donation->id, DonationMetaKeys::IS_RECURRING, 1);
        $before = $this->metaKeys($donation->id);

        $donation->feeAmountRecovered = null;
        $donation->company = null;
        $donation->honorific = null;
        $donation->comment = null;
        $donation->billingAddress = null;
        $donation->save();

        $removed = array_diff($before, $this->metaKeys($donation->id));
        sort($removed);
        $this->assertSame($this->optionalMetaKeys(), $removed);
    }

    /**
     * Inserting without optional fields writes no meta rows for them, as before.
     *
     * @since TBD
     */
    public function testInsertWritesNoMetaForAbsentOptionalFields()
    {
        $donation = Donation::factory()->create([
            'company' => null,
            'comment' => null,
        ]);

        $this->assertSame(0, $this->optionalMetaCount($donation->id));
        $this->assertNull(Donation::find($donation->id)->feeAmountRecovered);
    }

    /**
     * @since TBD
     */
    private function donationWithEveryOptionalField(): Donation
    {
        return Donation::factory()->create([
            'amount' => new Money(5000, 'USD'),
            'feeAmountRecovered' => new Money(250, 'USD'),
            'company' => 'Analytical Engines',
            'honorific' => 'Dr',
            'comment' => 'Keep it up',
            'billingAddress' => BillingAddress::fromArray([
                'address1' => '1 Main St',
                'address2' => 'Suite 2',
                'city' => 'London',
                'state' => 'LDN',
                'zip' => 'N1',
                'country' => 'GB',
            ]),
        ]);
    }

    /**
     * @since TBD
     */
    private function optionalMetaKeys(): array
    {
        $keys = [
            DonationMetaKeys::FEE_AMOUNT_RECOVERED,
            DonationMetaKeys::COMPANY,
            DonationMetaKeys::COMMENT,
            DonationMetaKeys::HONORIFIC,
            DonationMetaKeys::BILLING_ADDRESS1,
            DonationMetaKeys::BILLING_ADDRESS2,
            DonationMetaKeys::BILLING_CITY,
            DonationMetaKeys::BILLING_STATE,
            DonationMetaKeys::BILLING_ZIP,
            DonationMetaKeys::BILLING_COUNTRY,
        ];
        sort($keys);

        return $keys;
    }

    /**
     * @since TBD
     */
    private function optionalMetaCount(int $donationId): int
    {
        return (int)DB::table('give_donationmeta')
            ->where('donation_id', $donationId)
            ->whereIn('meta_key', $this->optionalMetaKeys())
            ->count();
    }

    /**
     * @since TBD
     */
    private function metaKeys(int $donationId): array
    {
        return DB::get_col(DB::prepare(
            'SELECT meta_key FROM ' . DB::prefix('give_donationmeta') . ' WHERE donation_id = %d',
            $donationId
        ));
    }
}

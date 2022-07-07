<?php

namespace unit\tests\Donations\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give_Subscriptions_DB;

class TestDonation extends \Give_Unit_Test_Case
{

    public function setUp()
    {
        parent::setUp();

        /** @var Give_Subscriptions_DB $legacySubscriptionDb */
        $legacySubscriptionDb = give(Give_Subscriptions_DB::class);

        $legacySubscriptionDb->create_table();
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $donationsTable = DB::prefix('posts');
        $donationMetaTable = DB::prefix('give_donationmeta');
        $donorTable = DB::prefix('give_donors');
        $donorMetaTable = DB::prefix('give_donormeta');
        $subscriptionsTable = DB::prefix('give_subscriptions');
        $sequentialOrderingTable = DB::prefix('give_sequential_ordering');
        $notesTable = DB::prefix('give_comments');

        DB::query("TRUNCATE TABLE $donorTable");
        DB::query("TRUNCATE TABLE $donorMetaTable");
        DB::query("TRUNCATE TABLE $donationMetaTable");
        DB::query("TRUNCATE TABLE $donationsTable");
        DB::query("TRUNCATE TABLE $subscriptionsTable");
        DB::query("TRUNCATE TABLE $sequentialOrderingTable");
        DB::query("TRUNCATE TABLE $notesTable");
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldInsertDonation()
    {
        $donor = Donor::factory()->create();

        /** @var PaymentGatewayRegister $registrar */
        $registrar = give(PaymentGatewayRegister::class);

        if (!$registrar->hasPaymentGateway(TestGateway::id())){
            $registrar->registerGateway(TestGateway::class);
        }

        $donation = Donation::create([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(5000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
            'formId' => 1,
            'levelId' => 'custom',
            'anonymous' => true
        ]);

        $donationFromDatabase = Donation::find($donation->id);

        $this->assertEquals($donation->getAttributes(), $donationFromDatabase->getAttributes());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDonationShouldGetDonor()
    {
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        $this->assertInstanceOf(Donor::class, $donation->donor);
        $this->assertEquals($donor->id, $donation->donor->id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDonationShouldGetSubscription()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create(['donorId' => $donor->id]);

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id, 'subscriptionId' => $subscription->id]);

        $this->assertEquals($donation->subscription->id, $subscription->id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDonationShouldGetSequentialId()
    {
        $donor = Donor::factory()->create();
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        give()->seq_donation_number->__save_donation_title($donation->id, get_post($donation->id), false);

        $this->assertEquals(1, $donation->getSequentialId());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldGetNotes()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        $donationNote1 = DonationNote::factory()->create(['donationId' => $donation->id]);
        $donationNote2 = DonationNote::factory()->create(['donationId' => $donation->id]);
        $donationNote3 = DonationNote::factory()->create(['donationId' => $donation->id]);

        $this->assertCount(3, $donation->notes);

        $this->assertEquals($donationNote1->id, $donation->notes[2]->id);
        $this->assertEquals($donationNote2->id, $donation->notes[1]->id);
        $this->assertEquals($donationNote3->id, $donation->notes[0]->id);
    }

    /**
     * @unreleased
     */
    public function testDonationShouldGetIntendedAmountInBaseCurrency()
    {
        // When a donation has a fee recovery amount
        $donation = Donation::factory()->create([
            'amount' => new Money(5000, 'USD'),
            'feeAmountRecovered' => new Money(500, 'USD'),
            'exchangeRate' => '0.9',
        ]);

        self::assertMoneyEquals(
            $donation->intendedAmount()->inBaseCurrency($donation->exchangeRate),
            $donation->intendedAmountInBaseCurrency()
        );
    }

    /**
     * @unreleased
     */
    public function testDonationShouldReturnAmountInBaseCurrency()
    {
        $donation = Donation::factory()->create([
            'amount' => new Money(5000, 'EUR'),
            'exchangeRate' => '0.9',
        ]);

        self::assertMoneyEquals(
            $donation->amount->inBaseCurrency($donation->exchangeRate),
            $donation->amountInBaseCurrency()
        );
    }

    /**
     * @unreleased
     */
    public function testDonationShouldGetIntendedAmount()
    {
        // When a donation has a fee recovery amount
        $donation = Donation::factory()->create([
            'amount' => new Money(5000, 'USD'),
            'feeAmountRecovered' => new Money(500, 'USD'),
        ]);

        self::assertMoneyEquals(new Money(4500, 'USD'), $donation->intendedAmount());

        // When a donation does not have a fee recovery amount
        $donation = Donation::factory()->create([
            'amount' => new Money(5000, 'USD'),
        ]);

        self::assertMoneyEquals(new Money(5000, 'USD'), $donation->intendedAmount());
    }
}

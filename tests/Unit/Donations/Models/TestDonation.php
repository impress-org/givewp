<?php

namespace Give\Tests\Unit\Donations\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonation extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldInsertDonation()
    {
        $donor = Donor::factory()->create();

        $donation = Donation::create([
            'status' => DonationStatus::PENDING(),
            'type' => DonationType::SINGLE(),
            'gatewayId' => TestGateway::id(),
            'amount' => new Money(5000, 'USD'),
            'donorId' => $donor->id,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
            'formId' => 1,
            'levelId' => 'custom',
            'anonymous' => true,
            'company' => 'GiveWP'
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
        $subscription = Subscription::factory()->createWithDonation(['donorId' => $donor->id]);

        $donation = $subscription->donations[0];

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
     * @since 2.19.6
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
     * @since 2.19.6
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
     * @since 2.19.6
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
     * @since 2.19.6
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

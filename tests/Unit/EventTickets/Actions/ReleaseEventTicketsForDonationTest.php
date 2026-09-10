<?php

namespace Give\Tests\Unit\EventTickets\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\EventTickets\Actions\ReleaseEventTicketsForDonation;
use Give\EventTickets\Models\EventTicket;
use Give\EventTickets\Repositories\EventTicketRepository;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.16.8.1
 */
final class ReleaseEventTicketsForDonationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.16.8.1
     */
    public function testInvokeDeletesTicketsWhenDonationIsCancelled(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::CANCELLED()]);
        EventTicket::factory()->create(['donationId' => $donation->id]);

        (new ReleaseEventTicketsForDonation())($donation);

        $this->assertSame(0, give(EventTicketRepository::class)->queryByDonationId($donation->id)->count());
    }

    /**
     * @since 4.16.8.1
     */
    public function testInvokeDeletesTicketsWhenDonationIsRefunded(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::REFUNDED()]);
        EventTicket::factory()->create(['donationId' => $donation->id]);

        (new ReleaseEventTicketsForDonation())($donation);

        $this->assertSame(0, give(EventTicketRepository::class)->queryByDonationId($donation->id)->count());
    }

    /**
     * @since 4.16.8.1
     */
    public function testInvokeDeletesTicketsWhenDonationIsFailed(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::FAILED()]);
        EventTicket::factory()->create(['donationId' => $donation->id]);

        (new ReleaseEventTicketsForDonation())($donation);

        $this->assertSame(0, give(EventTicketRepository::class)->queryByDonationId($donation->id)->count());
    }

    /**
     * @since 4.16.8.1
     */
    public function testInvokeDeletesTicketsWhenDonationIsAbandoned(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::ABANDONED()]);
        EventTicket::factory()->create(['donationId' => $donation->id]);

        (new ReleaseEventTicketsForDonation())($donation);

        $this->assertSame(0, give(EventTicketRepository::class)->queryByDonationId($donation->id)->count());
    }

    /**
     * @since 4.16.8.1
     */
    public function testInvokeDeletesTicketsWhenDonationIsRevoked(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::REVOKED()]);
        EventTicket::factory()->create(['donationId' => $donation->id]);

        (new ReleaseEventTicketsForDonation())($donation);

        $this->assertSame(0, give(EventTicketRepository::class)->queryByDonationId($donation->id)->count());
    }

    /**
     * @since 4.16.8.1
     */
    public function testInvokeLeavesTicketsAloneWhenDonationIsComplete(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::COMPLETE()]);
        EventTicket::factory()->create(['donationId' => $donation->id]);

        (new ReleaseEventTicketsForDonation())($donation);

        $this->assertSame(1, give(EventTicketRepository::class)->queryByDonationId($donation->id)->count());
    }

    /**
     * @since 4.16.8.1
     */
    public function testInvokeLeavesTicketsAloneWhenDonationIsPending(): void
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING()]);
        EventTicket::factory()->create(['donationId' => $donation->id]);

        (new ReleaseEventTicketsForDonation())($donation);

        $this->assertSame(1, give(EventTicketRepository::class)->queryByDonationId($donation->id)->count());
    }
}

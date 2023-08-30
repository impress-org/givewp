<?php

namespace Give\Tests\Unit\Actions;

use Give\DonationForms\Actions\GenerateDonationConfirmationReceiptUrl;
use Give\Donations\Models\Donation;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class GenerateDonationConfirmationReceiptUrlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();
        $originUrl = 'https://example.com/donation-page';

        $url = (new GenerateDonationConfirmationReceiptUrl())($donation, $originUrl, '123');

        $mockUrl = esc_url_raw(
            add_query_arg(
                [
                    'givewp-event' => 'donation-completed',
                    'givewp-listener' => 'show-donation-confirmation-receipt',
                    'givewp-receipt-id' => $donation->purchaseKey,
                    'givewp-embed-id' => '123',
                ],
                $originUrl
            )
        );

        $this->assertSame($mockUrl, $url);
    }
}

<?php

namespace Give\Tests\Unit\Actions;

use Give\NextGen\DonationForm\Actions\GenerateDonationConfirmationReceiptViewRouteUrl;
use Give\NextGen\Framework\Routes\Route;
use Give\Tests\TestCase;

class GenerateDonationConfirmationReceiptViewRouteUrlTest extends TestCase
{
    /**
     * @since 0.1.0
     *
     * @return void
     */
    public function testShouldReturnValidUrl()
    {
        $url = (new GenerateDonationConfirmationReceiptViewRouteUrl())('receipt-id');

        $mockUrl = esc_url_raw(Route::url('donation-confirmation-receipt-view', ['receipt-id' => 'receipt-id']));

        $this->assertSame($mockUrl, $url);
    }
}

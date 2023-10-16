<?php

namespace Give\Tests\Unit\Actions;

use Give\DonationForms\Actions\GenerateDonationConfirmationReceiptViewRouteUrl;
use Give\Framework\Routes\Route;
use Give\Tests\TestCase;

class GenerateDonationConfirmationReceiptViewRouteUrlTest extends TestCase
{
    /**
     * @since 3.0.0
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

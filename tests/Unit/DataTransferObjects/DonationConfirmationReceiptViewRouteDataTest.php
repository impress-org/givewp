<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Give\DonationForms\DataTransferObjects\DonationConfirmationReceiptViewRouteData;
use Give\Tests\TestCase;

/**
 * @since 0.1.0
 */
class DonationConfirmationReceiptViewRouteDataTest extends TestCase
{
    /**
     * @since 0.1.0
     *
     * @return void
     */
    public function testShouldReturnReceiptId()
    {
        $receiptId = md5('unique-receipt-id');

        $data = DonationConfirmationReceiptViewRouteData::fromRequest([
            'receipt-id' => $receiptId
        ]);

        $this->assertSame($receiptId, $data->receiptId);
    }

    /**
     * @since 0.1.0
     *
     * @return void
     */
    public function testReceiptIdShouldReturnNullIfNotValid()
    {
        $data = DonationConfirmationReceiptViewRouteData::fromRequest([
            'receipt-id' => 'unique-receipt-id'
        ]);

        $this->assertNull($data->receiptId);
    }
}

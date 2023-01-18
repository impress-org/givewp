<?php

namespace Give\Tests\Unit\DataTransferObjects;

use Give\NextGen\DonationForm\DataTransferObjects\DonationConfirmationReceiptViewRouteData;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class DonationConfirmationReceiptViewRouteDataTest extends TestCase
{
    /**
     * @unreleased
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
     * @unreleased
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

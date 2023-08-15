<?php

namespace Give\Framework\Receipts;

use Give\Framework\Receipts\Actions\GenerateConfirmationPageReceipt;

class DonationReceiptBuilder
{
    /**
     * @var DonationReceipt
     */
    public $donationReceipt;

    /**
     * @since 0.1.0
     */
    public function __construct(DonationReceipt $donationReceipt)
    {
        $this->donationReceipt = $donationReceipt;
    }

    /**
     * @since 0.1.0
     */
    public function toConfirmationPage(): DonationReceipt
    {
        return (new GenerateConfirmationPageReceipt())($this->donationReceipt);
    }
}
<?php

namespace Give\NextGen\Framework\Receipts;

use Give\NextGen\Framework\Receipts\Actions\GenerateConfirmationPageReceipt;

class DonationReceiptBuilder
{
    /**
     * @var DonationReceipt
     */
    public $donationReceipt;

    /**
     * @unreleased
     */
    public function __construct(DonationReceipt $donationReceipt)
    {
        $this->donationReceipt = $donationReceipt;
    }

    /**
     * @unreleased
     */
    public function toConfirmationPage(): DonationReceipt
    {
        return (new GenerateConfirmationPageReceipt())($this->donationReceipt);
    }
}
<?php

namespace Give\Framework\Receipts;

use Give\Donations\Models\Donation;
use Give\Framework\Receipts\Properties\ReceiptDetailCollection;
use Give\Framework\Receipts\Properties\ReceiptSettings;
use Give\Framework\Support\Contracts\Arrayable;
use Give\Framework\Support\Contracts\Jsonable;

class DonationReceipt implements Arrayable, Jsonable
{
    /**
     * @var Donation
     */
    public $donation;
    /**
     * @var ReceiptSettings
     */
    public $settings;
    /**
     * @var ReceiptDetailCollection
     */
    public $donorDetails;
    /**
     * @var ReceiptDetailCollection
     */
    public $donationDetails;
    /**
     * @var ReceiptDetailCollection
     */
    public $additionalDetails;
    /**
     * @var ReceiptDetailCollection
     */
    public $subscriptionDetails;
    /**
     * @var ReceiptDetailCollection
     */
    public $eventTicketsDetails;

    /**
     * @since 3.0.0
     */
    public function __construct(Donation $donation)
    {
        $this->donation = $donation;
        $this->settings = new ReceiptSettings();
        $this->donorDetails = new ReceiptDetailCollection();
        $this->donationDetails = new ReceiptDetailCollection();
        $this->eventTicketsDetails = new ReceiptDetailCollection();
        $this->subscriptionDetails = new ReceiptDetailCollection();
        $this->additionalDetails = new ReceiptDetailCollection();
    }


    /**
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return [
            'settings' => $this->settings->toArray(),
            'donorDetails' => $this->donorDetails->toArray(),
            'donationDetails' => $this->donationDetails->toArray(),
            'eventTicketsDetails' => $this->eventTicketsDetails->toArray(),
            'subscriptionDetails' => $this->subscriptionDetails->toArray(),
            'additionalDetails' => $this->additionalDetails->toArray(),
        ];
    }

    /**
     * @since 3.0.0
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}

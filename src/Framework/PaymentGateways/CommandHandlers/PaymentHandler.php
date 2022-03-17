<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\PaymentCommand;

abstract class PaymentHandler
{
    /**
     * @var PaymentCommand
     */
    protected $paymentCommand;

    /**
     * @since 2.18.0
     * @return string
     */
    abstract protected function getPaymentStatus();

    /**
     * @param PaymentCommand $paymentCommand
     */
    public function __construct(PaymentCommand $paymentCommand)
    {
        $this->paymentCommand = $paymentCommand;
    }

    /**
     * @param PaymentCommand $paymentCommand
     * @return static
     */
    public static function make(PaymentCommand $paymentCommand)
    {
        return new static($paymentCommand);
    }

    /**
     * @unreleased replace $donationId with Donation model
     * @since 2.18.0
     *
     * @param  Donation  $donation
     * @return void
     * @throws Exception
     */
    public function handle(Donation $donation)
    {
        $status = new DonationStatus($this->getPaymentStatus());
        
        $donation->status = $status;
        $donation->gatewayTransactionId = $this->paymentCommand->gatewayTransactionId;
        $donation->save();

        foreach ($this->paymentCommand->paymentNotes as $paymentNote) {
            give_insert_payment_note($donation->id, $paymentNote);
        }
    }
}

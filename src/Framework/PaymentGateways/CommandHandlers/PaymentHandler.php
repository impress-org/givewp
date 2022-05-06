<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\PaymentCommand;

abstract class PaymentHandler
{
    /**
     * @var PaymentCommand
     */
    protected $paymentCommand;

    /**
     * @unreleased change return type to DonationStatus
     * @since 2.18.0
     */
    abstract protected function getPaymentStatus(): DonationStatus;

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
        $donation->status = $this->getPaymentStatus();
        $donation->gatewayTransactionId = $this->paymentCommand->gatewayTransactionId;
        $donation->save();

        foreach ($this->paymentCommand->paymentNotes as $paymentNote) {
            DonationNote::create([
                'donationId' => $donation->id,
                'content' => $paymentNote
            ]);
        }
    }
}

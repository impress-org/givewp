<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\PaymentCommand;

abstract class PaymentHandler
{
    /**
     * @var PaymentCommand
     */
    protected $paymentCommand;

    /**
     * @since 2.21.0 change return type to DonationStatus
     *
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
     * @since 2.18.0
     */
    public static function make(PaymentCommand $paymentCommand): PaymentHandler
    {
        return new static($paymentCommand);
    }

    /**
     * @since 2.21.0 replace $donationId with Donation model
     * @since 2.18.0
     *
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

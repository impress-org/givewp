<?php
namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Decorators;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Stripe\Invoice;

class SubscriptionModelDecorator {
    /**
     * @var Subscription
     */
    public $subscription;

    /**
     * @since 3.0.0
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * @since 3.0.0
     */
    public function shouldEndSubscription(): bool
    {
        return $this->subscription->installments !== 0 && (count(
                    $this->subscription->donations
                ) >= $this->subscription->installments);
    }

    /**
     * @since 3.0.0
     */
    public function shouldCreateRenewal(): bool
    {
        $billTimes = $this->subscription->installments;
        $totalPayments = count($this->subscription->donations);

        return $this->subscription->status->isActive() && (0 === $billTimes || $totalPayments < $billTimes);
    }

    /**
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function handleRenewal(Invoice $invoice): SubscriptionModelDecorator
    {
        $initialDonation = $this->subscription->initialDonation();

        // create renewal
        Donation::create([
            'amount' => Money::fromDecimal(
                give_stripe_cents_to_dollars($invoice->total),
                strtoupper($invoice->currency)
            ),
            /**
             * TODO: the payment_intent.succeeded event is going to try setting this status as complete
             */
            'type' => DonationType::RENEWAL(),
            'status' => DonationStatus::COMPLETE(),
            'createdAt' => Temporal::toDateTime(date_i18n('Y-m-d H:i:s', $invoice->created)),
            'gatewayTransactionId' => $invoice->payment_intent,
            'subscriptionId' => $this->subscription->id,
            'gatewayId' => $this->subscription->gatewayId,
            'donorId' => $this->subscription->donorId,
            'formId' => $this->subscription->donationFormId,
            /**
             * TODO: these details might need to come from $invoice object
             * It appears we do not store this on the subscription
             * so otherwise would have to reach back to the initial donation to find out (which is how legacy works).
             */
            'firstName' => $initialDonation->firstName,
            'lastName' => $initialDonation->lastName,
            'email' => $initialDonation->email,
        ]);

        $this->subscription->bumpRenewalDate();
        $this->subscription->save();

        $subscription = Subscription::find($this->subscription->id);

        // return a refreshed subscription model to ensure we have the latest data
        return new SubscriptionModelDecorator($subscription);
    }

    /**
     * @since 3.0.0
     * @throws Exception
     */
    public function cancelSubscription()
    {
        $this->subscription->cancel();
    }

    /**
     * @since 3.0.0
     * @throws Exception
     */
    public function handleSubscriptionCompleted()
    {
        $this->subscription->status = SubscriptionStatus::COMPLETED();
        $this->subscription->save();
    }
}
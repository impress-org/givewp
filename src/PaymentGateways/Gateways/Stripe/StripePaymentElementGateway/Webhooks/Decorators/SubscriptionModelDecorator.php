<?php
namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Decorators;

use Exception;
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
     * @unreleased updated to use model method
     * @since 3.0.0
     */
    public function shouldEndSubscription(): bool
    {
        return $this->subscription->shouldEndSubscription();
    }

    /**
     * @unreleased updated to use model method
     * @since 3.0.0
     */
    public function shouldCreateRenewal(): bool
    {
        return $this->subscription->shouldCreateRenewal();
    }

    /**
     * @unreleased updated to use model method
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function handleRenewal(Invoice $invoice): SubscriptionModelDecorator
    {
        $this->subscription->createRenewal([
            'amount' => Money::fromDecimal(
                give_stripe_cents_to_dollars($invoice->total),
                strtoupper($invoice->currency)
            ),
            'createdAt' => Temporal::toDateTime(date_i18n('Y-m-d H:i:s', $invoice->created)),
            'gatewayTransactionId' => $invoice->payment_intent,
        ]);

        // refresh the subscription model
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

<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RespondToBrowser;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionAmountEditable;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionDashboardLinkable;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\DataTransferObjects\StripeGatewayData;
use Give\PaymentGateways\Gateways\Stripe\Traits\CanSetupStripeApp;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use GiveRecurring\Infrastructure\Exceptions\PaymentGateways\Stripe\UnableToCreateStripePlan;
use GiveRecurring\PaymentGateways\DataTransferObjects\SubscriptionDto;
use GiveRecurring\PaymentGateways\Stripe\Actions\RetrieveOrCreatePlan;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentMethod;
use Stripe\Plan;
use Stripe\Subscription as StripeSubscription;

/**
 * @since 0.3.0
 */
class StripePaymentElementGatewaySubscriptionModule extends SubscriptionModule implements SubscriptionDashboardLinkable,
                                                                                          SubscriptionAmountEditable
{
    use CanSetupStripeApp;
    use StripePaymentElementRepository;

    /**
     * @since 0.3.0
     *
     * @throws Exception
     * @throws ApiErrorException
     * @throws \Exception
     */
    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData
    ): GatewayCommand {
        /**
         * Initialize the Stripe SDK using Stripe::setAppInfo()
         */
        $this->setUpStripeAppInfo($donation->formId);

        /**
         * Get data from client request
         */
        $stripeGatewayData = StripeGatewayData::fromRequest($gatewayData);

        /**
         * Get or create a Stripe customer
         */
        $customer = $this->getOrCreateStripeCustomerFromDonation(
            $stripeGatewayData->stripeConnectedAccountId,
            $donation
        );

        /**
         * Setup Stripe Plan
         */
        $plan = $this->createStripePlan($subscription);

        /**
         * Create Stripe Subscription
         */
        $stripeSubscription = $this->createStripeSubscription(
            $donation,
            $subscription,
            $customer,
            $plan
        );

        /**
         * Update Subscription Meta
         */
        $this->updateSubscriptionMetaFromStripeSubscription(
            $subscription,
            $stripeSubscription
        );

        /**
         * Update Initial Donation Meta
         */
        $this->updateSubscriptionInitialDonationMetaFromStripeSubscription(
            $stripeSubscription,
            $donation
        );

        /**
         * Return response to client.
         * 'clientSecret' is required to confirm payment intent on client side.
         * 'returnUrl' is required to redirect user to success page.
         */
        return new RespondToBrowser([
            'clientSecret' => $stripeSubscription->latest_invoice->payment_intent->client_secret,
            'returnUrl' => $stripeGatewayData->successUrl,
            'billingDetails' => [
                'name' => trim("$donation->firstName $donation->lastName"),
                'email' => $donation->email
            ],
        ]);
    }

    /**
     * @since 0.3.0
     *
     * @inerhitDoc
     * @throws PaymentGatewayException
     */
    public function cancelSubscription(Subscription $subscription)
    {
        try {
            $this->setupStripeApp($subscription->donationFormId);

            $stripeSubscription = StripeSubscription::retrieve($subscription->gatewaySubscriptionId);

            $stripeSubscription->cancel();

            $subscription->status = SubscriptionStatus::CANCELLED();
            $subscription->save();
        } catch (\Exception $exception) {
            throw new PaymentGatewayException(
                sprintf(
                    'Unable to cancel subscription with Stripe. %s',
                    $exception->getMessage()
                ),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @since 0.3.0
     *
     * @throws UnableToCreateStripePlan
     */
    protected function createStripePlan(Subscription $subscription): Plan
    {
        $donation = $subscription->initialDonation();
        /**
         * Legacy gateways use the levelId for the subscription name.  We are not really doing that anymore in next gen.
         * So, we can just add the amount to the subscription name.  Keeping this filter here for now since this part is preserving our logic
         * used in legacy Stripe gateways but will eventually be refactored to its own functionality.
         */
        add_filter(
            'give_recurring_subscription_name',
            static function ($subscriptionName) use ($donation, $subscription) {
                if ($donation->levelId) {
                    return $subscriptionName;
                }

                return sprintf(
                    '%1$s - %2$s',
                    $subscriptionName,
                    $subscription->amount->formatToDecimal()
                );
            }
        );

        return give(RetrieveOrCreatePlan::class)->handle(
            SubscriptionDto::fromArray(
                [
                    'formId' => $subscription->donationFormId,
                    'priceId' => $donation->levelId,
                    'recurringDonationAmount' => $subscription->amount,
                    'period' => $subscription->period->getValue(),
                    'frequency' => $subscription->frequency,
                    'currencyCode' => $subscription->amount->getCurrency(),
                ]
            )
        );
    }

    /**
     * @since 0.3.0
     *
     * @throws Exception
     * @throws \Exception
     */
    protected function createStripeSubscription(
        Donation $donation,
        Subscription $subscription,
        Customer $customer,
        Plan $plan
    ): StripeSubscription {
        /**
         * @see https://stripe.com/docs/api/subscriptions/create
         *
         * Note: we do not add the application_fee_percent for subscriptions in favor of using our premium add-on give-recurring.
         * @see https://givewp.com/documentation/core/payment-gateways/stripe-free/
         */
        $subscriptionArgs = [
            'items' => [
                [
                    'plan' => $plan->id,
                ]
            ],
            'metadata' => give_stripe_prepare_metadata($donation->id),
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.payment_intent'],
        ];

        /**
         * @var StripeSubscription $stripeSubscription
         */
        $stripeSubscription = $customer->subscriptions->create($subscriptionArgs);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(
            /* translators: 1. Stripe payment intent id */
                esc_html__('Stripe Payment Invoice ID: %1$s', 'give'),
                $stripeSubscription->latest_invoice->id
            )
        ]);

        return $stripeSubscription;
    }

    /**
     * @since 0.3.0
     * @throws \Exception
     */
    protected function updateSubscriptionMetaFromStripeSubscription(
        Subscription $subscription,
        StripeSubscription $stripeSubscription
    ) {
        if ($stripeTransactionId = $stripeSubscription->latest_invoice->payment_intent->id) {
            $subscription->transactionId = $stripeTransactionId;
        }

        $subscription->gatewaySubscriptionId = $stripeSubscription->id;

        $subscription->status = SubscriptionStatus::ACTIVE();
        $subscription->save();
    }

    /**
     * @since 0.3.0
     *
     * @return void
     * @throws Exception
     */
    protected function updateSubscriptionInitialDonationMetaFromStripeSubscription(
        StripeSubscription $stripeSubscription,
        Donation $donation
    ) {
        $paymentIntentId = $stripeSubscription->latest_invoice->payment_intent->id;
        $clientSecret = $stripeSubscription->latest_invoice->payment_intent->client_secret;

        $donation->status = DonationStatus::PROCESSING();
        $donation->gatewayTransactionId = $paymentIntentId;
        $donation->save();

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(
                __('Stripe Charge/Payment Intent ID: %s', 'give'),
                $paymentIntentId
            )
        ]);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(
                __('Stripe Payment Intent Client Secret: %s', 'give'),
                $clientSecret
            )
        ]);

        give_update_meta(
            $donation->id,
            '_give_stripe_payment_intent_client_secret',
            $clientSecret
        );
    }

    /**
     * @since 0.3.0
     *
     * TODO: This is the start to implementing SubscriptionPaymentMethodEditable but there needs to be a donor dashboard counterpart to this in GiveWP core to work.
     * TODO: This would actually need to use the Payment Element because the payment methods are dynamic.
     *
     * @param  Subscription  $subscription
     * @param  array|null  $gatewayData
     * @return void
     * @throws PaymentGatewayException
     */
    public function updateSubscriptionPaymentMethod(Subscription $subscription, $gatewayData)
    {
        if (!isset($gatewayData['give_stripe_payment_method'])) {
            return;
        }

        $this->setupStripeApp($subscription->donationFormId);

        try {
            $stripePaymentMethod = PaymentMethod::retrieve($gatewayData['give_stripe_payment_method']);
            $initialSubscriptionDonation = $subscription->initialDonation();
            $stripeSubscription = StripeSubscription::retrieve($subscription->gatewaySubscriptionId);
            $stripeConnectedAccountKey = $this->getStripeConnectedAccountKey($subscription->donationFormId);

            /**
             * Get or create a Stripe customer
             */
            $customer = $this->getOrCreateStripeCustomerFromDonation(
                $stripeConnectedAccountKey,
                $initialSubscriptionDonation
            );

            if ($stripeSubscription->customer === $customer->id) {
                $stripePaymentMethod->attach(['customer' => $customer->id]);
                /**
                 * @see https://stripe.com/docs/api/customers/update#update_customer-invoice_settings
                 */
                $customer->invoice_settings->default_payment_method = $stripePaymentMethod->id;
                $customer->save();
            }

            StripeSubscription::update(
                $stripeSubscription->id,
                ['default_payment_method' => $customer->invoice_settings->default_payment_method]
            );
        } catch (\Exception $e) {
            throw new PaymentGatewayException($e->getMessage());
        }
    }

    /**
     * @since 0.3.0
     *
     * @inheritDoc
     * @throws PaymentGatewayException
     */
    public function updateSubscriptionAmount(Subscription $subscription, Money $newRenewalAmount)
    {
        try {
            $this->setupStripeApp($subscription->donationFormId);

            $subscription->amount = $newRenewalAmount;

            $plan = $this->createStripePlan($subscription);

            $stripeSubscription = StripeSubscription::retrieve($subscription->gatewaySubscriptionId);

            StripeSubscription::update(
                $stripeSubscription->id,
                [
                    'items' => [
                        [
                            'id' => $stripeSubscription->items->data[0]->id,
                            'plan' => $plan->id,
                        ],
                    ],
                    'prorate' => false,
                ]
            );

            $subscription->save();
        } catch (\Exception $e) {
            throw new PaymentGatewayException("Unable to update Stripe Subscription Amount.");
        }
    }

    /**
     * @since 0.3.0
     */
    public function gatewayDashboardSubscriptionUrl(Subscription $subscription): string
    {
        $stripeDashboardUrl = $subscription->mode->equals(SubscriptionMode::LIVE()) ?
            'https://dashboard.stripe.com/' :
            'https://dashboard.stripe.com/test/';

        return esc_url("{$stripeDashboardUrl}subscriptions/$subscription->gatewaySubscriptionId");
    }
}

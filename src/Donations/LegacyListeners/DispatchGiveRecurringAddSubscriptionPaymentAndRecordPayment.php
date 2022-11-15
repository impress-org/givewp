<?php

namespace Give\Donations\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Helpers\Hooks;
use Give_Payment;
use Give_Subscription;

class DispatchGiveRecurringAddSubscriptionPaymentAndRecordPayment
{
    /**
     * This function triggers legacy subscription renewal donation action hooks.
     *
     * @since 2.23.0 remove use of Donation::parentId
     * @since 2.19.6
     *
     * @param  Donation  $donation
     * @return void
     */
    public function __invoke(Donation $donation)
    {
        $subscription = new Give_Subscription($donation->subscriptionId);
        $payment = new Give_Payment($donation->id);
        $parent = new Give_Payment(give()->subscriptions->getInitialDonationId($donation->subscriptionId));

        $payment->parent_payment = $subscription->parent_payment_id;
        $payment->total = (float) $donation->amount->formatToDecimal();
        $payment->form_title = $donation->formTitle;
        $payment->form_id = $donation->formId;
        $payment->customer_id = $donation->donorId;
        $payment->address = $parent->address;
        $payment->first_name = $donation->firstName;
        $payment->last_name = $donation->lastName;
        $payment->user_info = $parent->user_info;
        $payment->user_id = $parent->user_id;
        $payment->email = $parent->email;
        $payment->currency = $parent->currency;
        $payment->status = 'give_subscription';
        $payment->transaction_id = $donation->gatewayTransactionId;
        $payment->key = $parent->key;
        $payment->mode = $parent->mode;

        Hooks::doAction('give_recurring_add_subscription_payment', $payment, $subscription);

        Hooks::doAction(
            'give_recurring_record_payment',
            $payment,
            $subscription->parent_payment_id,
            $payment->total,
            $donation->gatewayTransactionId
        );
    }
}

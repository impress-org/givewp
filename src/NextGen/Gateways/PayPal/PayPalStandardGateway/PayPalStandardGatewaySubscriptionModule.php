<?php

namespace Give\NextGen\Gateways\PayPal\PayPalStandardGateway;

use Exception;
use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Contracts\Subscription\SubscriptionDashboardLinkable;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\NextGen\Gateways\PayPal\PayPalStandardGateway\Actions\CancelPayPalStandardSubscription;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class PayPalStandardGatewaySubscriptionModule extends SubscriptionModule implements SubscriptionDashboardLinkable
{
    /**
     * @unreleased
     */
    public function createSubscription(
        Donation $donation,
        Subscription $subscription,
        $gatewayData
    ) {
        $invoiceIdPrefix = $this->getInvoiceIdPrefix();
        /**
         * Add additional query args to PayPal redirect URLs.
         * This does not affect the core PayPal Standard gateway functionality.
         * Later in our routeMethods, there are conditionals to check for these query args
         * and proceed accordingly if they exist or not making this gateway backwards compatible with legacy forms.
         *
         * @see https://developer.paypal.com/api/nvp-soap/paypal-payments-standard/integration-guide/Appx-websitestandard-htmlvariables/#auto-fill-paypal-checkout-page-variables
         */
        add_filter(
            'give_gateway_paypal_redirect_args',
            static function ($paypalPaymentArguments) use ($gatewayData, $donation, $subscription, $invoiceIdPrefix) {
                /**
                 * PayPal Docs:
                 * The URL to which PayPal redirects buyers' browser after they complete their payments. For example, specify a URL on your site that displays a thank you for your payment page.
                 *
                 * By default, PayPal redirects the browser to a PayPal webpage.
                 *
                 * Character Length: 1,024
                 */
                $paypalPaymentArguments['return'] = add_query_arg(
                    ['givewp-return-url' => $gatewayData['successUrl']],
                    $paypalPaymentArguments['return']
                );

                /**
                 * PayPal Docs:
                 *
                 * A URL to which PayPal redirects the buyers' browsers if they cancel checkout before completing their payments. For example, specify a URL on your website that displays the Payment Canceled page.
                 *
                 * By default, PayPal redirects the browser to a PayPal webpage.
                 *
                 * Character Length: 1,024
                 */
                $paypalPaymentArguments['cancel_return'] = add_query_arg(
                    ['givewp-return-url' => $gatewayData['cancelUrl']],
                    $paypalPaymentArguments['cancel_return']
                );

                /**
                 * PayPal Docs:
                 *
                 * Recurring payments. Subscription payments recur unless subscribers cancel their subscriptions before the end of the current billing cycle or you limit the number of times that payments recur with the value that you specify for srt.
                 *
                 * Valid value is:
                 *
                 * 0. Subscription payments do not recur.
                 * 1. Subscription payments recur.
                 * Default is 0.
                 */
                $paypalPaymentArguments['src'] = "1";
                /**
                 * PayPal Docs:
                 * Reattempt on failure. If a recurring payment fails, PayPal attempts to collect the payment two more times before canceling the subscription.
                 *
                 * Valid value is:
                 *
                 * 0. Do not reattempt failed recurring payments.
                 * 1. Reattempt failed recurring payments before canceling.
                 * Default is 1
                 */
                $paypalPaymentArguments['sra'] = "1";

                /**
                 * PayPal Docs:
                 *
                 * Return method. The FORM METHOD used to send data to the URL specified by the return variable.
                 *
                 * Valid value is:
                 *
                 * 0. All shopping cart payments use the GET method.
                 * 1. The buyer's browser is redirected to the return URL by using the GET method, but no payment variables are included.
                 * 2. The buyer's browser is redirected to the return URL by using the POST method, and all payment variables are included.
                 * Default is 0.
                 */
                $paypalPaymentArguments['rm'] = 2;

                // PayPal Docs: The button that the person clicked was a Subscribe button.
                $paypalPaymentArguments['cmd'] = '_xclick-subscriptions';

                // PayPal Docs: Regular subscription price.
                $paypalPaymentArguments['a3'] = $subscription->amount->formatToDecimal();

                // PayPal Docs: Description of item. If you omit this variable, buyers enter their own name during checkout. Optional for Buy Now, Donate, Subscribe, Automatic Billing, and Add to Cart buttons Character length: 127
                $paypalPaymentArguments['item_name'] = sprintf(
                    '%1$s - %2$s',
                    $donation->formTitle,
                    $subscription->amount->formatToDecimal()
                );

                /**
                 * PayPal Docs:
                 * Regular subscription units of duration.
                 *
                 * Valid value is:
                 * D. Days. Valid range for p3 is 1 to 90.
                 * W. Weeks. Valid range for p3 is 1 to 52.
                 * M. Months. Valid range for p3 is 1 to 24.
                 * Y. Years. Valid range for p3 is 1 to 5.
                 * Character Length: 1
                 */
                switch ($subscription->period->getValue()) {
                    case 'day' :
                        $paypalPaymentArguments['t3'] = 'D';
                        break;
                    case 'week' :
                        $paypalPaymentArguments['t3'] = 'W';
                        break;
                    case 'month' :
                        $paypalPaymentArguments['t3'] = 'M';
                        break;
                    case 'year' :
                        $paypalPaymentArguments['t3'] = 'Y';
                        break;
                }

                /**
                 * PayPal Docs:
                 *
                 * Trial period 1 duration. Required if you specify a1. Specify an integer value in the valid range for the units of duration that you specify with t1.
                 * Character Length: 2
                 */
                $paypalPaymentArguments['p1'] = $subscription->frequency;
                /**
                 * PayPal Docs:
                 *
                 * Subscription duration. Specify an integer value in the Valid range for the units of duration that you specify with t3.
                 * Character Length: 2
                 */
                $paypalPaymentArguments['p3'] = $subscription->frequency;

                if ($subscription->installments > 1) {
                    // Make sure it's not over the max of 52
                    $installments = $subscription->installments <= 52 ? $subscription->installments : 52;

                    // PayPal Docs: Recurring times. Number of times that subscription payments recur. Specify an integer with a minimum value of 2 and a maximum value of 52. Valid only if you specify src="1". Character Length: 1
                    $paypalPaymentArguments['srt'] = $installments;
                }


                /**
                 * PayPal Docs:
                 *
                 * The currency of the payment. Default is USD.
                 */
                $paypalPaymentArguments['currency_code'] = $subscription->amount->getCurrency()->getCode();

                if (!empty($invoiceIdPrefix)) {
                    /**
                     * PayPal Docs:
                     *
                     * Pass-through variable you can use to identify your invoice number for this purchase.
                     *
                     * By default, no variable is passed back to you.
                     *
                     * Character Length: 127
                     */
                    $paypalPaymentArguments['invoice'] = trim($invoiceIdPrefix) . $donation->purchaseKey;
                }

                return $paypalPaymentArguments;
            }
        );

        // TODO: Preserving this Legacy functionality?
        // Taken from give-recurring-paypal->create_payment_profile().
        // "This is a temporary ID used to look it up later during IPN processing"
        $subscription->gatewaySubscriptionId = 'paypal-' . trim($invoiceIdPrefix) . $donation->purchaseKey;
        $subscription->save();

        // Re-use the PayPal Standard gateway create payment method to build the args and redirect to PayPal..
        return give(PayPalStandardGateway::class)->createPayment($donation, $gatewayData);
    }

    /**
     * @unreleased
     * @throws PaymentGatewayException
     */
    public function cancelSubscription(Subscription $subscription)
    {
        try {
            (new CancelPayPalStandardSubscription())($subscription);

            $subscription->status = SubscriptionStatus::CANCELLED();
            $subscription->save();
        } catch (Exception $e) {
            throw new PaymentGatewayException($e->getMessage());
        }
    }

    /**
     * @unreleased
     */
    public function gatewayDashboardSubscriptionUrl(Subscription $subscription): string
    {
        $baseUrl = $subscription->mode->getValue() === 'live' ? 'https://www.paypal.com' : 'https://www.sandbox.paypal.com';

        $gatewaySubscriptionId = !str_contains(
            $subscription->gatewaySubscriptionId,
            'paypal-'
        ) ? $subscription->gatewaySubscriptionId : null;

        if (!$gatewaySubscriptionId) {
            return "{$baseUrl}/cgi-bin/webscr?cmd=_profile-recurring-payments";
        }

        return esc_url(
            "{$baseUrl}/cgi-bin/webscr?cmd=_profile-recurring-payments&encrypted_profile_id={$gatewaySubscriptionId}"
        );
    }

    /**
     * @unreleased
     */
    protected function getInvoiceIdPrefix()
    {
        return give_get_option('paypal_invoice_prefix', 'GIVE-');
    }
}

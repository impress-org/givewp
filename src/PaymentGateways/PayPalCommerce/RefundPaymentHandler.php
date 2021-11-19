<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Exception;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;

/**
 * Class RefundPaymentHandler
 *
 * @since 2.9.0
 */
class RefundPaymentHandler
{
    /**
     * @since 2.9.0
     *
     * @var PayPalOrder
     */
    private $ordersRepository;

    /**
     * RefundPaymentHandler constructor.
     *
     * @since 2.9.0
     *
     * @param PayPalOrder $ordersRepository
     */
    public function __construct(PayPalOrder $ordersRepository)
    {
        $this->ordersRepository = $ordersRepository;
    }

    /**
     * Refunds the payment when the donation is marked as refunded
     *
     * @since 2.9.0
     *
     * @param int $donationId
     *
     * @throws Exception
     */
    public function refundPayment($donationId)
    {
        if ( ! $this->isAdminOptInToRefundPaymentOnPayPal()) {
            return;
        }

        $payPalPaymentId = give_get_payment_transaction_id($donationId);
        $paymentGateway = give_get_payment_gateway($donationId);
        $newDonationStatus = give_clean($_POST['give-payment-status']);

        if ('refunded' !== $newDonationStatus || PayPalCommerce::GATEWAY_ID !== $paymentGateway) {
            return;
        }

        try {
            $this->ordersRepository->refundPayment($payPalPaymentId);
        } catch (Exception $ex) {
            wp_safe_redirect(
                admin_url(
                    "edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id={$donationId}&paypal-error=refund-failure"
                )
            );
            exit();
        }
    }

    /**
     * show Paypal Commerce payment refund failure notice.
     *
     * @since 2.9.0
     */
    public function showPaymentRefundFailureNotice()
    {
        if ( ! isset($_GET['paypal-error']) || 'refund-failure' !== $_GET['paypal-error']) {
            return;
        }

        give('notices')->register_notice(
            [
                'id' => 'give-paypal-commerce-refund-failure',
                'type' => 'warning',
                'show' => true,
                'description' => sprintf(
                    '<strong>%1$s</strong>&nbsp;%2$s&nbsp;%3$s<a href="%4$s" target="_blank">%5$s</a>%6$s',
                    esc_html__('PayPal Donations:', 'give'),
                    esc_html__('We were unable to process refund.', 'give'),
                    esc_html__('Please ', 'give'),
                    admin_url('edit.php?post_type=give_forms&page=give-tools&tab=logs'),
                    esc_html__('check log', 'give'),
                    esc_html__(' for detailed information.', 'give')
                ),
            ]
        );
    }

    /**
     * This function will display field to opt for refund.
     *
     * @since 2.5.0
     *
     * @param int $donationId Donation ID.
     *
     * @return void
     */
    public function optInForRefundFormField($donationId)
    {
        if (PayPalCommerce::GATEWAY_ID !== give_get_payment_gateway($donationId)) {
            return;
        }

        ?>
        <div id="give-paypal-commerce-opt-refund-wrap"
             class="give-paypal-commerce-opt-refund give-admin-box-inside give-hidden">
            <p>
                <input type="checkbox" id="give-paypal-commerce-opt-refund"
                       name="give_paypal_donations_optin_for_refund" value="1" />
                <label for="give-paypal-commerce-opt-refund">
                    <?php
                    esc_html_e('Refund Charge in PayPal?', 'give'); ?>
                </label>
            </p>
        </div>

        <?php
    }

    /**
     * Return whether or not admin optin for refund payment in PayPal
     *
     * @since 2.9.0
     *
     * @return bool
     */
    private function isAdminOptInToRefundPaymentOnPayPal()
    {
        return ! empty($_POST['give_paypal_donations_optin_for_refund']) ?
            (bool)absint($_POST['give_paypal_donations_optin_for_refund'])
            : false;
    }
}

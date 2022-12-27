<?php

namespace Give\Framework\LegacyPaymentGateways\Adapters;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayAdapter;

class LegacyPaymentGatewayRegisterAdapter
{
    /**
     * Run the necessary legacy hooks on our LegacyPaymentGatewayAdapter
     * that prepares data to be sent to each gateway
     *
     * @since 2.19.0
     *
     * @param string $gatewayClass
     */
    public function connectGatewayToLegacyPaymentGatewayAdapter($gatewayClass)
    {
        /** @var LegacyPaymentGatewayAdapter $legacyPaymentGatewayAdapter */
        $legacyPaymentGatewayAdapter = give(LegacyPaymentGatewayAdapter::class);

        /** @var PaymentGatewayInterface $registeredGateway */
        $registeredGateway = give($gatewayClass);
        $registeredGatewayId = $registeredGateway::id();

        add_action(
            "give_{$registeredGatewayId}_cc_form",
            static function ($formId, $args) use ($registeredGateway, $legacyPaymentGatewayAdapter) {
                echo $legacyPaymentGatewayAdapter->getLegacyFormFieldMarkup($formId, $args, $registeredGateway);
            },
            10,
            2
        );

        add_action(
            "give_gateway_{$registeredGatewayId}",
            static function ($legacyPaymentData) use ($registeredGateway, $legacyPaymentGatewayAdapter) {
                $legacyPaymentGatewayAdapter->handleBeforeGateway(give_clean($legacyPaymentData), $registeredGateway);
            }
        );

        /**
         * Remove all refund checkboxes added by the gateways with priority 11 and add a new refund checkbox with priority 12
         */
        remove_all_actions("give_view_donation_details_totals_after", 11);
        add_action(
            "give_view_donation_details_totals_after",
            static function (int $donationId) use ($registeredGateway, $legacyPaymentGatewayAdapter) {
                $legacyPaymentGatewayAdapter->addOptRefundCheckbox($donationId, $registeredGateway);
            },
            12
        );
        add_action(
            "give_update_payment_status",
            static function (int $donationId, string $newStatus, string $oldStatus) use (
                $registeredGateway,
                $legacyPaymentGatewayAdapter
            ) {
                $legacyPaymentGatewayAdapter->maybeRefundOnGateway($donationId, $newStatus, $oldStatus,
                    $registeredGateway);
            },
            12,
            3
        );
    }

    /**
     * Adds new payment gateways to legacy list for settings
     *
     * @since 2.19.0
     *
     * @param array $gatewaysData
     * @param array $newPaymentGateways
     *
     * @return array
     */
    public function addNewPaymentGatewaysToLegacyListSettings($gatewaysData, $newPaymentGateways)
    {
        foreach ($newPaymentGateways as $gatewayClassName) {
            /* @var PaymentGatewayInterface $paymentGateway */
            $paymentGateway = give($gatewayClassName);

            $gatewaysData[$paymentGateway::id()] = [
                'admin_label' => $paymentGateway->getName(),
                'checkout_label' => $paymentGateway->getPaymentMethodLabel(),
            ];
        }

        return $gatewaysData;
    }
}

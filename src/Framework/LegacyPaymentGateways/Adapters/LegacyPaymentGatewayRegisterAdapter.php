<?php

namespace Give\Framework\LegacyPaymentGateways\Adapters;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayAdapter;

use function method_exists;

class LegacyPaymentGatewayRegisterAdapter
{
    /**
     * Run the necessary legacy hooks on our LegacyPaymentGatewayAdapter
     * that prepares data to be sent to each gateway
     *
     * @since 2.19.0
     */
    public function connectGatewayToLegacyPaymentGatewayAdapter(string $gatewayClass)
    {
        /** @var LegacyPaymentGatewayAdapter $legacyPaymentGatewayAdapter */
        $legacyPaymentGatewayAdapter = give(LegacyPaymentGatewayAdapter::class);

        /** @var PaymentGateway $registeredGateway */
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

        if ($registeredGateway->supportsRefund()) {
            add_action(
                "give_view_donation_details_totals_after",
                static function (int $donationId) use ($registeredGateway, $legacyPaymentGatewayAdapter) {
                    $legacyPaymentGatewayAdapter->addOptRefundCheckbox($donationId, $registeredGateway);
                },
                PHP_INT_MAX // Ensure this will be the last callback registered to this hook.
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
                11,
                3
            );
        }
    }

    /**
     * Adds new payment gateways to legacy list for settings
     *
     * @since 2.25.0 add is_visible key to $gatewayData
     * @since 2.19.0
     */
    public function addNewPaymentGatewaysToLegacyListSettings(array $gatewaysData, array $newPaymentGateways): array
    {
        foreach ($newPaymentGateways as $gatewayClassName) {
            /* @var PaymentGatewayInterface $paymentGateway */
            $paymentGateway = give($gatewayClassName);

            $gatewaysData[$paymentGateway::id()] = [
                'admin_label' => $paymentGateway->getName(),
                'checkout_label' => $paymentGateway->getPaymentMethodLabel(),
                'is_visible' => $this->supportsLegacyForm($paymentGateway),
            ];
        }

        return $gatewaysData;
    }

    /**
     * @since 2.25.0
     */
    public function supportsLegacyForm(PaymentGatewayInterface $gateway): bool
    {
        return method_exists($gateway, 'supportsLegacyForm') ? $gateway->supportsLegacyForm() : true;
    }
}

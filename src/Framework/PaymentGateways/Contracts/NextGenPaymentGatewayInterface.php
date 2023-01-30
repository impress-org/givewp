<?php
namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\EnqueueScript;

/**
 * @since 0.1.0
 */
interface NextGenPaymentGatewayInterface
{

    /**
     * @since 0.1.0
     */
    public function supportsLegacyForm(): bool;

    /**
     * @since 0.1.0
     */
    public function enqueueScript(): EnqueueScript;

    /**
     * @since 0.1.0
     */
    public function formSettings(int $formId): array;
}

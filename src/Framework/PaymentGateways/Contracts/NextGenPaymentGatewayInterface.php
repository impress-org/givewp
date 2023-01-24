<?php
namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\EnqueueScript;

/**
 * @unreleased
 */
interface NextGenPaymentGatewayInterface
{

    /**
     * @unreleased
     */
    public function supportsLegacyForm(): bool;

    /**
     * @unreleased
     */
    public function enqueueScript(): EnqueueScript;

    /**
     * @unreleased
     */
    public function formSettings(int $formId): array;
}

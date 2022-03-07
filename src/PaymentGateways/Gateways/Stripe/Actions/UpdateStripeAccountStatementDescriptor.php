<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Repositories\Settings;

/**
 * @since 2.19.0
 */
class UpdateStripeAccountStatementDescriptor
{
    /**
     *
     * @param string $stripeAccountId
     * @param string $stripeStatementDescriptorText
     *
     * @return bool
     * @throws InvalidArgumentException|InvalidPropertyName
     */
    public function __invoke($stripeAccountId, $stripeStatementDescriptorText)
    {
        $settingRepository = give(Settings::class);
        $stripeAccount = $settingRepository->getStripeAccountById($stripeAccountId);

        if ($stripeStatementDescriptorText === $stripeAccount->statementDescriptor) {
            return true;
        }

        $newStripeAccount = AccountDetail::fromArray(
            array_merge(
                $stripeAccount->toArray(),
                ['statement_descriptor' => $stripeStatementDescriptorText]
            )
        );

        return $settingRepository->updateStripeAccount($newStripeAccount);
    }
}

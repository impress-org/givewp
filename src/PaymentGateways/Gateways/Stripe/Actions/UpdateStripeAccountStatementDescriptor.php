<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Repositories\Settings;

/**
 * @unreleased
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

        if ($stripeAccount === null) {
            throw new InvalidArgumentException(
                esc_html__(
                    'Stripe account id does not match to any saved account ids.',
                    'give'
                )
            );
        }

        if ($stripeStatementDescriptorText === $stripeAccount->statementDescriptor) {
            throw new InvalidArgumentException(
                esc_html__(
                    'This Stripe statement descriptor text is already saved in Stripe account.',
                    'give'
                )
            );
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

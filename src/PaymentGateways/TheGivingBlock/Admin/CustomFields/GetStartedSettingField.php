<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields;

use Give\PaymentGateways\TheGivingBlock\Admin\Tabs\GetStarted\GetStartedTabContent;

/**
 * Custom setting field for GiveWP > Payment Gateways > The Giving Block > Get Started tab.
 *
 * @unreleased
 */
class GetStartedSettingField
{
    /**
     * Render the Get Started tab content.
     *
     * @unreleased
     *
     * @param array $field Field config (id, type, wrapper_class, etc.).
     */
    public function handle(array $field): void
    {
        give(GetStartedTabContent::class)->display();
    }
}

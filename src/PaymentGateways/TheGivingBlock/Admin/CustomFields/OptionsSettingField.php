<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields;

use Give\PaymentGateways\TheGivingBlock\Admin\Tabs\Options\OptionsTabContent;

/**
 * Custom setting field for GiveWP > Payment Gateways >The Giving Block > Options tab.
 *
 * @unreleased
 */
class OptionsSettingField
{
    /**
     * Render the Options tab content.
     *
     * @unreleased
     *
     * @param array $field Field config (id, type, wrapper_class, etc.).
     */
    public function handle(array $field): void
    {
        OptionsTabContent::display();
    }
}

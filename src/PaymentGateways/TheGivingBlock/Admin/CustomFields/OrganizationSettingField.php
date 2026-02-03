<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields;

use Give\PaymentGateways\TheGivingBlock\Admin\Tabs\Organization\OrganizationTabContent;

/**
 * Custom setting field for GiveWP > Payment Gateways > The Giving Block > Organization tab.
 *
 * @unreleased
 */
class OrganizationSettingField
{
    /**
     * Render the Organization tab content.
     *
     * @unreleased
     *
     * @param array $field Field config (id, type, wrapper_class, etc.).
     */
    public function handle(array $field): void
    {
        OrganizationTabContent::display();
    }
}

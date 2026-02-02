<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields;

use function esc_attr;

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
        $classes = !empty($field['wrapper_class']) ? esc_attr($field['wrapper_class']) : '';
        ?>
        <div class="give-tgb-setting-field give-tgb-organization-placeholder <?php echo $classes; ?>">
            <h2 class="give-setting-tab-header give-setting-tab-header-organization">
                <?php esc_html_e('Connect to The Giving Block', 'give'); ?>
            </h2>
            <p class="give-tgb-placeholder-description">
                <?php esc_html_e('Content for this section will be migrated from the give-tgb plugin: connect or create an organization, connect existing organization, and organization details.', 'give'); ?>
            </p>
        </div>
        <?php
    }
}

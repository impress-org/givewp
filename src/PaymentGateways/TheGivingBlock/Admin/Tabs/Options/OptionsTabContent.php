<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\Tabs\Options;

use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * @unreleased
 */
class OptionsTabContent
{
    /**
     * @unreleased
     */
    public static function display()
    {
        $hasOrganizationData = OrganizationRepository::hasStoredData();
        ?>
        <div class="give-tgb-card">
            <h2><span class="dashicons dashicons-trash"></span> <?php esc_html_e('Data Management', 'give'); ?></h2>
            <p><?php esc_html_e('Manage your organization data stored in the database:', 'give'); ?></p>

            <div class="give-tgb-data-management">
                <div class="data-info">
                    <h3><?php esc_html_e('Organization Data', 'give'); ?></h3>
                    <p><?php esc_html_e('This will permanently delete all organization data from the database, including:', 'give'); ?></p>
                    <ul>
                        <li><?php esc_html_e('Organization ID and connection status', 'give'); ?></li>
                        <li><?php esc_html_e('Complete organization details and settings', 'give'); ?></li>
                        <li><?php esc_html_e('All cached data and preferences', 'give'); ?></li>
                    </ul>
                    <p><strong><span class="dashicons dashicons-warning"></span> <?php esc_html_e('Warning:', 'give'); ?></strong> <?php esc_html_e('This action cannot be undone. You will need to reconnect or create a new organization.', 'give'); ?></p>
                </div>

                <div class="data-actions">
                    <button type="button"
                        onclick="<?php echo $hasOrganizationData ? 'deleteAllOrganizationData()' : ''; ?>"
                        class="delete-all-btn"
                        <?php echo $hasOrganizationData ? '' : ' disabled="disabled"'; ?>
                        <?php echo $hasOrganizationData ? '' : ' title="' . esc_attr__('No organization data to delete.', 'give') . '"'; ?>>
                        <span class="dashicons dashicons-trash"></span>
                        <span class="button-text"><?php esc_html_e('Delete All Organization Data', 'give'); ?></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
}

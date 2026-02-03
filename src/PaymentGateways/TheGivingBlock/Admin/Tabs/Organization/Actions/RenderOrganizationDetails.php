<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\Tabs\Organization\Actions;

use Give\PaymentGateways\TheGivingBlock\API\TheGivingBlockApi;
use Give\PaymentGateways\TheGivingBlock\DataTransferObjects\Organization;
use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * @unreleased
 */
class RenderOrganizationDetails
{
    /**
     * @unreleased
     */
    public function __invoke(Organization $organization)
    {
        if (empty($organization->id)) {
            $this->displayError(__('No organization data available.', 'give-tgb'));
            return;
        }

        $this->displayOrganizationDetails($organization);
    }

    /**
     * @unreleased
     */
    private function displayOrganizationDetails(Organization $organization)
    {
        ?>
        <div class="give-tgb-card">
            <h2><span class="dashicons dashicons-building"></span> <?php esc_html_e('Organization Details', 'give-tgb'); ?></h2>
            <p><?php esc_html_e('Information for your saved organization:', 'give-tgb'); ?></p>

            <div class="give-tgb-refresh-notice">
                <div class="refresh-info">
                    <div>
                        <strong><span class="dashicons dashicons-database"></span> <?php esc_html_e('Local Data', 'give-tgb'); ?></strong> -
                        <?php esc_html_e('Data saved in local database. Click the button to update with latest data from The Giving Block.', 'give-tgb'); ?>
                    </div>
                    <button type="button"
                        onclick="refreshOrganizationData('<?php echo esc_js($organization->id); ?>')"
                        class="refresh-btn">
                        <span class="dashicons dashicons-update"></span> <?php esc_html_e('Refresh from API', 'give-tgb'); ?>
                    </button>
                </div>
            </div>

            <!-- Organization Details (Single Container) -->
            <div style="border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 5px; background: #f9f9f9;">
                <!-- Content Wrapper for consistent width -->
                <div style="max-width: 100%; width: 100%;">
                    <!-- Organization Header Info -->
                    <?php if (!empty($organization->logo)): ?>
                        <!-- Two Columns Layout (with logo) -->
                        <div class="organization-header-grid" style="display: grid; grid-template-columns: auto 1fr; gap: 20px; margin: 5px 0;">
                            <!-- Left Column: Logo -->
                            <div class="logo-column" style="width: fit-content;">
                                <div style="text-align: center;">
                                    <img src="<?php echo esc_url($organization->logo); ?>"
                                        alt="<?php esc_attr_e('Organization Logo', 'give-tgb'); ?>"
                                        style="max-width: 200px; max-height: 100px; border: 1px solid #ccc; border-radius: 3px;">
                                </div>
                            </div>

                            <!-- Right Column: Name and ID -->
                            <div>
                                <p><strong><?php esc_html_e('Name:', 'give-tgb'); ?></strong>
                                    <?php echo esc_html($organization->name ?: esc_html__('N/A', 'give-tgb')); ?>
                                </p>
                                <div style="display: flex; align-items: center; gap: 20px; margin: 5px 0;">
                                    <div style="flex: 0 0 auto;">
                                        <p><strong><?php esc_html_e('ID:', 'give-tgb'); ?></strong>
                                            <span class="organization-id-container">
                                                <span class="organization-id-value"><?php echo esc_html($organization->id ?: esc_html__('N/A', 'give-tgb')); ?></span>
                                                <?php if (!empty($organization->id)): ?>
                                                    <button type="button"
                                                        onclick="copyOrganizationId('<?php echo esc_js($organization->id); ?>')"
                                                        class="copy-id-btn"
                                                        title="<?php esc_attr_e('Copy Organization ID', 'give-tgb'); ?>">
                                                        <span class="dashicons dashicons-clipboard"></span>
                                                    </button>
                                                <?php endif; ?>
                                            </span>
                                        </p>
                                    </div>
                                    <?php if (!empty($organization->id)): ?>
                                        <div class="id-security-notice" style="flex: 0 1 auto; max-width: fit-content;">
                                            <span class="dashicons dashicons-warning"></span>
                                            <strong><?php esc_html_e('Important:', 'give-tgb'); ?></strong>
                                            <?php esc_html_e('Store this ID in a secure location. You may need it for future reconnections to The Giving Block service.', 'give-tgb'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Single Column Layout (no logo) -->
                        <div style="margin: 5px 0;">
                            <p><strong><?php esc_html_e('Name:', 'give-tgb'); ?></strong>
                                <?php echo esc_html($organization->name ?: esc_html__('N/A', 'give-tgb')); ?>
                            </p>
                            <div style="display: flex; align-items: center; gap: 20px; margin: 5px 0;">
                                <div style="flex: 0 0 auto;">
                                    <p><strong><?php esc_html_e('ID:', 'give-tgb'); ?></strong>
                                        <span class="organization-id-container">
                                            <span class="organization-id-value"><?php echo esc_html($organization->id ?: esc_html__('N/A', 'give-tgb')); ?></span>
                                            <?php if (!empty($organization->id)): ?>
                                                <button type="button"
                                                    onclick="copyOrganizationId('<?php echo esc_js($organization->id); ?>')"
                                                    class="copy-id-btn"
                                                    title="<?php esc_attr_e('Copy Organization ID', 'give-tgb'); ?>">
                                                    <span class="dashicons dashicons-clipboard"></span>
                                                </button>
                                            <?php endif; ?>
                                        </span>
                                    </p>
                                </div>
                                <?php if (!empty($organization->id)): ?>
                                    <div class="id-security-notice" style="flex: 0 1 auto; margin-top: 0; max-width: fit-content;">
                                        <span class="dashicons dashicons-warning"></span>
                                        <strong><?php esc_html_e('Important:', 'give-tgb'); ?></strong>
                                        <?php esc_html_e('Store this ID in a secure location. You may need it for future reconnections to The Giving Block service.', 'give-tgb'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Organization Details Columns -->
                    <div class="organization-details-grid" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; margin: 0; align-items: start; max-width: 600px;">
                        <!-- Left column -->
                        <div>
                            <p><strong><?php esc_html_e('UUID:', 'give-tgb'); ?></strong>
                                <?php echo esc_html($organization->uuid ?: esc_html__('N/A', 'give-tgb')); ?>
                            </p>
                            <p><strong><?php esc_html_e('Tax ID:', 'give-tgb'); ?></strong>
                                <?php echo esc_html($organization->nonprofitTaxID ?: esc_html__('N/A', 'give-tgb')); ?>
                            </p>
                            <p><strong><?php esc_html_e('Country:', 'give-tgb'); ?></strong>
                                <?php echo esc_html($organization->country ?: esc_html__('N/A', 'give-tgb')); ?>
                            </p>
                            <p><strong><?php esc_html_e('State:', 'give-tgb'); ?></strong>
                                <?php echo esc_html($organization->state ?: esc_html__('N/A', 'give-tgb')); ?>
                            </p>
                            <p><strong><?php esc_html_e('City:', 'give-tgb'); ?></strong>
                                <?php echo esc_html($organization->city ?: esc_html__('N/A', 'give-tgb')); ?>
                            </p>
                            <p><strong><?php esc_html_e('Postcode:', 'give-tgb'); ?></strong>
                                <?php echo esc_html($organization->postcode ?: esc_html__('N/A', 'give-tgb')); ?>
                            </p>
                        </div>

                        <!-- Right column -->
                        <div class="details-right-column">
                            <p><strong><?php esc_html_e('Address:', 'give-tgb'); ?></strong>
                                <?php echo esc_html($organization->nonprofitAddress1 ?: esc_html__('N/A', 'give-tgb')); ?>
                            </p>
                            <?php if (!empty($organization->nonprofitAddress2)): ?>
                                <p><strong><?php esc_html_e('Address 2:', 'give-tgb'); ?></strong>
                                    <?php echo esc_html($organization->nonprofitAddress2); ?>
                                </p>
                            <?php endif; ?>
                            <p><strong><?php esc_html_e('Allows Anonymous:', 'give-tgb'); ?></strong>
                                <?php echo $organization->allowsAnon ? '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . esc_html__('Yes', 'give-tgb') : '<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span> ' . esc_html__('No', 'give-tgb'); ?>
                            </p>
                            <p><strong><?php esc_html_e('Notes Enabled:', 'give-tgb'); ?></strong>
                                <?php echo $organization->areNotesEnabled ? '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . esc_html__('Yes', 'give-tgb') : '<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span> ' . esc_html__('No', 'give-tgb'); ?>
                            </p>
                            <p><strong><?php esc_html_e('Receipt Enabled:', 'give-tgb'); ?></strong>
                                <?php echo $organization->isReceiptEnabled ? '<span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> ' . esc_html__('Yes', 'give-tgb') : '<span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span> ' . esc_html__('No', 'give-tgb'); ?>
                            </p>
                            <p><strong><?php esc_html_e('Created:', 'give-tgb'); ?></strong>
                                <?php echo esc_html($organization->createdAt ?: esc_html__('N/A', 'give-tgb')); ?>
                            </p>

                            <!-- Disconnect Button -->
                            <div style="margin-top: 25px;">
                                <button type="button"
                                    onclick="disconnectOrganization()"
                                    class="disconnect-btn">
                                    <span class="dashicons dashicons-editor-unlink"></span>
                                    <span class="button-text"><?php esc_html_e('Disconnect Organization', 'give-tgb'); ?></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="address-notice" style="margin-top: 25px; font-style: italic; flex: 0 1 auto; max-width: fit-content;">
                        <span class="dashicons dashicons-info"></span>
                        <?php esc_html_e("All donations are processed via The Giving Block's U.S. distribution partner, Modern Philanthropy Foundation. The EIN and address provided above are associated with this entity, which allocates the funds directly to your organization.", 'give-tgb'); ?>
                    </div>
                </div>
            </div>

        <?php
    }

    /**
     * @unreleased
     */
    private function displayError(string $message)
    {
        ?>
            <div class="give-tgb-card">
                <h2>
                    <span class="dashicons dashicons-warning"></span> <?php esc_html_e('Organization Details Error', 'give-tgb'); ?>
                </h2>
                <div class="give-tgb-status error">
                    <strong><?php esc_html_e('Error:', 'give-tgb'); ?></strong> <?php echo esc_html($message); ?>
                </div>
            </div>
    <?php
    }

    /**
     * @unreleased
     */
    public static function refreshFromApi(string $organizationId)
    {
        $organizationResponse = TheGivingBlockApi::getOrganizationById($organizationId);

        if (!is_array($organizationResponse) || ($organizationResponse['code'] ?? 0) !== 200) {
            return new \WP_Error('refresh_failed', __('Failed to refresh organization data from API', 'give-tgb'));
        }

        $code = $organizationResponse['code'];
        $data = $organizationResponse['data'];

        if ($code === 200 && isset($data['data']['organization'])) {
            OrganizationRepository::update($data['data']['organization']);
            return true;
        }

        return new \WP_Error('refresh_failed', __('Failed to refresh organization data from API', 'give-tgb'));
    }
}

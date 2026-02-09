<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions;

/**
 * @unreleased
 */
class RenderOnboardingForm
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $this->displayOnboardingForm();
    }

    /**
     * @unreleased
     */
    private function displayOnboardingForm()
    {
        // Check if we have existing organization data
        $existingOrganization = get_option('give_tgb_organization', []);
        $hasExistingData = !empty($existingOrganization) && isset($existingOrganization['id']);
        $defaultTab = $hasExistingData ? 'existing' : 'new';
        $defaultOrganizationId = $hasExistingData ? $existingOrganization['id'] : '';
        ?>
        <div class="give-tgb-card">
            <h2><?php esc_html_e('Connect to The Giving Block', 'give'); ?></h2>

            <!-- Tab Navigation -->
            <div class="give-tgb-tab-navigation">
                <button type="button" class="give-tgb-tab-button <?php echo $defaultTab === 'new' ? 'active' : ''; ?>" data-tab="new">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e('Create New Organization', 'give'); ?>
                </button>
                <button type="button" class="give-tgb-tab-button <?php echo $defaultTab === 'existing' ? 'active' : ''; ?>" data-tab="existing">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php esc_html_e('Connect Existing', 'give'); ?>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="give-tgb-tab-content">
                <!-- New Organization Tab -->
                <div id="tab-new" class="give-tgb-tab-panel <?php echo $defaultTab === 'new' ? 'active' : ''; ?>">
                    <div class="give-tgb-tab-description">
                        <h3><?php esc_html_e('Create New Organization', 'give'); ?></h3>
                        <p><?php esc_html_e('Submit a new organization for onboarding with The Giving Block. Fill out the form below with your organization details.', 'give'); ?></p>
                    </div>
                </div>

                <!-- Existing Organization Tab -->
                <div id="tab-existing" class="give-tgb-tab-panel <?php echo $defaultTab === 'existing' ? 'active' : ''; ?>">
                    <div class="give-tgb-tab-description">
                        <h3><?php esc_html_e('Connect Existing Organization', 'give'); ?></h3>
                        <p><?php esc_html_e('I already have an Organization ID from The Giving Block. Enter your Organization ID below to connect your existing organization.', 'give'); ?></p>
                    </div>
                </div>
            </div>

            <!-- New Organization Form -->
            <div id="new-organization-form">
                <h3><?php esc_html_e('New Organization Information', 'give'); ?></h3>

            <form id="giveTgbOnboardingForm" method="post">
                <?php wp_nonce_field('giveTgbNonce', 'giveTgbOnboardingNonceField'); ?>

                <div class="give-tgb-form-section">

                    <!-- Organization Name and EIN -->
                    <div class="give-tgb-form-row">
                        <div>
                            <label for="organizationName"><span class="required">*</span> <?php esc_html_e('Organization Name:', 'give'); ?></label>
                            <input type="text" id="organizationName" name="name" required>
                        </div>
                        <div>
                            <label for="organizationEin"><span class="required">*</span> <?php esc_html_e('EIN (Tax ID):', 'give'); ?></label>
                            <input type="text" id="organizationEin" name="ein" required placeholder="e.g., 12-3456789">
                        </div>
                    </div>

                    <!-- Contact Email and Website URL -->
                    <div class="give-tgb-form-row">
                        <div>
                            <label for="organizationContactEmail"><span class="required">*</span> <?php esc_html_e('Contact Email:', 'give'); ?></label>
                            <input type="email" id="organizationContactEmail" name="contactEmail" required>
                        </div>
                        <div>
                            <label for="organizationWebsiteUrl"><?php esc_html_e('Website URL:', 'give'); ?></label>
                            <input type="url" id="organizationWebsiteUrl" name="websiteUrl" placeholder="https://example.com">
                        </div>
                    </div>

                    <!-- Website Profile Checkbox - Aligned to right column -->
                    <div class="give-tgb-form-row">
                        <div></div>
                        <div class="give-tgb-checkbox-row">
                            <input type="checkbox" id="isWebsiteProfileVisible" name="isWebsiteProfileVisible" value="1">
                            <label for="isWebsiteProfileVisible"><?php esc_html_e('Make Website Profile Visible', 'give'); ?></label>
                        </div>
                    </div>
                </div>

                <div class="give-tgb-form-section">
                    <h3><?php esc_html_e('Address Information', 'give'); ?></h3>

                    <!-- Address Line 1 and 2 -->
                    <div class="give-tgb-form-row">
                        <div>
                            <label for="organizationAddress1"><span class="required">*</span> <?php esc_html_e('Address Line 1:', 'give'); ?></label>
                            <input type="text" id="organizationAddress1" name="address1" required>
                        </div>
                        <div>
                            <label for="organizationAddress2"><?php esc_html_e('Address Line 2:', 'give'); ?></label>
                            <input type="text" id="organizationAddress2" name="address2">
                        </div>
                    </div>

                    <!-- City and State -->
                    <div class="give-tgb-form-row">
                        <div>
                            <label for="organizationCity"><span class="required">*</span> <?php esc_html_e('City:', 'give'); ?></label>
                            <input type="text" id="organizationCity" name="city" required>
                        </div>
                        <div>
                            <label for="organizationState"><span class="required">*</span> <?php esc_html_e('State:', 'give'); ?></label>
                            <select id="organizationState" name="state" required>
                                <option value=""><?php esc_html_e('Select State', 'give'); ?></option>
                                <?php
                                foreach (give_get_states('US') as $code => $label) {
                                    if ($code === '') {
                                        continue;
                                    }
                                    ?>
                                    <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($label); ?></option>
                                    <?php
                                }
        ?>
                            </select>
                        </div>
                    </div>

                    <!-- Postal Code and Country -->
                    <div class="give-tgb-form-row">
                        <div>
                            <label for="organizationPostcode"><span class="required">*</span> <?php esc_html_e('Postal Code:', 'give'); ?></label>
                            <input type="text" id="organizationPostcode" name="postcode" required pattern="[0-9]{5}" maxlength="5" placeholder="12345">
                            <small><?php esc_html_e('Must be exactly 5 digits', 'give'); ?></small>
                        </div>
                        <div>
                            <label for="organizationCountry"><?php esc_html_e('Country:', 'give'); ?></label>
                            <input type="text" id="organizationCountry" name="country" value="USA" readonly>
                            <small class="give-tgb-country-notice"><?php esc_html_e('The Giving Block only accepts organizations from the USA', 'give'); ?></small>
                        </div>
                    </div>
                </div>

                <div class="give-tgb-submit-row">
                    <input type="button" name="submit" id="submitOnboarding" class="give-tgb-button button-secondary" value="<?php esc_attr_e('Submit Organization for Onboarding', 'give'); ?>">
                    <span id="giveTgbOnboardingMessage"></span>
                </div>
            </form>
            </div>

            <!-- Existing Organization Form -->
            <div id="existing-organization-form" style="display: none;">
                <h3><?php esc_html_e('Connect Existing Organization', 'give'); ?></h3>
                <p><?php esc_html_e('Enter your Organization ID from The Giving Block to connect your existing organization.', 'give'); ?></p>

                <form id="giveTgbExistingOrganizationForm" method="post">
                    <?php wp_nonce_field('giveTgbNonce', 'giveTgbExistingOrganizationNonceField'); ?>

                    <div class="give-tgb-form-section">
                        <div class="give-tgb-form-row">
                            <div>
                                <label for="existingOrganizationId"><span class="required">*</span> <?php esc_html_e('Organization ID:', 'give'); ?></label>
                                <input type="text" id="existingOrganizationId" name="organizationId" required placeholder="e.g., 1189132016" value="<?php echo esc_attr($defaultOrganizationId); ?>">
                                <small><?php esc_html_e('Enter the Organization ID provided by The Giving Block.', 'give'); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="give-tgb-submit-row">
                        <input type="button" name="submit" id="submitExistingOrganization" class="give-tgb-button button-secondary" value="<?php esc_attr_e('Connect Organization', 'give'); ?>">
                        <span id="giveTgbExistingOrganizationMessage"></span>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}

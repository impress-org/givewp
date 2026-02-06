<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\Tabs\GetStarted;

use Give\Framework\Http\ConnectServer\Client\ConnectClient;
use Give\PaymentGateways\TheGivingBlock\DataTransferObjects\Organization;
use Give\PaymentGateways\TheGivingBlock\Repositories\OrganizationRepository;

/**
 * @unreleased
 */
class GetStartedTabContent
{
    /**
     * @unreleased
     */
    public function display()
    {
        ?>
        <div class="wrap">
            <div style="text-align: left;">
                <img src="<?php echo esc_url(GIVE_PLUGIN_URL . 'src/PaymentGateways/TheGivingBlock/assets/images/TGB-Logo-on-Light-Full-Color-Brand.png'); ?>" alt="<?php esc_attr_e('The Giving Block by GiveWP', 'give'); ?>" style="display: inline-block; max-width: 280px; width: 100%; height: auto;">
            </div>

            <?php if (OrganizationRepository::isConnected()) : ?>
                <?php
                $organization = Organization::fromOptions();
                $this->displayConnectedOrganizationContent($organization);
                ?>
            <?php else : ?>
                <?php $this->displaySetupInstructions(); ?>
            <?php endif; ?>



        </div>
        <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('[data-give-tgb-switch-group]').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        var group = btn.getAttribute('data-give-tgb-switch-group');
                        var menu = document.querySelector('.give-settings-section-content .give-settings-section-group-menu');
                        if (!menu) return;
                        var tabLink = menu.querySelector('a[data-group="' + group.replace(/["\\]/g, '\\$&') + '"]');
                        if (tabLink) tabLink.click();
                        return false;
                    });
                });
            });
        })();
        </script>
        <?php
    }

    /**
     * @unreleased
     */
    private function displaySetupInstructions()
    {
        $organizationTabUrl = admin_url(
            'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=the-giving-block&group=organization'
        );
        ?>
        <div class="give-tgb-setting-field give-tgb-get-started">

            <div class="give-tgb-quick-start-box" style="background: #f0f8ff; border: 1px solid #0073aa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3 class="give-tgb-quick-start-title" style="margin-top: 0; color: #1565c0;">
                    <span class="dashicons dashicons-info" style="color: #1976d2;"></span> <?php esc_html_e('Quick Start', 'give'); ?>
                </h3>
                <p class="give-tgb-quick-start-intro">
                    <?php esc_html_e('To get started with crypto and stock donations:', 'give'); ?>
                </p>
                <ol class="give-tgb-quick-start-steps">
                    <li>
                        <?php esc_html_e('Use the Organization tab to connect your existing organization or create a new one with The Giving Block.', 'give'); ?>
                    </li>
                    <li>
                        <?php esc_html_e('Add The Giving Block donation form to any page or post using the shortcode or block in the editor.', 'give'); ?>
                    </li>
                    <li>
                        <?php esc_html_e('Use the Options tab to manage your data and configure additional settings.', 'give'); ?>
                    </li>
                </ol>
                <p class="give-tgb-quick-start-actions">
                    <a href="<?php echo esc_url($organizationTabUrl); ?>" class="button button-secondary give-tgb-connect-organization-btn" data-give-tgb-switch-group="organization">
                        <?php esc_html_e('Connect Organization', 'give'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
        $this->displayWarningBox();
    }

    private function displayConnectedOrganizationContent(Organization $organization)
    {
        $organizationName = $organization->name ?? esc_html__('Unknown Organization', 'give');
        $organizationId = $organization->id;
        $connectClient = give(ConnectClient::class);
        $apiBaseUrl = $connectClient->getApiUrl();
        $isSandbox = defined('GIVE_TGB_CONNECT_MODE') && GIVE_TGB_CONNECT_MODE === 'sandbox';
        ?>

        <div class="give-tgb-organization-connected" style="background: #e8f5e8; border: 1px solid #4caf50; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #2e7d32;">
                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <?php esc_html_e('Organization Connected', 'give'); ?>
            </h3>
            <p>
                <strong><?php esc_html_e('Organization:', 'give'); ?></strong> <?php echo esc_html($organizationName); ?>
            </p>
            <p>
                <strong><?php esc_html_e('ID:', 'give'); ?></strong> <?php echo esc_html($organizationId); ?>
            </p>

            <hr style="margin: 15px 0; border: none; border-top: 1px solid #c8e6c9;">
            <div style="background: #ffffff; border: 1px solid #c8e6c9; padding: 12px; border-radius: 4px; margin-top: 10px;">
                <h4 style="margin: 0 0 8px 0; color: #2e7d32; display: flex; align-items: center; gap: 6px;">
                    <span class="dashicons dashicons-welcome-widgets-menus"></span> <?php esc_html_e('TGB Dashboard Access', 'give'); ?>
                </h4>
                <p style="margin: 8px 0; font-size: 14px;">
                    <?php esc_html_e('You will receive an email titled "You\'ve been granted access to The Giving Block dashboard (Action Required)" with login credentials.', 'give'); ?>
                </p>
                <p style="margin: 8px 0; font-size: 14px;">
                    <?php esc_html_e('Use the temporary password to login and you\'ll be required to change it upon first access.', 'give'); ?>
                </p>
                <p style="margin: 8px 0 0 0;">
                    <a href="https://dashboard.tgbwidget.com" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                        <span class="dashicons dashicons-external"></span> <?php esc_html_e('Access TGB Dashboard', 'give'); ?>
                    </a>
                    <br>
                    <small style="color: #666; margin-top: 4px; display: block;">
                        <?php esc_html_e('Opens in new tab - Use your email and temporary password from the email', 'give'); ?>
                    </small>
                </p>
                <p style="margin: 12px 0 0 0; font-size: 14px;">
                    <?php esc_html_e('Need help?', 'give'); ?>
                    <a href="https://thegivingblock.com/about/contact/" target="_blank" rel="noopener noreferrer" style="color: #0073aa; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-left: 4px;">
                        <span class="dashicons dashicons-external" style="font-size: 16px; width: 16px; height: 16px;"></span>
                        <?php esc_html_e('Contact The Giving Block Support', 'give'); ?>
                    </a>
                </p>
            </div>
        </div>
        <div style="background: #fff3e0; border: 1px solid #ff9800; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #ff9800;">
                <span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e('Usage Information', 'give'); ?>
            </h3>
            <p>
                <strong><?php esc_html_e('WordPress Integration:', 'give'); ?></strong>
                <?php esc_html_e('For WordPress pages and posts, you have several options:', 'give'); ?>
            </p>
            <p>
                <strong><?php esc_html_e('WordPress Block:', 'give'); ?></strong>
                <?php
                printf(
                    /* translators: %s: Plugin name */
                    esc_html__('Add the "%s" block from the embed category. In the block editor you can choose display type (iframe or popup) and configure additional options in the block settings. Those same options are available as shortcode attributes and are described below.', 'give'),
                    esc_html__('The Giving Block by GiveWP', 'give')
                );
                ?>
            </p>
            <p>
                <strong><?php esc_html_e('Shortcode:', 'give'); ?></strong>
                <?php
                printf(
                    /* translators: %1$s: iframe shortcode, %2$s: popup shortcode */
                    esc_html__('Use %1$s to display the donation form directly, or %2$s to show a button that opens a popup modal. The same options available in the block (listed below) can be used as shortcode attributes.', 'give'),
                    '<code>[give_tgb_form type="iframe"]</code>',
                    '<code>[give_tgb_form type="popup"]</code>'
                );
                ?>
            </p>
            <p>
                <strong><?php esc_html_e('Options (shortcode attributes / block settings)', 'give'); ?></strong>
            </p>
            <ul style="margin-bottom: 12px;">
                <li><code>type</code> — <?php esc_html_e('"iframe" (default) or "popup". Iframe shows the form on the page; popup shows a button that opens the form in a modal.', 'give'); ?></li>
                <li><code>popup_button_text</code> — <?php esc_html_e('Button label when type is popup (e.g. "Donate Crypto"). Leave empty for default.', 'give'); ?></li>
                <li><code>popup_button_notice_enable</code> — <?php esc_html_e('Set to "true" to show the campaign stats notice below the button (explains that crypto/stock donations are not included in campaign statistics).', 'give'); ?></li>
                <li><code>popup_button_notice_short_text</code> — <?php esc_html_e('Short text shown next to the info icon (e.g. "Do not affect stats"). Leave empty for default.', 'give'); ?></li>
                <li><code>popup_button_notice_short_cta</code> — <?php esc_html_e('Link text that opens the detailed notice modal (e.g. "Learn more"). Leave empty for default.', 'give'); ?></li>
                <li><code>popup_button_notice_long_text</code> — <?php esc_html_e('Full text shown in the notice modal. Leave empty for default explanation.', 'give'); ?></li>
            </ul>
            <p style="margin-bottom: 0;">
                <em><?php esc_html_e('Example with custom button and notice:', 'give'); ?></em>
                <code style="display: block; margin-top: 6px; padding: 8px; background: rgba(0,0,0,0.06); border-radius: 4px; font-size: 12px; white-space: pre-wrap;">[give_tgb_form type="popup" popup_button_text="Donate Crypto" popup_button_notice_enable="true"]</code>
            </p>
            <p style="margin-top: 12px; margin-bottom: 0;">
                <em><?php esc_html_e('Note: The iframe type displays the donation form directly on the page, while the popup type shows a button that opens the donation form in a modal window.', 'give'); ?></em>
            </p>
            <p style="margin-top: 12px; margin-bottom: 0;">
                <strong><?php esc_html_e('New GiveWP campaign pages:', 'give'); ?></strong>
                <?php esc_html_e('When your organization is connected, a "Donate Crypto" button (The Giving Block block) is automatically added to new campaign pages, right below the regular donate button. You can disable this in the Options tab if you prefer to add the block manually.', 'give'); ?>
            </p>
        </div>

        <div style="background: #f0f8ff; border: 1px solid #0073aa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #0073aa;">
                <span class="dashicons dashicons-admin-site"></span> <?php esc_html_e('External Website Integration', 'give'); ?>
            </h3>
            <p>
                <strong><?php esc_html_e('For websites outside of WordPress:', 'give'); ?></strong>
                <?php esc_html_e('Use the embed codes below to integrate donation forms on any external website or platform.', 'give'); ?>
            </p>
        </div>

        <?php
        // Display widgets
        $this->displayWidgetScript($organization->widgetCode ?? []);
        $this->displayWidgetPopup($organization->widgetCode ?? []);
        $this->displayWidgetIframe($organization->widgetCode ?? []);
        $this->displayWarningBox();
        ?>

        <?php if ($isSandbox) : ?>
            <?php $donation_url = 'https://sandbox.thegivingblock.com/?version=2&organizationId=' . esc_attr($organizationId); ?>
            <div style="background: #f8f9fa; border: 1px solid #6c757d; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3 style="margin-top: 0; color: #495057; display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e('Sandbox Testing Tools', 'give'); ?>
                </h3>
                <p style="margin: 8px 0; color: #6c757d;">
                    <strong><?php esc_html_e('Connect Server URL:', 'give'); ?></strong>
                    <code style="background: #e9ecef; color: #495057; padding: 2px 6px; border-radius: 3px;"><?php echo esc_html($apiBaseUrl); ?></code>
                </p>
                <p style="margin: 8px 0; color: #6c757d;">
                    <?php
                    echo wp_kses_post(
                        sprintf(
                            /* translators: 1: constant name GIVE_TGB_CONNECT_MODE wrapped in strong, 2: constant name GIVE_CONNECT_URL wrapped in strong */
                            esc_html__('This section is shown because the PHP constant %1$s is set to "sandbox" in your wp-config.php (or similar). All requests go through the Connect Server URL above and use sandbox mode when calling The Giving Block API. You can point to a locally configured server by defining the constant %2$s in your wp-config.php (or similar) with the desired URL.', 'give'),
                            '<strong>' . esc_html('GIVE_TGB_CONNECT_MODE') . '</strong>',
                            '<strong>' . esc_html('GIVE_CONNECT_URL') . '</strong>'
                        )
                    );
            ?>
                </p>
                <hr style="margin: 15px 0; border: none; border-top: 1px solid #dee2e6;">
                <p style="margin: 8px 0 15px 0; font-weight: 500; color: #495057;">
                    <?php esc_html_e('Use the links below to test the integration while in sandbox mode:', 'give'); ?>
                </p>

                <div style="margin: 12px 0; padding: 12px; background: #ffffff; border: 1px solid #dee2e6; border-radius: 4px;">
                    <strong style="color: #495057; display: flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-heart"></span> <?php esc_html_e('Test Donation Form:', 'give'); ?>
                    </strong>
                    <br>
                    <a href="<?php echo esc_url($donation_url); ?>" target="_blank" rel="noopener noreferrer" style="color: #007cba; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; margin-top: 6px;">
                        <span class="dashicons dashicons-external"></span> <?php esc_html_e('Open Test Donation Form for', 'give'); ?> <?php echo esc_html($organizationName); ?>
                    </a>
                    <br>
                    <small style="color: #6c757d; margin-top: 4px; display: block;">
                        <?php esc_html_e('Opens in new tab - Use this to test crypto and stock donations', 'give'); ?>
                    </small>
                </div>

                <div style="margin: 12px 0; padding: 12px; background: #ffffff; border: 1px solid #dee2e6; border-radius: 4px;">
                    <strong style="color: #495057; display: flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-products"></span> <?php esc_html_e('Testing Documentation (Crypto):', 'give'); ?>
                    </strong>
                    <br>
                    <a href="https://docs.thegivingblock.com/docs/how-to-make-a-test-crypto-donation" target="_blank" rel="noopener noreferrer" style="color: #007cba; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; margin-top: 6px;">
                        <span class="dashicons dashicons-external"></span> <?php esc_html_e('How to Make a Test Crypto Donation', 'give'); ?>
                    </a>
                    <br>
                    <small style="color: #6c757d; margin-top: 4px; display: block;">
                        <?php esc_html_e('Complete guide for testing crypto donations, including testnet setup and simulated donations.', 'give'); ?>
                    </small>
                </div>

                <div style="margin: 12px 0; padding: 12px; background: #ffffff; border: 1px solid #dee2e6; border-radius: 4px;">
                    <strong style="color: #495057; display: flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-chart-area"></span> <?php esc_html_e('Testing Documentation (Stock):', 'give'); ?>
                    </strong>
                    <br>
                    <a href="https://docs.thegivingblock.com/docs/how-to-make-a-test-stock-donation" target="_blank" rel="noopener noreferrer" style="color: #007cba; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; margin-top: 6px;">
                        <span class="dashicons dashicons-external"></span> <?php esc_html_e('How to Make a Test Stock Donation', 'give'); ?>
                    </a>
                    <br>
                    <small style="color: #6c757d; margin-top: 4px; display: block;">
                        <?php esc_html_e('Step-by-step instructions for testing stock donations in sandbox.', 'give'); ?>
                    </small>
                </div>

                <?php $dev_dashboard_url = 'https://dashboard.sandbox.thegivingblock.com/login'; ?>
                <div style="margin: 12px 0; padding: 12px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
                    <strong style="color: #856404; display: flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Development Dashboard:', 'give'); ?>
                    </strong>
                    <br>
                    <a href="<?php echo esc_url($dev_dashboard_url); ?>" target="_blank" rel="noopener noreferrer" style="color: #007cba; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; margin-top: 6px;">
                        <span class="dashicons dashicons-external"></span> <?php esc_html_e('Open Development Dashboard', 'give'); ?>
                    </a>
                    <br>
                    <small style="color: #856404; margin-top: 4px; display: block;">
                        <strong><?php esc_html_e('Development Only:', 'give'); ?></strong>
                        <?php esc_html_e('Use this during development to view all organizations and transactions made through the plugin. This tool is exclusively for development purposes.', 'give'); ?>
                    </small>
                    <small style="color: #856404; margin-top: 4px; display: block;">
                        <?php esc_html_e('If you are a plugin developer, you should have received an email titled "You\'ve been granted access to The Giving Block Dashboard (Action Required)" which contains your login and a temporary password that must be changed after your first login.', 'give'); ?>
                    </small>
                </div>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin: 20px 0;">
            <?php $organizationTabUrl = admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=the-giving-block&group=organization'); ?>
            <a href="<?php echo esc_url($organizationTabUrl); ?>" class="button button-secondary" data-give-tgb-switch-group="organization">
                <?php esc_html_e('Manage Organization Settings', 'give'); ?>
            </a>
        </div>
        <?php
    }

    /**
     * @unreleased
     */
    private function displayWidgetScript(array $widgetCode)
    {
        if (isset($widgetCode['script'])): ?>
            <div style="margin-top: 10px; padding: 10px; background: #e3f2fd; border: 1px solid #2196f3; border-radius: 5px;">
                <strong><span class="dashicons dashicons-editor-code"></span> <?php esc_html_e('Widget Script:', 'give'); ?></strong><br>
                <div style="margin-top: 8px;">
                    <button type="button"
                        onclick="copyToClipboard('<?php echo esc_js($widgetCode['script']); ?>')"
                        class="button button-secondary"
                        style="display: inline-flex; align-items: center; gap: 6px; line-height: 1;">
                        <span class="dashicons dashicons-clipboard" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: middle;"></span>
                        <span style="line-height: 1;"><?php esc_html_e('Copy Embed Code', 'give'); ?></span>
                    </button>
                    <br><small style="color: #666; margin-left: 0;"><?php esc_html_e('Copy the embed code for your website', 'give'); ?></small>
                </div>
            </div>
        <?php endif;
    }

    /**
     * @unreleased
     */
    private function displayWidgetPopup(array $widgetCode)
    {
        if (isset($widgetCode['popup'])): ?>
            <div style="margin-top: 10px; padding: 10px; background: #e3f2fd; border: 1px solid #2196f3; border-radius: 5px;">
                <strong><span class="dashicons dashicons-external"></span> <?php esc_html_e('Widget Popup:', 'give'); ?></strong><br>
                <div style="margin-top: 8px;">
                    <button type="button"
                        onclick="copyToClipboard('<?php echo esc_js($widgetCode['popup']); ?>')"
                        class="button button-secondary"
                        style="display: inline-flex; align-items: center; gap: 6px; line-height: 1;">
                        <span class="dashicons dashicons-clipboard" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: middle;"></span>
                        <span style="line-height: 1;"><?php esc_html_e('Copy Popup Code', 'give'); ?></span>
                    </button>
                    <br><small style="color: #666; margin-left: 0;"><?php esc_html_e('Copy the popup code for your website', 'give'); ?></small>
                </div>
            </div>
        <?php endif;
    }

    /**
     * @unreleased
     */
    private function displayWidgetIframe(array $widgetCode)
    {
        if (isset($widgetCode['iframe'])): ?>
            <div style="margin-top: 10px; padding: 10px; background: #e3f2fd; border: 1px solid #2196f3; border-radius: 5px;">
                <strong><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e('Widget Iframe:', 'give'); ?></strong><br>
                <div style="margin-top: 8px;">
                    <button type="button"
                        onclick="copyToClipboard('<?php echo esc_js($widgetCode['iframe']); ?>')"
                        class="button button-secondary"
                        style="display: inline-flex; align-items: center; gap: 6px; line-height: 1;">
                        <span class="dashicons dashicons-clipboard" style="font-size: 16px; width: 16px; height: 16px; line-height: 1; vertical-align: middle;"></span>
                        <span style="line-height: 1;"><?php esc_html_e('Copy Iframe Code', 'give'); ?></span>
                    </button>
                    <br><small style="color: #666; margin-left: 0;"><?php esc_html_e('Copy the iframe code for your website', 'give'); ?></small>
                </div>
            </div>
        <?php endif;
    }

    /**
     * @unreleased
     */
    private function displayWarningBox()
    {
        ?>
        <div class="give-tgb-warning-box" style="margin-top: 1.5em; background: #fff8e6; border: 1px solid #e6b800; padding: 15px; border-radius: 5px; margin-bottom: 1.5em;">
            <h3 style="margin-top: 0; color: #b38600;">
                <span class="dashicons dashicons-warning" style="color: #e6b800;"></span> <?php esc_html_e('Important: Donations & Donors Management', 'give'); ?>
            </h3>
            <p style="margin: 8px 0; color: #5c4a00;">
                <?php esc_html_e('The Giving Block provides its own donation form, rendered via a shortcode or block in the WordPress block editor.', 'give'); ?>
            </p>
            <p style="margin: 8px 0; color: #5c4a00;">
                <?php esc_html_e('Donations and donors created through The Giving Block forms are not managed by the standard GiveWP Donations and Donors screens. Those donors and donations must be managed independently through an external dashboard. Access to this dashboard is sent by email to organizations connected to The Giving Block platform.', 'give'); ?>
            </p>
            <p style="margin: 8px 0; color: #5c4a00;">
                <?php esc_html_e('On a regular GiveWP campaign page, you can add the block with the "Crypto Donation" button alongside the standard GiveWP donation button to offer both options to your supporters.', 'give'); ?>
            </p>
        </div>
        <?php
    }
}

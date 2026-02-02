<?php

namespace Give\PaymentGateways\TheGivingBlock\Admin\CustomFields;

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
        $classes = !empty($field['wrapper_class']) ? esc_attr($field['wrapper_class']) : '';
        $organizationTabUrl = admin_url(
            'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=the-giving-block&group=organization'
        );
        ?>
        <div class="give-tgb-setting-field give-tgb-get-started <?php echo $classes; ?>">
            <p class="give-tgb-get-started-welcome">
                <?php esc_html_e('Welcome to The Giving Block. With this integration, you can accept cryptocurrency and stock donations. The Giving Block provides its own donation form, rendered via a shortcode or block in the WordPress block editor.', 'give'); ?>
            </p>

            <div class="give-tgb-quick-start-box" style="background: #f0f8ff; border: 1px solid #0073aa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h3 class="give-tgb-quick-start-title" style="margin-top: 0;">
                    <span class="dashicons dashicons-rocket"></span> <?php esc_html_e('Quick Start', 'give'); ?>
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
                    <a href="<?php echo esc_url($organizationTabUrl); ?>" class="button button-primary give-tgb-connect-organization-btn" data-give-tgb-switch-group="organization">
                        <?php esc_html_e('Connect Organization', 'give'); ?>
                    </a>
                </p>
            </div>
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
}

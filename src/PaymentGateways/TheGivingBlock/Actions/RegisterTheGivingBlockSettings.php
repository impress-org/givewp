<?php

namespace Give\PaymentGateways\TheGivingBlock\Actions;

use Give\Helpers\Hooks;
use Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\GetStarted\GetStartedSettingField;
use Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\OrganizationSettingField;
use Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions\HandleOrganizationDeletion;
use Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions\HandleApiRefresh;
use Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions\HandleConnectingSubmission;
use Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions\HandleOnboardingSubmission;
use Give\PaymentGateways\TheGivingBlock\Admin\CustomFields\Organization\Actions\HandleOrganizationDisconnect;
use Give\PaymentGateways\TheGivingBlock\Admin\TheGivingBlockSettingPage;

/**
 * @unreleased
 */
class RegisterTheGivingBlockSettings
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        // The Giving Block settings page under GiveWP > Payment Gateways
        add_action('give-settings_start', function () {
            give()->make(TheGivingBlockSettingPage::class)->boot();
        });

        // CustomFields (Groups/Vertical Tabs) for The Giving Block settings page
        Hooks::addAction('give_admin_field_the_giving_block_get_started', GetStartedSettingField::class, 'handle');
        Hooks::addAction('give_admin_field_the_giving_block_organization', OrganizationSettingField::class, 'handle');

        //Assets – loaded only on Settings > Gateways > The Giving Block
        add_action('admin_enqueue_scripts', function ($hook) {
            if (
                strpos($hook, 'give-settings') !== false
                && give_get_current_setting_tab() === 'gateways'
                && give_get_current_setting_section() === 'the-giving-block'
            ) {
                wp_enqueue_style('dashicons');
                wp_enqueue_style('giveTgbAdminPages', GIVE_PLUGIN_URL . 'src/PaymentGateways/TheGivingBlock/assets/css/adminPages.css', [], GIVE_VERSION);
                wp_enqueue_script('giveTgbAdminPages', GIVE_PLUGIN_URL . 'src/PaymentGateways/TheGivingBlock/assets/js/adminPages.js', ['jquery', 'wp-i18n'], GIVE_VERSION, true);
                wp_set_script_translations('giveTgbAdminPages', 'give');

                wp_localize_script('giveTgbAdminPages', 'giveTgbSettings', [
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('giveTgbNonce'),
                    'getStartedUrl' => admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=the-giving-block&group=get-started'),
                ]);
            }
        });

        //Ajax actions for The Giving Block settings page
        Hooks::addAction('wp_ajax_giveTgbOnboarding', HandleOnboardingSubmission::class);
        Hooks::addAction('wp_ajax_giveTgbConnectExisting', HandleConnectingSubmission::class);
        Hooks::addAction('wp_ajax_giveTgbRefreshOrganization', HandleApiRefresh::class);
        Hooks::addAction('wp_ajax_giveTgbDisconnectOrganization', HandleOrganizationDisconnect::class);
        Hooks::addAction('wp_ajax_giveTgbDeleteAllOrganizationData', HandleOrganizationDeletion::class);
    }
}

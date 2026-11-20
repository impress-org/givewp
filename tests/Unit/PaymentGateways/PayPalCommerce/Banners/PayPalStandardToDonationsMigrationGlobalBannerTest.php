<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalCommerce\Banners;

use Give\PaymentGateways\PayPalCommerce\Banners\PayPalStandardToDonationsMigrationGlobalBanner;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class PayPalStandardToDonationsMigrationGlobalBannerTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testModalScriptFillsInTheBannerValues()
    {
        $script = (new PayPalStandardToDonationsMigrationGlobalBanner())->getModalScript();
        $nonce = wp_create_nonce('give_edit_PayPalStandardToDonationsMigrationGlobalBanner_notice');
        $settingsUrl = esc_url_raw(admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=paypal'));

        $this->assertStringContainsString("formData.append('notice_id', 'PayPalStandardToDonationsMigrationGlobalBanner');", $script);
        $this->assertStringContainsString("formData.append('_wpnonce', '" . $nonce . "');", $script);
        $this->assertStringContainsString('<h2 class="give-modal__title">', $script);
        $this->assertStringContainsString('<div class="give-modal__description">', $script);
        $this->assertStringContainsString("cancelBtnTitle: 'Read Documentation'", $script);
        $this->assertStringContainsString("window.location.assign('" . $settingsUrl . "');", $script);
        $this->assertStringNotContainsString('$modal', $script);
        $this->assertStringNotContainsString('\\"', $script);
    }
}

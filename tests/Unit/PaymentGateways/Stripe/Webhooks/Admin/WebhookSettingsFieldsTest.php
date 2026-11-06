<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Admin;

use Give\PaymentGateways\Stripe\Admin\WebhookSettingsFields;
use Give\PaymentGateways\Stripe\Webhooks\Controllers\EnsureWebhookEndpointsController;
use Give\PaymentGateways\Stripe\Webhooks\WebhookHealthMonitor;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
final class WebhookSettingsFieldsTest extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        /* The settings page helpers are loaded only on admin requests, and the test bootstrap is not one. */
        require_once GIVE_PLUGIN_DIR . 'includes/admin/setting-page-functions.php';
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $_GET = [];
        $_REQUEST = [];

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testItAddsBothFieldsBeforeTheEndOfTheWebhooksGroup(): void
    {
        $this->openSection('stripe-settings');
        $settings = $this->gatewaySettings();

        $fields = (new WebhookSettingsFields())->addFields($settings)['webhooks'];

        $expectedIds = array_merge(
            array_slice(array_column($settings['webhooks'], 'id'), 0, -1),
            ['stripe_webhook_setup', WebhookHealthMonitor::EMAIL_SETTING],
            array_slice(array_column($settings['webhooks'], 'id'), -1)
        );
        $this->assertSame($expectedIds, array_column($fields, 'id'));
        $this->assertSame(WebhookSettingsFields::SETUP_FIELD_TYPE, $fields[2]['type']);
        $this->assertSame('sectionend', end($fields)['type']);
    }

    /**
     * @since TBD
     */
    public function testItLeavesTheSettingsOfOtherSectionsAlone(): void
    {
        $this->openSection('paypal');
        $settings = $this->gatewaySettings();

        $this->assertSame($settings, (new WebhookSettingsFields())->addFields($settings));
    }

    /**
     * @since TBD
     */
    public function testItEnablesTheHealthEmailByDefault(): void
    {
        $this->openSection('stripe-settings');

        $fields = (new WebhookSettingsFields())->addFields($this->gatewaySettings())['webhooks'];
        $emailField = $fields[array_search(WebhookHealthMonitor::EMAIL_SETTING, array_column($fields, 'id'), true)];

        $this->assertSame('enabled', $emailField['default']);
        $this->assertSame(['enabled', 'disabled'], array_keys($emailField['options']));
    }

    /**
     * @since TBD
     */
    public function testItRendersTheSetupButtonWithANonceForTheController(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));

        ob_start();
        (new WebhookSettingsFields())->renderSetupField(['name' => 'Webhook Setup']);
        $html = ob_get_clean();

        $this->assertMatchesRegularExpression('/data-nonce="([^"]+)"/', $html);
        preg_match('/data-nonce="([^"]+)"/', $html, $matches);
        $this->assertNotFalse(wp_verify_nonce($matches[1], EnsureWebhookEndpointsController::ACTION));
        $this->assertStringContainsString('give-stripe-webhook-setup-button', $html);
    }

    /**
     * @since TBD
     */
    private function openSection(string $section): void
    {
        /* give_get_current_setting_section() reads $_REQUEST, which PHPUnit does not fill from $_GET. */
        $_GET['section'] = $section;
        $_REQUEST['section'] = $section;
    }

    /**
     * The Webhooks group as the Stripe admin settings register it.
     *
     * @since TBD
     */
    private function gatewaySettings(): array
    {
        return [
            'general' => [
                ['id' => 'give_title_stripe_general', 'type' => 'title'],
                ['id' => 'give_title_stripe_general', 'type' => 'sectionend'],
            ],
            'webhooks' => [
                ['id' => 'give_title_stripe_webhooks', 'type' => 'title'],
                ['id' => 'stripe_webhooks', 'type' => 'stripe_webhooks'],
                ['id' => 'give_title_stripe_webhooks', 'type' => 'sectionend'],
            ],
        ];
    }
}

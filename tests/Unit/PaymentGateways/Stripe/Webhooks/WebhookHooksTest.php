<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks;

use Give\PaymentGateways\Stripe\Admin\AccountManagerSettingField;
use Give\PaymentGateways\Stripe\Admin\WebhookSettingsFields;
use Give\PaymentGateways\Stripe\Webhooks\Controllers\EnsureWebhookEndpointsController;
use Give\PaymentGateways\Stripe\Webhooks\WebhookHealthMonitor;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;
use Give_Stripe_Admin_Settings;

/**
 * The hooks that the PaymentGateways service provider and the Stripe admin settings register once GiveWP boots.
 *
 * @since TBD
 */
final class WebhookHooksTest extends TestCase
{
    use UsesFakeStripeApi;

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
        set_current_screen('front');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testItRegistersTheAjaxActionForAdminsOnly(): void
    {
        $this->assertTrue(has_action('wp_ajax_' . EnsureWebhookEndpointsController::ACTION));
        $this->assertFalse(has_action('wp_ajax_nopriv_' . EnsureWebhookEndpointsController::ACTION));
    }

    /**
     * @since TBD
     */
    public function testItRunsTheHealthCheckOnTheCronHook(): void
    {
        $account = $this->makeAccount();
        $emails = [];
        give_update_option('_give_stripe_get_all_accounts', [$account->accountSlug => $account->toArray()]);
        add_filter('give_is_test_mode', '__return_true');
        add_filter('pre_wp_mail', static function ($short, array $email) use (&$emails) {
            $emails[] = $email;

            return true;
        }, 10, 2);
        $this->stripe->queue(200, $this->listBody([]));

        do_action(WebhookHealthMonitor::CRON_HOOK);

        $this->assertCount(1, $this->stripe->requests);
        $this->assertCount(1, $emails);
    }

    /**
     * @since TBD
     */
    public function testItAddsTheWebhookFieldsThroughTheGatewaySettingsFilter(): void
    {
        $this->openStripeSettings();
        $settings = [
            'webhooks' => [
                ['id' => 'give_title_stripe_webhooks', 'type' => 'title'],
                ['id' => 'give_title_stripe_webhooks', 'type' => 'sectionend'],
            ],
        ];

        $ids = array_column(apply_filters('give_get_settings_gateways', $settings)['webhooks'], 'id');

        $this->assertContains('stripe_webhook_setup', $ids);
        $this->assertContains(WebhookHealthMonitor::EMAIL_SETTING, $ids);
    }

    /**
     * @since TBD
     */
    public function testItRendersTheSetupFieldThroughItsFieldTypeAction(): void
    {
        ob_start();
        do_action('give_admin_field_' . WebhookSettingsFields::SETUP_FIELD_TYPE, ['name' => 'Webhook Setup']);
        $html = ob_get_clean();

        $this->assertStringContainsString('give-stripe-webhook-setup-button', $html);
    }

    /**
     * @since TBD
     */
    public function testTheStripeSettingsHaveAWebhooksGroupThatHoldsTheStripeWebhooksField(): void
    {
        $this->openStripeSettings();
        $stripeSettings = $this->stripeAdminSettings();

        $settings = $stripeSettings->register_settings([]);

        $this->assertArrayHasKey('webhooks', $stripeSettings->register_groups());
        $this->assertContains('stripe_webhooks', array_column($settings['webhooks'], 'id'));
        $this->assertNotContains('stripe_webhooks', array_column($settings['general'], 'id'));
    }

    /**
     * @since TBD
     */
    public function testTheAccountManagerModalCarriesANonceForTheController(): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $this->openStripeSettings();

        ob_start();
        give(AccountManagerSettingField::class)->handle(['wrapper_class' => '']);
        $html = ob_get_clean();

        $this->assertSame(1, preg_match('/data-webhook-nonce="([^"]+)"/', $html, $matches));
        $this->assertNotFalse(wp_verify_nonce($matches[1], EnsureWebhookEndpointsController::ACTION));
    }

    /**
     * @since TBD
     */
    private function openStripeSettings(): void
    {
        /* Give_Admin_Settings::is_setting_page() requires an admin request on the Stripe section of the settings page. */
        set_current_screen('dashboard');
        $_GET = [
            'post_type' => 'give_forms',
            'page' => 'give-settings',
            'tab' => 'gateways',
            'section' => 'stripe-settings',
        ];
        /* give_get_current_setting_section() reads $_REQUEST, which PHPUnit does not fill from $_GET. */
        $_REQUEST = $_GET;
    }

    /**
     * @since TBD
     */
    private function stripeAdminSettings(): Give_Stripe_Admin_Settings
    {
        /* The file is loaded only on admin requests, and the test bootstrap is not one. */
        require_once GIVE_PLUGIN_DIR . 'includes/gateways/stripe/includes/admin/class-give-stripe-admin-settings.php';

        return new Give_Stripe_Admin_Settings();
    }
}

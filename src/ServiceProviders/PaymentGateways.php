<?php

namespace Give\ServiceProviders;

use Give\Controller\PayPalWebhooks;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\PaymentGateways\Gateways\PayPalStandard\Migrations\RemovePayPalIPNVerificationSetting;
use Give\PaymentGateways\Gateways\PayPalStandard\Migrations\SetPayPalStandardGatewayId;
use Give\PaymentGateways\PayPalCommerce\AccountAdminNotices;
use Give\PaymentGateways\PayPalCommerce\AdvancedCardFields;
use Give\PaymentGateways\PayPalCommerce\AjaxRequestHandler;
use Give\PaymentGateways\PayPalCommerce\DonationDetailsPage;
use Give\PaymentGateways\PayPalCommerce\DonationFormPaymentMethod;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\onBoardingRedirectHandler;
use Give\PaymentGateways\PayPalCommerce\PayPalClient;
use Give\PaymentGateways\PayPalCommerce\RefreshToken;
use Give\PaymentGateways\PayPalCommerce\RefundPaymentHandler;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalAuth;
use Give\PaymentGateways\PayPalCommerce\Repositories\Settings;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give\PaymentGateways\PayPalCommerce\ScriptLoader;
use Give\PaymentGateways\PayPalCommerce\Webhooks\WebhookChecker;
use Give\PaymentGateways\PayPalCommerce\Webhooks\WebhookRegister;
use Give\PaymentGateways\PaypalSettingPage;
use Give\PaymentGateways\Stripe\Admin\AccountManagerSettingField;
use Give\PaymentGateways\Stripe\Admin\CreditCardSettingField;
use Give\PaymentGateways\Stripe\ApplicationFee;
use Give\PaymentGateways\Stripe\Controllers\DisconnectStripeAccountController;
use Give\PaymentGateways\Stripe\Controllers\GetStripeAccountDetailsController;
use Give\PaymentGateways\Stripe\Controllers\NewStripeAccountOnBoardingController;
use Give\PaymentGateways\Stripe\Controllers\SetDefaultStripeAccountController;
use Give\PaymentGateways\Stripe\DonationFormElements;
use Give\PaymentGateways\Stripe\DonationFormSettingPage;
use Give\PaymentGateways\Stripe\Repositories\AccountDetail as AccountDetailRepository;

/**
 * Class PaymentGateways
 *
 * The Service Provider for loading the Payment Gateways
 *
 * @since 2.8.0
 */
class PaymentGateways implements ServiceProvider
{
    /**
     * Array of SettingPage classes to be bootstrapped
     *
     * @var string[]
     */
    private $gatewaySettingsPages = [
        PaypalSettingPage::class,
    ];

    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->bind(
            'PAYPAL_COMMERCE_ATTRIBUTION_ID',
            static function () {
                return 'GiveWP_SP_PCP';
            }
        ); // storage

        give()->singleton(PayPalWebhooks::class);
        give()->singleton(Webhooks::class);
        give()->singleton(DonationFormElements::class);
        give()->singleton(
            ApplicationFee::class,
            function () {
                return new ApplicationFee(
                    give(AccountDetailRepository::class)->getAccountDetail(
                        give_stripe_get_connected_account_options()['stripe_account']
                    )
                );
            }
        );

        $this->registerPayPalCommerceClasses();
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        add_action('admin_init', [$this, 'handleSellerOnBoardingRedirect']);
        add_action('give-settings_start', [$this, 'registerPayPalSettingPage']);
        Hooks::addFilter('give_form_html_tags', DonationFormElements::class, 'addFormHtmlTags', 99);
        Hooks::addAction('wp_ajax_give_stripe_set_account_default', SetDefaultStripeAccountController::class);
        Hooks::addAction('wp_ajax_disconnect_stripe_account', DisconnectStripeAccountController::class);
        Hooks::addAction('wp_ajax_give_stripe_account_get_details', GetStripeAccountDetailsController::class);
        Hooks::addAction('admin_init', NewStripeAccountOnBoardingController::class);
        Hooks::addFilter('give_metabox_form_data_settings', DonationFormSettingPage::class, '__invoke', 10, 2);

        $this->registerMigrations();
        $this->registerStripeCustomFields();
        $this->registerPayPalCommerceHooks();
    }

    /**
     * Handle seller on boarding redirect.
     *
     * @since 2.8.0
     */
    public function handleSellerOnBoardingRedirect()
    {
        if( current_user_can('manage_give_settings') ) {
            give(onBoardingRedirectHandler::class)->boot();
        }
    }

    /**
     * Register all payment gateways setting pages with GiveWP.
     *
     * @since 2.8.0
     */
    public function registerPayPalSettingPage()
    {
        foreach ($this->gatewaySettingsPages as $page) {
            give()->make($page)->boot();
        }
    }

    /**
     * Registers the classes for the PayPal Commerce gateway
     *
     * @since 2.8.0
     */
    private function registerPayPalCommerceClasses()
    {
        give()->singleton(AdvancedCardFields::class);
        give()->singleton(PayPalClient::class);
        give()->singleton(RefreshToken::class);
        give()->singleton(AjaxRequestHandler::class);
        give()->singleton(ScriptLoader::class);
        give()->singleton(WebhookRegister::class);
        give()->singleton(Webhooks::class);
        give()->singleton(PayPalClient::class);
        give()->singleton(MerchantDetails::class);
        give()->singleton(PayPalAuth::class);
        give()->singleton(Settings::class);

        give()->singleton(
            MerchantDetail::class,
            static function () {
                /** @var MerchantDetails $repository */
                $repository = give(MerchantDetails::class);

                return $repository->getDetails();
            }
        );

        give()->resolving(
            MerchantDetails::class,
            static function (MerchantDetails $details) {
                $details->setMode(give_is_test_mode() ? 'sandbox' : 'live');
            }
        );

        give()->resolving(
            Webhooks::class,
            static function (Webhooks $repository) {
                $repository->setMode(give_is_test_mode() ? 'sandbox' : 'live');
            }
        );

        give()->resolving(
            Settings::class,
            static function (Settings $repository) {
                $repository->setMode(give_is_test_mode() ? 'sandbox' : 'live');
            }
        );

        give()->resolving(
            PayPalClient::class,
            static function (PayPalClient $object) {
                $object->setMode(give_is_test_mode() ? 'sandbox' : 'live');
            }
        );
    }

    /**
     * Register migrations
     *
     * @since 2.9.1
     */
    private function registerMigrations()
    {
        /* @var MigrationsRegister $migrationRegisterer */
        $migrationRegisterer = give(MigrationsRegister::class);

        $migrationRegisterer->addMigration(SetPayPalStandardGatewayId::class);
        $migrationRegisterer->addMigration(RemovePayPalIPNVerificationSetting::class);
    }

    /**
     * @since 2.13.0
     */
    private function registerStripeCustomFields()
    {
        Hooks::addAction('give_admin_field_stripe_account_manager', AccountManagerSettingField::class, 'handle');
        Hooks::addAction('give_admin_field_stripe_credit_card_format', CreditCardSettingField::class, 'handle', 10, 2);
    }

    /**
     * Register action/filter hooks for paypal commerce.
     *
     * @since 2.19.0
     */
    private function registerPayPalCommerceHooks()
    {
        Hooks::addAction(
            'wp_ajax_give_paypal_commerce_user_on_boarded',
            AjaxRequestHandler::class,
            'onBoardedUserAjaxRequestHandler'
        );
        Hooks::addAction(
            'wp_ajax_give_paypal_commerce_get_partner_url',
            AjaxRequestHandler::class,
            'onGetPartnerUrlAjaxRequestHandler'
        );
        Hooks::addAction(
            'wp_ajax_give_paypal_commerce_disconnect_account',
            AjaxRequestHandler::class,
            'removePayPalAccount'
        );
        Hooks::addAction('wp_ajax_give_paypal_commerce_create_order', AjaxRequestHandler::class, 'createOrder');
        Hooks::addAction(
            'wp_ajax_give_paypal_commerce_onboarding_trouble_notice',
            AjaxRequestHandler::class,
            'onBoardingTroubleNotice'
        );
        Hooks::addAction('wp_ajax_nopriv_give_paypal_commerce_create_order', AjaxRequestHandler::class, 'createOrder');
        Hooks::addAction('wp_ajax_give_paypal_commerce_approve_order', AjaxRequestHandler::class, 'approveOrder');
        Hooks::addAction(
            'wp_ajax_nopriv_give_paypal_commerce_approve_order',
            AjaxRequestHandler::class,
            'approveOrder'
        );

        Hooks::addAction('wp_ajax_give_paypal_commerce_update_order_amount', AjaxRequestHandler::class,
            'updateOrderAmount');
        Hooks::addAction(
            'wp_ajax_nopriv_give_paypal_commerce_update_order_amount',
            AjaxRequestHandler::class,
            'updateOrderAmount'
        );

        Hooks::addAction('admin_enqueue_scripts', ScriptLoader::class, 'loadAdminScripts');
        Hooks::addAction('wp_enqueue_scripts', ScriptLoader::class, 'loadPublicAssets');
        Hooks::addAction('give_pre_form_output', DonationFormPaymentMethod::class, 'handle');

        Hooks::addAction('give_paypal_commerce_refresh_sandbox_token', RefreshToken::class, 'cronJobRefreshToken');
        Hooks::addAction('give_paypal_commerce_refresh_live_token', RefreshToken::class, 'cronJobRefreshToken');

        Hooks::addAction('admin_init', AccountAdminNotices::class, 'displayNotices');
        Hooks::addFilter(
            'give_payment_details_transaction_id-paypal-commerce',
            DonationDetailsPage::class,
            'getPayPalPaymentUrl'
        );

        Hooks::addAction('give_update_edited_donation', RefundPaymentHandler::class, 'refundPayment');
        Hooks::addAction('admin_notices', RefundPaymentHandler::class, 'showPaymentRefundFailureNotice');
        Hooks::addAction(
            'give_view_donation_details_totals_after',
            RefundPaymentHandler::class,
            'optInForRefundFormField'
        );

        Hooks::addAction('admin_init', WebhookChecker::class, 'checkWebhookCriteria');
    }
}

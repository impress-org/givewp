<?php

namespace Give\PaymentGateways\Stripe\Controllers;

use Give\PaymentGateways\Stripe\DataTransferObjects\NewStripeAccountOnBoardingDto;
use Give\PaymentGateways\Stripe\Models\AccountDetail as AccountDetailModel;
use Give\PaymentGateways\Stripe\Repositories\Settings;
use Give_Admin_Settings;
use Stripe\Stripe;

/**
 * Class NewStripeAccountOnBoardingController
 * @package Give\PaymentGateways\Stripe\Controllers
 *
 * @since 2.13.0
 */
class NewStripeAccountOnBoardingController
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @since 3.4.0 Handle Stripe connect account on-boarding redirect on specific pages.
     *
     * @since 2.13.0
     */
    public function __invoke()
    {
        if (! current_user_can('manage_give_settings')) {
            return;
        }

        if (wp_doing_ajax() || ! $this->canProcessRequestOnCurrentPage($_SERVER['REQUEST_URI'])) {
            return;
        }

        $requestedData = NewStripeAccountOnBoardingDto::fromArray(give_clean($_GET));

        if (! $requestedData->hasValidateData()) {
            return;
        }

        $stripe_accounts = give_stripe_get_all_accounts();
        $secret_key = ! give_is_test_mode() ? $requestedData->stripeAccessToken : $requestedData->stripeAccessTokenTest;

        Stripe::setApiKey($secret_key);

        // Get Account Details.
        $account_details = give_stripe_get_account_details($requestedData->stripeUserId);

        // Setup Account Details for Connected Stripe Accounts.
        if (empty($account_details->id)) {
            Give_Admin_Settings::add_error(
                'give-stripe-account-id-fetching-error',
                sprintf(
                    '<strong>%1$s</strong> %2$s',
                    esc_html__('Stripe Error:', 'give'),
                    esc_html__(
                        'We are unable to connect your Stripe account. Please contact the support team for assistance.',
                        'give'
                    )
                )
            );

            return;
        }

        $account_name = ! empty($account_details->business_profile->name) ?
            $account_details->business_profile->name :
            $account_details->settings->dashboard->display_name;
        $account_slug = $account_details->id;
        $account_email = $account_details->email;
        $account_country = $account_details->country;

        // Set first Stripe account as default.
        if (! $stripe_accounts) {
            give_update_option('_give_stripe_default_account', $account_slug);
        }

        try {
            $accountDetailModel = AccountDetailModel::fromArray(
                [
                    'type' => 'connect',
                    'account_name' => $account_name,
                    'account_slug' => $account_slug,
                    'account_email' => $account_email,
                    'account_country' => $account_country,
                    'account_id' => $requestedData->stripeUserId,
                    'live_secret_key' => $requestedData->stripeAccessToken,
                    'test_secret_key' => $requestedData->stripeAccessTokenTest,
                    'live_publishable_key' => $requestedData->stripePublishableKey,
                    'test_publishable_key' => $requestedData->stripePublishableKeyTest,
                    'statement_descriptor' => $account_details->settings->payments->statement_descriptor,
                ]
            );

            $this->settings->addNewStripeAccount($accountDetailModel);

            if ($requestedData->formId) {
                if (! Settings::getDefaultStripeAccountSlugForDonationForm($requestedData->formId)) {
                    Settings::setDefaultStripeAccountSlugForDonationForm(
                        $requestedData->formId,
                        $accountDetailModel->accountSlug
                    );
                }

                give()->form_meta->update_meta(
                    $requestedData->formId,
                    'give_stripe_per_form_accounts',
                    'enabled'
                );
            }

            wp_redirect(
                esc_url_raw(
                    add_query_arg(
                        ['stripe_account' => 'connected'],
                        $requestedData->formId ?
                            admin_url(
                                "post.php?post=$requestedData->formId&action=edit&give_tab=stripe_form_account_options"
                            ) :
                            admin_url(
                                'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings'
                            )
                    )
                )
            );
            exit();
        } catch (\Exception $e) {
            Give_Admin_Settings::add_error(
                'give-stripe-account-on-boarding-error',
                sprintf(
                    '<strong>%1$s</strong> %2$s',
                    esc_html__('Stripe Error:', 'give'),
                    esc_html__(
                        'We are unable to connect your Stripe account. Please contact the support team for assistance.',
                        'give'
                    )
                )
            );

            return;
        }
    }

    /**
     * Check if the request can be processed on the current page.
     *
     * Admin redirect to following page:
     *  1. GiveWP stripe settings page.
     *  2. V2 donation form edit form.
     *
     * @since 3.4.0
     */
    protected function canProcessRequestOnCurrentPage(string $url): bool
    {
        // Check if request is from edit.php or post.php page.
        if (false === strpos($url, 'wp-admin/post.php') && false === strpos($url, 'wp-admin/edit.php')) {
            return false;
        }

        $path = wp_parse_url($url);

        // Result should be in array and should have query string.
        if (! is_array($path) || ! isset($path['query'])) {
            return false;
        }

        $queryParams = wp_parse_args($path['query']);

        if (empty($queryParams)) {
            return false;
        }

        // Check if request is from V2 donation form edit page.
        $isDonationFormPage = isset($queryParams['post'], $queryParams['give_tab'])
                              && get_post_type(absint($queryParams['post'])) === 'give_forms'
                              && $queryParams['give_tab'] === 'stripe_form_account_options';

        // Check if request is from GiveWP stripe settings page.
        $isSettingPage = isset($queryParams['page'], $queryParams['post_type'], $queryParams['tab'], $queryParams['section'])
                         && $queryParams['post_type'] === 'give_forms'
                         && $queryParams['page'] === 'give-settings'
                         && $queryParams['tab'] === 'gateways'
                         && $queryParams['section'] === 'stripe-settings';

        return $isDonationFormPage || $isSettingPage;
    }
}

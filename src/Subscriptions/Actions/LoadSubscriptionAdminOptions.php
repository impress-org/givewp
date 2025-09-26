<?php

namespace Give\Subscriptions\Actions;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\BetaFeatures\Facades\FeatureFlag;
use Give\Framework\Database\DB;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * The purpose of this action is to have a centralized place for localizing options used on many different places
 * by donation scripts (list tables, blocks, etc.)
 *
 * @since 4.8.0
 */
class LoadSubscriptionAdminOptions
{
    public function __invoke()
    {
        wp_register_script('give-subscription-options', false);
        wp_localize_script('give-subscription-options', 'GiveSubscriptionOptions', $this->getSubscriptionOptions());
        wp_enqueue_script('give-subscription-options');
    }

    /**
     * Get all donation options for localization
     *
     * @unreleased Added campaigns with forms option
     * @since 4.8.0
     */
    private function getSubscriptionOptions(): array
    {
        $isAdmin = is_admin();

        return [
            'isAdmin' => $isAdmin,
            'adminUrl' => admin_url(),
            'pluginUrl' => GIVE_PLUGIN_URL,
            'apiRoot' => rest_url(DonationRoute::NAMESPACE),
            'legacyApiRoot' => esc_url_raw(rest_url('give-api/v2/admin')),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'syncSubscriptionNonce' => wp_create_nonce( 'sync-subscription-details' ),
            'subscriptionsAdminUrl' => admin_url('edit.php?post_type=give_forms&page=give-subscriptions'),
            'currency' => give_get_currency(),
            'subscriptionStatuses' => SubscriptionStatus::labels(),
            'campaignsWithForms' => $this->getCampaignsWithForms(),
            'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION') ? GIVE_RECURRING_VERSION : null,
            'eventTicketsEnabled' => FeatureFlag::eventTickets(),
            'isFeeRecoveryEnabled' => defined('GIVE_FEE_RECOVERY_VERSION'),
            'mode' => give_is_test_mode() ? 'test' : 'live',
            'gateways' => $this->getGateways(),
        ];
    }

    /**
     * Get gateways
     *
     * @since 4.8.0
     */
    private function getGateways(): array
    {
        $enabledGateways = array_keys(give_get_enabled_payment_gateways());

        $gateways = array_map(static function ($gatewayClass) use ($enabledGateways) {
            /** @var PaymentGateway $gateway */
            $gateway = give($gatewayClass);

            return [
                'id' => $gateway::id(),
                'enabled' => in_array($gateway::id(), $enabledGateways, true),
                'label' => $gateway->getName(),
                'supportsSubscriptions' => $gateway->supportsSubscriptions(),
                'supportsRefund' => $gateway->supportsRefund(),
            ];
        }, give()->gateways->getPaymentGateways());

        return array_values($gateways);
    }

    /**
     * Get campaigns with their forms using Campaign query builder
     *
     * @unreleased
     */
    private function getCampaignsWithForms(): array
    {
        $campaignsWithForms = [];

        $results = DB::table('give_campaigns', 'campaigns')
            ->select(
                ['campaigns.id', 'campaignId'],
                ['campaigns.campaign_title', 'campaignTitle'],
                ['campaigns.form_id', 'defaultFormId'],
                ['posts.ID', 'formId'],
                ['posts.post_title', 'formTitle']
            )
            ->leftJoin('give_campaign_forms', 'campaigns.id', 'campaign_forms.campaign_id', 'campaign_forms')
            ->leftJoin('posts', 'campaign_forms.form_id', 'posts.ID', 'posts')
            ->where('posts.post_type', 'give_forms')
            ->orderBy('campaigns.id', 'DESC')
            ->orderBy('posts.ID', 'DESC')
            ->getAll(ARRAY_A);

        foreach ($results as $row) {
            [
                'campaignId' => $campaignId,
                'campaignTitle' => $campaignTitle,
                'defaultFormId' => $defaultFormId,
                'formId' => $formId,
                'formTitle' => $formTitle
            ] = $row;

            if (!isset($campaignsWithForms[$campaignId])) {
                $campaignsWithForms[$campaignId] = [
                    'title' => $campaignTitle,
                    'defaultFormId' => $defaultFormId,
                    'forms' => []
                ];
            }

            if ($formId && $formTitle) {
                $campaignsWithForms[$campaignId]['forms'][$formId] = $formTitle;
            }
        }

        return $campaignsWithForms;
    }
}

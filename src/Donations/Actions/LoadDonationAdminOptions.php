<?php

namespace Give\Donations\Actions;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Helpers\IntlTelInput;
use Give\BetaFeatures\Facades\FeatureFlag;
use Give\Framework\Database\DB;
use Give\Framework\PaymentGateways\PaymentGateway;

/**
 * The purpose of this action is to have a centralized place for localizing options used on many different places
 * by donation scripts (list tables, blocks, etc.)
 *
 * @since 4.6.1 Rename to LoadDonationAdminOptions
 * @since 4.6.0
 */
class LoadDonationAdminOptions
{
    public function __invoke()
    {
        wp_register_script('give-donation-options', false);
        wp_localize_script('give-donation-options', 'GiveDonationOptions', $this->getDonationOptions());
        wp_enqueue_script('give-donation-options');
    }

    /**
     * Get all donation options for localization
     *
     * @since 4.7.0 removed donors from the options
     * @since 4.6.0
     */
    private function getDonationOptions(): array
    {
        $isAdmin = is_admin();

        return [
            'isAdmin' => $isAdmin,
            'adminUrl' => admin_url(),
            'apiRoot' => rest_url(DonationRoute::NAMESPACE),
            'apiNonce' => wp_create_nonce('wp_rest'),
            'donationsAdminUrl' => admin_url('edit.php?post_type=give_forms&page=give-payment-history'),
            'currency' => give_get_currency(),
            'intlTelInputSettings' => IntlTelInput::getSettings(),
            'nameTitlePrefixes' => give_get_option('title_prefixes', array_values(give_get_default_title_prefixes())),
            'countries' => $this->decodeHtmlEntities(give_get_country_list()),
            'states' => $this->getStatesData(),
            'donationStatuses' => DonationStatus::labels(),
            'campaignsWithForms' => $this->getCampaignsWithForms(),
            'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION') ? GIVE_RECURRING_VERSION : null,
            'admin' => $isAdmin ? [] : null,
            'eventTicketsEnabled' => FeatureFlag::eventTickets(),
            'isFeeRecoveryEnabled' => defined('GIVE_FEE_RECOVERY_VERSION'),
            'mode' => give_is_test_mode() ? 'test' : 'live',
            'gateways' => $this->getGateways(),
        ];
    }

    /**
     * Get states data with decoded HTML entities
     *
     * @since 4.6.0
     */
    private function getStatesData(): array
    {
        return [
            'list' => $this->decodeHtmlEntities(give_states_list(), true),
            'labels' => give_get_states_label(),
            'noStatesCountries' => array_keys(give_no_states_country_list()),
            'statesNotRequiredCountries' => array_keys(give_states_not_required_country_list()),
        ];
    }

    /**
     * Get campaigns with their forms using Campaign query builder
     *
     * @since 4.6.0
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

    /**
     * Decode HTML entities from an array of strings or nested arrays
     *
     * @since 4.6.0
     */
    private function decodeHtmlEntities(array $data, bool $isNested = false): array
    {
        if ($isNested) {
            return array_map(function($nestedData) {
                return $this->decodeHtmlEntities($nestedData);
            }, $data);
        }

        return array_map(function($item) {
            return html_entity_decode($item, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
        }, $data);
    }

    /**
     * Get gateways
     *
     * @since 4.6.0
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
}

<?php

namespace Give\FormBuilder\ViewModels;

use Give\DonationForms\Actions\GenerateDonationFormPreviewRouteUrl;
use Give\DonationForms\Models\DonationForm;
use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\Framework\FormDesigns\FormDesign;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Framework\PaymentGateways\Contracts\NextGenPaymentGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;

class FormBuilderViewModel
{
    /**
     * @since 0.1.0
     */
    public function storageData(int $donationFormId): array
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::find($donationFormId);

        return [
            'resourceURL' => rest_url(FormBuilderRestRouteConfig::NAMESPACE . '/form/' . $donationFormId),
            'previewURL' => (new GenerateDonationFormPreviewRouteUrl())($donationFormId),
            'nonce' => wp_create_nonce('wp_rest'),
            'blockData' => $donationForm->blocks->toJson(),
            'settings' => $donationForm->settings->toJson(),
            'currency' => give_get_currency(),
            'formDesigns' => array_map(static function ($designClass) {
                /** @var FormDesign $design */
                $design = give($designClass);

                return [
                    'id' => $design::id(),
                    'name' => $design::name(),
                ];
            }, give(FormDesignRegistrar::class)->getDesigns()),
            'formPage' => [
                'isEnabled' => give_is_setting_enabled(give_get_option('forms_singular')), // Note: Boolean values must be nested in an array to maintain boolean type, see \WP_Scripts::localize().
                'permalink' => add_query_arg(['p' => $donationFormId], site_url('?post_type=give_forms')),
                'rewriteSlug' => get_post_type_object('give_forms')->rewrite['slug'],
            ],
        ];
    }

    /**
     * @unreleased
     */
    public function jsPathFromPluginRoot(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/formBuilderApp.js';
    }

    /**
     * @unreleased
     */
    public function jsPathToRegistrars(): string
    {
        return GIVE_NEXT_GEN_URL . 'build/formBuilderRegistrars.js';
    }

    /**
     * @unreleased
     */
    public function jsDependencies(): array
    {
        $scriptAsset = require GIVE_NEXT_GEN_DIR . 'build/formBuilderApp.asset.php';

        return array_merge($scriptAsset['dependencies'], ['@givewp/form-builder/registrars']);
    }

    /**
     * @unreleased
     */
    public function getGateways(): array
    {
        $enabledGateways = array_keys(give_get_option('gateways'));

        $supportedGateways = array_filter(
            give(PaymentGatewayRegister::class)->getPaymentGateways(),
            static function ($gateway) {
                return is_a($gateway, NextGenPaymentGatewayInterface::class, true);
            }
        );

        $builderPaymentGatewayData = array_map(static function ($gatewayClass) use ($enabledGateways) {
            $gateway = give($gatewayClass);
            return [
                'id' => $gateway::id(),
                'enabled' => in_array($gateway::id(), $enabledGateways, true),
                'label' => give_get_gateway_checkout_label($gateway::id()) ?? $gateway->getPaymentMethodLabel(),
                'supportsSubscriptions' => $gateway->supportsSubscriptions(),
            ];
        }, $supportedGateways);

        return array_values($builderPaymentGatewayData);
    }
}

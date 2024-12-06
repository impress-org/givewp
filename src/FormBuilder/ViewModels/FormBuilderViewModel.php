<?php

namespace Give\FormBuilder\ViewModels;

use Give\DonationForms\Actions\GenerateDonationFormPreviewRouteUrl;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\GoalProgressType;
use Give\DonationForms\ValueObjects\GoalType;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorMetaKeys;
use Give\FormBuilder\DataTransferObjects\EmailNotificationData;
use Give\FormBuilder\ValueObjects\FormBuilderRestRouteConfig;
use Give\Framework\FormDesigns\FormDesign;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\IntlTelInput;
use Give\Subscriptions\Models\Subscription;

class FormBuilderViewModel
{
    /**
     * @since 3.12.0  Add goalProgressOptions key to the returned array
     * @since 3.9.0 Add support to intlTelInputSettings key in the returned array
     * @since      3.7.0 Add support to isExcerptEnabled key in the returned array
     * @since 3.2.0 Add nameTitlePrefixes key to the returned array
     * @since 3.0.0
     */
    public function storageData(int $donationFormId): array
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::find($donationFormId);

        return [
            'formId' => $donationFormId,
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
                    'isMultiStep' => $design->isMultiStep(),
                ];
            }, give(FormDesignRegistrar::class)->getDesigns()),
            'formPage' => [
                'isEnabled' => give_is_setting_enabled(give_get_option('forms_singular')),
                // Note: Boolean values must be nested in an array to maintain boolean type, see \WP_Scripts::localize().
                'permalink' => add_query_arg(['p' => $donationFormId], site_url('?post_type=give_forms')),
                'rewriteSlug' => get_post_type_object('give_forms')->rewrite['slug'],
                'baseUrl' => preg_replace('/^https?:\/\//', '', site_url()),
            ],
            'gateways' => $this->getGateways(),
            'gatewaySettingsUrl' => admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways'),
            'isRecurringEnabled' => defined('GIVE_RECURRING_VERSION') ? GIVE_RECURRING_VERSION : null,
            'recurringAddonData' => [
                'isInstalled' => defined('GIVE_RECURRING_VERSION'),
            ],
            'formFieldManagerData' => [
                'isInstalled' => defined('GIVE_FFM_VERSION'),
            ],
            'emailTemplateTags' => $this->getEmailTemplateTags(),
            'emailNotifications' => array_map(static function ($notification) {
                return EmailNotificationData::fromLegacyNotification($notification);
            }, apply_filters('give_email_notification_options_metabox_fields', [], $donationFormId)),
            'emailPreviewURL' => rest_url('givewp/form-builder/email-preview'),
            'emailDefaultAddress' => get_option('admin_email'),
            'disallowedFieldNames' => $this->getDisallowedFieldNames(),
            'donationConfirmationTemplateTags' => $this->getDonationConfirmationPageTemplateTags(),
            'termsAndConditions' => [
                'checkboxLabel' => give_get_option('agree_to_terms_label'),
                'agreementText' => give_get_option('agreement_text'),
            ],
            'goalTypeOptions' => $this->getGoalTypeOptions(),
            'goalProgressOptions' => $this->getGoalProgressOptions(),
            'nameTitlePrefixes' => give_get_option('title_prefixes'),
            'isExcerptEnabled' => give_is_setting_enabled(give_get_option('forms_excerpt')),
            'intlTelInputSettings' => IntlTelInput::getSettings(),
        ];
    }

    /**
     * @since 3.0.0
     */
    public function isRecurringEnabled(): bool
    {
        return defined('GIVE_RECURRING_VERSION') && GIVE_RECURRING_VERSION;
    }

    /**
     * @since 3.0.0
     */
    public function getGoalTypeOption(
        string $value,
        string $label,
        string $description,
        bool $isCurrency = false
    ): array
    {
        return [
            'value' => $value,
            'label' => $label,
            'description' => $description,
            'isCurrency' => $isCurrency,
        ];
    }

    /**
     * @since 3.12.0
     */
    public function getGoalProgressOption(
        string $value,
        string $label,
        string $description
    ): array
    {
        return [
            'value' => $value,
            'label' => $label,
            'description' => $description
        ];
    }


    /**
     * @since 3.0.0
     */
    public function getGoalTypeOptions(): array
    {
        $options = [
            $this->getGoalTypeOption(
                GoalType::AMOUNT,
                __('Amount Raised', 'give'),
                __('The total amount raised for the form', 'give'),
                true
            ),
            $this->getGoalTypeOption(
                GoalType::DONATIONS,
                __('Number of Donations', 'give'),
                __('The total number of donations made for the form', 'give')
            ),
            $this->getGoalTypeOption(
                GoalType::DONORS,
                __('Number of Donors', 'give'),
                __('The total number of unique donors who have donated to the form', 'give')
            ),
        ];

        if ($this->isRecurringEnabled()) {
            return array_merge($options, [
                $this->getGoalTypeOption(
                    GoalType::AMOUNT_FROM_SUBSCRIPTIONS,
                    __('Subscription Amount Raised', 'give'),
                    __('The total amount raised for the form through subscriptions', 'give'),
                    true
                ),
                $this->getGoalTypeOption(
                    GoalType::SUBSCRIPTIONS,
                    __('Number of Subscriptions', 'give'),
                    __('The total number of subscriptions made for the form', 'give')
                ),
                $this->getGoalTypeOption(
                    GoalType::DONORS_FROM_SUBSCRIPTIONS,
                    __('Number of Subscribers', 'give'),
                    __('The total number of unique donors who have donated to the form through subscriptions', 'give')
                ),
            ]);
        }

        return $options;
    }

    /**
     * @since 3.12.0
     */
    public function getGoalProgressOptions(): array
    {
        return [
            $this->getGoalProgressOption(
                GoalProgressType::ALL_TIME,
                __('All time', 'give'),
                __('Displays the goal progress for a lifetime, starting from when this form was published.', 'give')
            ),
            $this->getGoalProgressOption(
                GoalProgressType::CUSTOM,
                __('Custom', 'give'),
                __('Displays the goal progress from the start date to the end date.', 'give')
            )
        ];
    }

    /**
     * @since 3.0.0
     */
    public function getEmailTemplateTags(array $tags = []): array
    {
        return array_map(static function ($tag) {
            $tag['id'] = $tag['tag'];
            $tag['desc'] = html_entity_decode($tag['desc'], ENT_QUOTES);
            $tag['description'] = html_entity_decode($tag['description'], ENT_QUOTES);

            return $tag;
        }, array_merge($tags, array_values(give()->email_tags->get_tags())));
    }

    /**
     * @since 3.0.0
     */
    public function getDonationConfirmationPageTemplateTags(): array
    {
        $templateTags = $this->getEmailTemplateTags([
            [
                'tag' => 'first_name',
                'desc' => __('The first name supplied by the donor during their donation.', 'give'),
                'description' => __('The first name supplied by the donor during their donation.', 'give'),
                'func' => null,
                "context" => 'donation',
            ],
            [
                'tag' => 'last_name',
                'desc' => __('The last name supplied by the donor during their donation.', 'give'),
                'description' => __('The last name supplied by the donor during their donation.', 'give'),
                'func' => null,
                "context" => 'donation',
            ],
            [
                'tag' => 'email',
                'desc' => __('The email supplied by the donor during their donation.', 'give'),
                'description' => __('The email supplied by the donor during their donation.', 'give'),
                'func' => null,
                "context" => 'donation',
            ],
        ]);

        $supportedContexts = [
            "general",
            "form",
            "donation",
            "donor",
            "subscription",
        ];

        array_multisort($templateTags, SORT_ASC);

        return array_values(
            array_filter($templateTags, static function ($tag) use ($supportedContexts) {
                return !empty($tag['description']) && in_array((string)$tag['context'], $supportedContexts, true);
            })
        );
    }

    /**
     * @since 3.0.0
     */
    public function jsPathFromPluginRoot(): string
    {
        return GIVE_PLUGIN_URL . 'build/formBuilderApp.js';
    }

    /**
     * @since 3.0.0
     */
    public function jsPathToRegistrars(): string
    {
        return GIVE_PLUGIN_URL . 'build/formBuilderRegistrars.js';
    }

    /**
     * @since 3.0.0
     */
    public function jsRegistrarsDependencies(): array
    {
        return ScriptAsset::getDependencies(GIVE_PLUGIN_DIR . 'build/formBuilderRegistrars.asset.php');
    }

    /**
     * @since 3.0.0
     */
    public function jsDependencies(): array
    {
        $dependencies = ScriptAsset::getDependencies(GIVE_PLUGIN_DIR . 'build/formBuilderApp.asset.php');

        return array_merge($dependencies, ['@givewp/form-builder/registrars']);
    }

    /**
     * @since 3.0.0
     */
    public function getGateways(): array
    {
        $enabledGateways = array_keys(give_get_option('gateways_v3', []));

        $builderPaymentGatewayData = array_map(static function ($gatewayClass) use ($enabledGateways) {
            /** @var PaymentGateway $gateway */
            $gateway = give($gatewayClass);

            return [
                'id' => $gateway::id(),
                'enabled' => in_array($gateway::id(), $enabledGateways, true),
                'label' => give_get_gateway_checkout_label($gateway::id(), 3) ?? $gateway->getPaymentMethodLabel(),
                'supportsSubscriptions' => $gateway->supportsSubscriptions(),
            ];
        }, give()->gateways->getPaymentGateways(3));

        return array_values($builderPaymentGatewayData);
    }

    /**
     * In the Form Builder custom fields have meta keys. These meta keys are used for both the field name and the meta,
     * as not to be too confusing. This array is used to prevent the user from creating meta keys that conflict with the
     * existing meta or field names.
     *
     * @since 3.0.0
     */
    protected function getDisallowedFieldNames(): array
    {
        $disallowedFieldNames = array_merge(
            Donation::propertyKeys(),
            array_values(DonationMetaKeys::toArray()),
            Donor::propertyKeys(),
            array_values(DonorMetaKeys::toArray()),
            Subscription::propertyKeys(),
            [
                'fund_id',
                'login',
                'consent',
                'donation-summary',
            ]
        );

        return array_values(array_unique($disallowedFieldNames));
    }
}

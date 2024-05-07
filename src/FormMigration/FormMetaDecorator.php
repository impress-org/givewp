<?php

namespace Give\FormMigration;

use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\ValueObjects\GoalType;
use Give\FormMigration\Contracts\FormModelDecorator;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give_Email_Notification_Util;

class FormMetaDecorator extends FormModelDecorator
{
    /**
     * @var DonationForm
     */
    protected $form;

    public function __construct(DonationForm $form)
    {
        $this->form = $form;
    }

    public function isLastNameRequired(): bool
    {
        return give_is_last_name_required($this->form->id);
    }

    public function isNameTitlePrefixEnabled(): bool
    {
        return give_is_name_title_prefix_enabled($this->form->id);
    }

    public function getNameTitlePrefixes(): array
    {
        return give_get_name_title_prefixes($this->form->id);
    }

    public function isUserRegistrationEnabled(): bool
    {
        // @note In v3 all donors are registered as users, so no need for a registration setting.
        return in_array(
            give_get_meta($this->form->id, '_give_show_register_form', true),
            ['registration', 'login', 'both']
        );
    }

    public function isUserLoginRequired(): bool
    {
        return ! $this->isGuestDonationsEnabled();
    }

    public function isGuestDonationsEnabled(): bool
    {
        // @note The "Guest Donation" setting corresponds to the `_give_logged_in_only` meta, which seems backwards.
        return give_is_setting_enabled(
            give_get_meta($this->form->id, '_give_logged_in_only', true)
        );
    }

    public function isCompanyFieldEnabled(): bool
    {
        return give_is_company_field_enabled($this->form->id);
    }

    public function isCompanyFieldRequired(): bool
    {
        // @note Forked from give/includes/process-donation.php:718
        if (give_is_company_field_enabled($this->form->id)) {
            $form_option = give_get_meta($this->form->id, '_give_company_field', true);
            $global_setting = give_get_option('company_field');

            if ( ! empty($form_option) && give_is_setting_enabled($form_option, ['required'])) {
                return true;
            } elseif ('global' === $form_option && give_is_setting_enabled($global_setting, ['required'])) {
                return true;
            } elseif (empty($form_option) && give_is_setting_enabled($global_setting, ['required'])) {
                return true;
            }
        }

        return false;
    }

    public function getFormTemplate(): string
    {
        return give_get_meta($this->form->id, '_give_form_template', true);
    }

    public function getFormTemplateSettings(): array
    {
        $template = $this->getFormTemplate();

        $templateSettings = give_get_meta($this->form->id, "_give_{$template}_form_template_settings", true);

        return is_array($templateSettings) ? $templateSettings : [];
    }

    public function isDonationGoalEnabled(): bool
    {
        return give_is_setting_enabled(
            give_get_meta($this->form->id, '_give_goal_option', true)
        );
    }

    public function getDonationGoalType(): GoalType
    {
        $onlyRecurringEnabled = $this->isGoalCountingOnlyRecurringDonations();

        switch (give_get_form_goal_format($this->form->id)) {
            case 'donors':
                return $onlyRecurringEnabled ? GoalType::DONORS_FROM_SUBSCRIPTIONS() : GoalType::DONORS();
            case 'donation': // @note v2: Singular
                return $onlyRecurringEnabled ? GoalType::SUBSCRIPTIONS() : GoalType::DONATIONS();
            // @note v3: Plural
            case 'amount':
            case 'percentage': // @note `percentage` is not supported in v3 - defaulting to `amount`
            default:
                return $onlyRecurringEnabled ? GoalType::AMOUNT_FROM_SUBSCRIPTIONS() : GoalType::AMOUNT();
        }
    }

    /**
     * @return mixed Goal of the form
     */
    public function getDonationGoalAmount()
    {
        return give_get_form_goal($this->form->id);
    }

    public function isAutoClosedEnabled()
    {
        return give_is_setting_enabled(
            give_get_meta($this->form->id, '_give_close_form_when_goal_achieved', true, 'disabled')
        );
    }

    public function getGoalAchievedMessage()
    {
        return give_get_meta($this->form->id, '_give_form_goal_achieved_message', true);
    }

    public function isTermsEnabled()
    {
        return give_is_terms_enabled($this->form->id);
    }

    public function getTermsAgreementLabel()
    {
        // @note Forked from give/includes/forms/template.php:1845
        return ($label = give_get_meta($this->form->id, '_give_agree_label', true))
            ? stripslashes($label)
            : esc_html__('Agree to Terms?', 'give');
    }

    public function getTermsAgreementText()
    {
        return give_get_meta($this->form->id, '_give_agree_text', true);
    }

    public function isOfflineDonationsCustomized()
    {
        return give_is_setting_enabled(
            give_get_meta($this->form->id, '_give_customize_offline_donations', true),
            'custom'
        );
    }

    public function isOfflineDonationsBillingFieldEnabled()
    {
        return give_is_setting_enabled(
            give_get_meta($this->form->id, '_give_offline_donation_enable_billing_fields_single', true)
        );
    }

    public function getOfflineDonationInstructions()
    {
        return give_get_meta($this->form->id, '_give_offline_checkout_notes', true);
    }

    public function isFormGridCustomized()
    {
        return give_is_setting_enabled(
            give_get_meta($this->form->id, '_give_form_grid_option', true),
            'custom'
        );
    }

    public function getFormGridRedirectUrl()
    {
        return give_get_meta($this->form->id, '_give_form_grid_redirect_url', true);
    }

    public function getFormGridDonateButtonText()
    {
        return give_get_meta($this->form->id, '_give_form_grid_donate_button_text', true);
    }

    public function getEmailOptionsStatus(): string
    {
        return give_get_meta($this->form->id, '_give_email_options', true);
    }

    public function getEmailTemplate(): string
    {
        return give_get_meta($this->form->id, '_give_email_template', true);
    }

    public function getEmailLogo(): string
    {
        return Give_Email_Notification_Util::get_email_logo($this->form->id);
    }

    public function getEmailFromName(): string
    {
        return give_get_meta($this->form->id, '_give_from_name', true);
    }

    public function getEmailFromEmail(): string
    {
        return give_get_meta($this->form->id, '_give_from_email', true);
    }

    /**
     * @since 3.0.0
     * @return string 'multi', 'set'
     */
    public function getDonationOption(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_price_option', true);
    }

    /**
     * @since 3.0.0
     * @return string 'multi', 'set'
     */
    public function isDonationOptionMulti(): string
    {
        return 'multi' === $this->getDonationOption();
    }

    /**
     * @return string 'multi', 'set'
     */
    public function isDonationOptionSet(): string
    {
        return 'set' === $this->getDonationOption();
    }

    /**
     * @since 3.0.0
     */
    public function getDonationLevels(): array
    {
        return give()->form_meta->get_meta($this->form->id, '_give_donation_levels', true);
    }

    /**
     * @since 3.0.0
     */
    public function isRecurringDonationsEnabled(): bool
    {
        $_give_recurring = $this->getRecurringDonationsOption();

        return ! empty($_give_recurring) && 'no' !== $_give_recurring;
    }

    /**
     * Recurring Donations = 'no', 'yes_donor', 'yes_admin'
     *
     * @since 3.0.0
     */
    public function getRecurringDonationsOption(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_recurring', true);
    }

    /**
     * @since 3.0.0
     */
    public function isRecurringDefaultCheckboxEnabled(): bool
    {
        return 'yes' === $this->getRecurringDefaultCheckboxOption();
    }

    /**
     * 'day', 'week', 'month', 'year'
     *
     * @since 3.0.0
     */
    public function getRecurringPeriod(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_period', true);
    }

    /**
     * 'day', 'week', 'month', 'year'
     *
     * @since 3.0.0
     */
    public function getRecurringPeriodDefaultDonorsChoice(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_period_default_donor_choice', true);
    }

    /**
     * @since 3.0.0
     */
    public function getRecurringLengthOfTime(): int
    {
        return (int)give()->form_meta->get_meta($this->form->id, '_give_times', true);
    }

    /**
     * @since 3.0.0
     */
    public function getRecurringBillingInterval(): int
    {
        return (int)give()->form_meta->get_meta($this->form->id, '_give_period_interval', true);
    }

    /**
     * 'yes', 'no'
     *
     * @since 3.0.0
     */
    public function getRecurringDefaultCheckboxOption(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_checkbox_default', true);
    }

    /**
     * 'donors_choice', 'admin_choice', 'custom' (The "Donor's Choice" option allows the donor to select the time period (commonly also referred as the "frequency") of their subscription. The "Preset Period" option provides only the selected period for the donor's subscription.)
     *
     * @since 3.0.0
     */
    public function getRecurringPeriodFunctionality(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_period_functionality', true);
    }

    /**
     * @since 3.0.0
     * @return string 'enabled', 'disabled'
     */
    public function getCustomAmountOption(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_custom_amount', true);
    }

    /**
     * @since 3.0.0
     */
    public function isCustomAmountOptionEnabled(): bool
    {
        return 'enabled' === $this->getCustomAmountOption();
    }

    /**
     * @since 3.0.0
     */
    public function isRecurringPeriodFunctionalityDonorsChoice(): bool
    {
        return 'donors_choice' === $this->getRecurringPeriodFunctionality();
    }

    /**
     * @since 3.0.0
     */
    public function isRecurringPeriodFunctionalityAdminChoice(): bool
    {
        return 'admin_choice' === $this->getRecurringPeriodFunctionality();
    }

    /**
     * This used when donation option is 'multi' and custom amount is 'enabled'
     *
     * @since 3.0.0
     */
    public function getRecurringCustomAmountPeriod(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_recurring_custom_amount_period', true);
    }

    /**
     * This used when donation option is 'multi' and custom amount is 'enabled'
     *
     * @since 3.0.0
     */
    public function getRecurringCustomAmountInterval(): int
    {
        return give()->form_meta->get_meta($this->form->id, '_give_recurring_custom_amount_interval', true);
    }

    /**
     * This used when donation option is 'multi' and custom amount is 'enabled'
     *
     * @since 3.0.0
     */
    public function getRecurringCustomAmountTimes(): int
    {
        return give()->form_meta->get_meta($this->form->id, '_give_recurring_custom_amount_times', true);
    }

    public function getStripeUseGlobalDefault(): bool
    {
        return (
            give_get_meta($this->form->id, 'give_stripe_per_form_accounts', true) !== 'enabled'
        );
    }

    public function getStripeAccountId(): string
    {
        return give_get_meta($this->form->id, '_give_stripe_default_account', true);
    }

    public function isGoalCountingOnlyRecurringDonations(): bool
    {
        return filter_var(give_get_meta($this->form->id, '_give_recurring_goal_format', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array{offlineEnabled: bool, offlineUseGlobalInstructions: bool, offlineDonationInstructions: string}
     */
    public function getOfflineAttributes(): array
    {
        $customization = give_get_meta($this->form->id, '_give_customize_offline_donations', true);
        $instructions = give_get_meta($this->form->id, '_give_offline_checkout_notes', true);

        return [
            'offlineEnabled'               => $customization !== 'disabled',
            'offlineUseGlobalInstructions' => $customization === 'global',
            'offlineDonationInstructions'  => $instructions,
        ];
    }

    /**
     * @since 3.0.0
     */
    public function getFormFields(): array
    {
        return array_filter((array)give_get_meta($this->form->id, 'give-form-fields', true));
    }

    /**
     * @since 3.0.0
     */
    public function getFormFieldsPlacement(): string
    {
        return give_get_meta($this->form->id, '_give_ffm_placement', true);
    }

    /**
     * @since 3.0.2 set correct $gatewayId to be used in getMeta calls
     * @since 3.0.0
     */
    public function getFeeRecoverySettings(): array
    {
        $feeRecoveryStatus = $this->getMeta('_form_give_fee_recovery');

        if (empty($feeRecoveryStatus) || $feeRecoveryStatus === 'disabled') {
            return [];
        }

        if ($feeRecoveryStatus === 'global') {
            return [
                'useGlobalSettings' => true,
            ];
        }

        if ($feeRecoveryStatus !== 'enabled') {
            return [];
        }

        $perGatewaySettings = [];
        $gateways = give_get_ordered_payment_gateways(give_get_enabled_payment_gateways());
        $gatewaysMap = [
            'stripe' => StripePaymentElementGateway::id(),
        ];

        if ($gateways) {
            foreach (array_keys($gateways) as $gatewayId) {
                $v3GatewayId = $gatewayId;
                if (array_key_exists($gatewayId, $gatewaysMap)) {
                    $v3GatewayId = $gatewaysMap[$gatewayId];
                }

                $perGatewaySettings[$v3GatewayId] = [
                    'enabled'       => $this->getMeta('_form_gateway_fee_enable_' . $gatewayId) === 'enabled',
                    'feePercentage' => (float)$this->getMeta('_form_gateway_fee_percentage_' . $gatewayId, 2.9),
                    'feeBaseAmount' => (float)$this->getMeta('_form_gateway_fee_base_amount_' . $gatewayId, 0.30),
                    'maxFeeAmount'  => (float)$this->getMeta(
                        '_form_gateway_fee_maximum_fee_amount_' . $gatewayId,
                        give_format_decimal(['amount' => '0.00'])
                    ),
                ];
            }
        }

        return [
            'useGlobalSettings'        => false,
            'feeSupportForAllGateways' => $this->getMeta('_form_give_fee_configuration') === 'all_gateways',
            'perGatewaySettings'       => $perGatewaySettings,
            'feePercentage'            => (float)$this->getMeta('_form_give_fee_percentage'),
            'feeBaseAmount'            => (float)$this->getMeta('_form_give_fee_base_amount'),
            'maxFeeAmount'             => (float)$this->getMeta('_form_give_fee_maximum_fee_amount'),
            'includeInDonationSummary' => $this->getMeta('_form_breakdown') === 'enabled',
            'donorOptIn'               => $this->getMeta('_form_give_fee_mode') === 'donor_opt_in',
            'feeCheckboxLabel'         => $this->getMeta('_form_give_fee_checkbox_label'),
            'feeMessage'               => $this->getMeta('_form_give_fee_explanation'),
        ];
    }

    /**
     * @since 3.7.0
     */
    public function isConstantContactEnabled(): bool
    {
        $isFormEnabled = give_is_setting_enabled($this->getMeta('_give_constant_contact_enable'), 'true');

        $isFormDisabled = give_is_setting_enabled($this->getMeta('_give_constant_contact_disable'), 'true');

        $isGloballyEnabled = give_is_setting_enabled(
            give_get_option('give_constant_contact_show_checkout_signup'),
            'on'
        );

        return ! ($isFormDisabled || ( ! $isGloballyEnabled && ! $isFormEnabled));
    }

    /**
     * @since 3.7.0
     */
    public function getConstantContactLabel(): string
    {
        $defaultMeta = give_get_option('give_constant_contact_label', __('Subscribe to our newsletter?'));

        return $this->getMeta('_give_constant_contact_custom_label', $defaultMeta);
    }

    /**
     * @since 3.7.0
     */
    public function getConstantContactDefaultChecked(): bool
    {
        $defaultMeta = give_is_setting_enabled(
            give_get_option(
                'give_constant_contact_checked_default',
                true
            ),
            'on'
        );

        return $this->getMeta('_give_constant_contact_checked_default', $defaultMeta);
    }

    /**
     * @since 3.7.0
     */
    public function getConstantContactSelectedLists(): array
    {
        $defaultMeta = give_get_option('give_constant_contact_list', []);

        return (array)$this->getMeta('_give_constant_contact', $defaultMeta);
    }

    /**
     * @since 3.3.0
     */
    public function isMailchimpEnabled(): bool
    {
        $isFormEnabled = give_is_setting_enabled($this->getMeta('_give_mailchimp_enable'), 'true');

        $isFormDisabled = give_is_setting_enabled($this->getMeta('_give_mailchimp_disable'), 'true');

        $isGloballyEnabled = give_is_setting_enabled(give_get_option('give_mailchimp_show_checkout_signup'), 'on');

        return ! ($isFormDisabled || ( ! $isGloballyEnabled && ! $isFormEnabled));
    }

    /**
     * @since 3.3.0
     */
    public function getMailchimpLabel(): string
    {
        return $this->getMeta(
            '_give_mailchimp_custom_label',
            give_get_option('give_mailchimp_label', __('Subscribe to newsletter?'))
        );
    }

    /**
     * @since 3.7.0 add global setting as default.
     * @since 3.3.0
     */
    public function getMailchimpDefaultChecked(): bool
    {
        return give_is_setting_enabled(
            $this->getMeta(
                '_give_mailchimp_checked_default',
                give_get_option('give_mailchimp_checked_default', true)
            )
        );
    }

    /**
     * @since 3.7.0 add global setting as default.
     * @since 3.3.0
     */
    public function getMailchimpSendDonationData(): bool
    {
        return give_is_setting_enabled(
            $this->getMeta(
                '_give_mailchimp_send_donation_data',
                give_get_option('give_mailchimp_donation_data', true)
            )
        );
    }

    /**
     * @since 3.7.0 add global setting as default.
     * @since 3.3.0
     */
    public function getMailchimpSendFFMData(): bool
    {
        return give_is_setting_enabled(
            $this->getMeta(
                '_give_mailchimp_send_ffm',
                give_get_option('give_mailchimp_ffm_pass_field')
            )
        );
    }

    /**
     * @since 3.3.0
     */
    public function getMailchimpDefaultAudiences(): array
    {
        return (array)$this->getMeta('_give_mailchimp', give_get_option('give_mailchimp_list', ['']));
    }

    /**
     * @since 3.3.0
     */
    public function getMailchimpSubscriberTags(): ?array
    {
        return (array)$this->getMeta('_give_mailchimp_tags', null);
    }


    /**
     * Retrieves metadata for the current form.
     *
     * This method acts as a wrapper for the give_get_meta function, reducing redundancy
     * and improving code readability when fetching metadata related to the current form.
     *
     * @since 3.0.0
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getMeta(string $key, $default = null)
    {
        return give_get_meta($this->form->id, $key, true, $default);
    }

    /**
     * @since 3.3.1 changed how is checked if the form has funds
     * @since 3.3.0
     */
    public function hasFunds(): bool
    {
        $fundsAndDesignationsAttributes = $this->getFundsAndDesignationsAttributes();

        return ! empty($fundsAndDesignationsAttributes['fund']);
    }

    /**
     * @since 3.3.1 changed how is checked if the form has fund options
     * @since 3.3.0
     */
    public function hasFundOptions(): bool
    {
        $fundsAndDesignationsAttributes = $this->getFundsAndDesignationsAttributes();

        return ! empty($fundsAndDesignationsAttributes['options']);
    }

    /**
     * @since 3.4.0 added additional checks to ensure that the form has funds and fund options
     * @since 3.3.0
     */
    public function getFundsAndDesignationsAttributes(): array
    {
        $label = give_get_meta($this->form->id, 'give_funds_label', true);
        $isAdminChoice = 'admin_choice' === give_get_meta($this->form->id, 'give_funds_form_choice', true);
        $adminChoice = give_get_meta($this->form->id, 'give_funds_admin_choice', true);
        $donorOptions = give_get_meta($this->form->id, 'give_funds_donor_choice', true);


        $options = [];
        if ( ! empty($donorOptions)) {
            foreach ($donorOptions as $fundId) {
                $options[] = [
                    'value'     => $fundId,
                    'label'     => $this->getFundLabel($fundId),
                    'checked'   => $isAdminChoice ? $fundId === $adminChoice : true,
                    'isDefault' => $this->isDefaultFund($fundId),
                ];
            }
        }

        $fund = [];
        if ($isAdminChoice) {
            $fund = [
                'value'     => $adminChoice,
                'label'     => $this->getFundLabel($adminChoice),
                'checked'   => true,
                'isDefault' => $this->isDefaultFund($adminChoice),
            ];
        } elseif ( ! empty($options)) {
            $fund = $options[0];
        }

        return [
            'label'   => $label,
            'fund'    => $fund,
            'options' => $options,
        ];
    }

    /**
     * @since 3.3.0
     */
    private function getFundLabel(int $fundId): string
    {
        global $wpdb;

        $fund = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->give_funds} WHERE id = %d", $fundId)
        );

        if ( ! $fund) {
            return '';
        }

        return $fund->title;
    }

    /**
     * @since 3.3.0
     */
    private function isDefaultFund(int $fundId): bool
    {
        global $wpdb;

        $fund = $wpdb->get_row("SELECT id FROM {$wpdb->give_funds} WHERE is_default = 1");

        if ( ! $fund) {
            return false;
        }

        return $fund->id === $fundId;
    }

    /**
     * @since 3.4.0
     *
     * @return string 'global', 'enabled', 'disabled'
     */
    public function getGiftAidStatus(): string
    {
        return $this->getMeta('give_gift_aid_enable_disable');
    }

    /**
     * @since 3.4.0
     */
    public function getGiftAidTitle(): string
    {
        return $this->getMeta('give_gift_aid_fieldset_title');
    }

    /**
     * @since 3.4.0
     */
    public function getGiftAidDescription(): string
    {
        return $this->getMeta('give_gift_aid_explanation_content');
    }

    /**
     * @since 3.4.0
     */
    public function getGiftAidLongExplanationEnabled(): bool
    {
        return $this->getMeta('give_gift_aid_long_explanation_enable_disable');
    }

    /**
     * @since 3.4.0
     */
    public function getGiftAidLongExplanation(): string
    {
        return $this->getMeta('give_gift_aid_long_explanation_content');
    }

    /**
     * @since 3.4.0
     */
    public function getGiftAidCheckboxLabel(): string
    {
        return $this->getMeta('give_gift_aid_checkbox_label');
    }

    /**
     * @since 3.4.0
     */
    public function getGiftAidAgreementText(): string
    {
        return $this->getMeta('give_gift_aid_agreement');
    }

    /**
     * @since 3.4.0
     */
    public function getGiftAidDeclarationForm(): string
    {
        return $this->getMeta('give_gift_aid_declaration_form');
    }

    /**
     * @since 3.8.0
     */
    public function getDoubleTheDonationStatus(): string
    {
        return $this->getMeta('dtd_enable_disable');
    }

    /**
     * @since 3.8.0
     */
    public function getDoubleTheDonationLabel(): string
    {
        return $this->getMeta('give_dtd_label');
    }

    /**
     * @since 3.5.0
     */
    public function getFormFeaturedImage()
    {
        $templateSettings = $this->getFormTemplateSettings();

        if ( ! empty($templateSettings['introduction']['image'])) {
            // Sequoia Template (Multi-Step)
            $featuredImage = $templateSettings['introduction']['image'];
        } elseif ( ! empty($templateSettings['visual_appearance']['header_background_image'])) {
            // Classic Template - it doesn't use the featured image from the WP default setting as a fallback
            $featuredImage = $templateSettings['visual_appearance']['header_background_image'];
        } elseif ( ! isset($templateSettings['visual_appearance']['header_background_image'])) {
            // Legacy Template or Sequoia Template without the ['introduction']['image'] setting
            $featuredImage = get_the_post_thumbnail_url($this->form->id, 'full');
        } else {
            $featuredImage = null;
        }

        return $featuredImage;
    }

    /**
     * @since 3.10.0
     */
    public function isConvertKitEnabled(): bool
    {
        $isFormEnabled = $this->getMeta('_give_convertkit_override_option') === 'customize';

        $isFormDisabled = $this->getMeta('_give_convertkit_override_option') === 'disabled';

        $isGloballyEnabled = $this->getMeta('_give_convertkit_override_option') === 'default' &&
                             give_is_setting_enabled(give_get_option('give_convertkit_show_subscribe_checkbox'));

        return $isFormEnabled ? $isGloballyEnabled : $isFormDisabled;
    }

    /**
     * @since 3.11.0
     */
    public function getConvertKitLabel(): string
    {
        $defaultMeta = give_get_option('give_convertkit_label', __('Subscribe to newsletter?'));

        return $this->getMeta('_give_convertkit_custom_label', $defaultMeta);
    }

    /**
     * @since 3.11.0
     */
    public function getConvertKitDefaultChecked(): bool
    {
        $defaultMeta = give_get_option('give_convertkit_checked_default');

        return give_is_setting_enabled($this->getMeta('_give_convertkit_checked_default', $defaultMeta));
    }

    /**
     * @since 3.11.0
     */
    public function getConvertKitTags(): ?array
    {
        $defaultMeta = give_get_option('_give_convertkit_tags', []);

        return ! empty($this->getMeta('_give_convertkit_tags')) ?
            $this->getMeta('_give_convertkit_tags') :
            $defaultMeta;
    }

    /**
     * @since 3.11.0
     */
    public function getConvertKitSelectedForm(): string
    {
        $defaultMeta = give_get_option('give_convertkit_list', '');

        return $this->getMeta('_give_convertkit', $defaultMeta);
    }

    public function isActiveCampaignEnabled(): bool
    {
        $isFormEnabled = give_is_setting_enabled($this->getMeta('activecampaign_per_form_options'), 'customized');

        $isFormDisabled = give_is_setting_enabled($this->getMeta('activecampaign_per_form_options'), 'disabled');

        $isGloballyEnabled = give_is_setting_enabled(give_get_option('give_activecampaign_globally_enabled'), 'on');

        return ! ($isFormDisabled || ( ! $isGloballyEnabled && ! $isFormEnabled));
    }

    /**
     * @since 3.10.0
     */
    public function getActiveCampaignLabel(): string
    {
        $defaultMeta = get_option('give_activecampaign_label', __('Subscribe to our newsletter?', 'give'));

        return $this->getMeta('give_activecampaign_label', $defaultMeta);
    }

    /**
     * @since 3.10.0
     */
    public function getActiveCampaignDefaultChecked(): bool
    {
        $isFormEnabled = give_is_setting_enabled($this->getMeta('activecampaign_per_form_options'), 'customized');

        $isGlobalChecked = give_is_setting_enabled(give_get_option('give_activecampaign_checkbox_default'), 'on');

        $isFormChecked = give_is_setting_enabled($this->getMeta('give_activecampaign_checkbox_default'), 'on');

        return $isFormEnabled ? $isFormChecked : $isGlobalChecked;
    }

    /**
     * @since 3.10.0
     */
    public function getActiveCampaignSelectedLists(): array
    {
        $defaultMeta = give_get_option('give_activecampaign_lists', []);

        return ! empty($this->getMeta('give_activecampaign_lists')) ?
            $this->getMeta('give_activecampaign_lists') : $defaultMeta;
    }

    /**
     * @since 3.10.0
     */
    public function getActiveCampaignTags(): array
    {
        $defaultMeta = give_get_option('give_activecampaign_tags', []);

        return ! empty($this->getMeta('give_activecampaign_tags')) ?
            $this->getMeta('give_activecampaign_tags') : $defaultMeta;
    }
}

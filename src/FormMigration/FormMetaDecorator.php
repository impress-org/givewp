<?php

namespace Give\FormMigration;

use Give\DonationForms\V2\Models\DonationForm;
use Give\DonationForms\ValueObjects\GoalType;
use Give\FormMigration\Contracts\FormModelDecorator;
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
        return !$this->isGuestDonationsEnabled();
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

            if (!empty($form_option) && give_is_setting_enabled($form_option, ['required'])) {
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

        return give_get_meta($this->form->id, "_give_{$template}_form_template_settings", true);
    }

    public function isDonationGoalEnabled(): bool
    {
        return give_is_setting_enabled(
            give_get_meta($this->form->id, '_give_goal_option', true)
        );
    }

    public function getDonationGoalType(): GoalType
    {
        switch (give_get_form_goal_format($this->form->id)) {
            case 'donors':
                return GoalType::DONORS();
            case 'donation': // @note v2: Singular
                return GoalType::DONATIONS(); // @note v3: Plural
            case 'amount':
            case 'percentage': // @note `percentage` is not supported in v3 - defaulting to `amount`
            default:
                return GoalType::AMOUNT();
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
     * @unreleased
     * @return string 'multi', 'set'
     */
    public function getDonationOption(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_price_option', true);
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    public function getDonationLevels(): array
    {
        return give()->form_meta->get_meta($this->form->id, '_give_donation_levels', true);
    }

    /**
     * @unreleased
     */
    public function isRecurringDonationsEnabled(): bool
    {
        $_give_recurring = $this->getRecurringDonationsOption();

        return !empty($_give_recurring) && 'no' !== $_give_recurring;
    }

    /**
     * Recurring Donations = 'no', 'yes_donor', 'yes_admin'
     *
     * @unreleased
     */
    public function getRecurringDonationsOption(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_recurring', true);
    }

    /**
     * @unreleased
     */
    public function isRecurringDefaultCheckboxEnabled(): bool
    {
        return 'yes' === $this->getRecurringDefaultCheckboxOption();
    }

    /**
     * 'day', 'week', 'month', 'year'
     *
     * @unreleased
     */
    public function getRecurringPeriod(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_period', true);
    }

    /**
     * 'day', 'week', 'month', 'year'
     *
     * @unreleased
     */
    public function getRecurringPeriodDefaultDonorsChoice(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_period_default_donor_choice', true);
    }

    /**
     * @unreleased
     */
    public function getRecurringLengthOfTime(): int
    {
        return (int)give()->form_meta->get_meta($this->form->id, '_give_times', true);
    }

    /**
     * @unreleased
     */
    public function getRecurringBillingInterval(): int
    {
        return (int)give()->form_meta->get_meta($this->form->id, '_give_period_interval', true);
    }

    /**
     * 'yes', 'no'
     *
     * @unreleased
     */
    public function getRecurringDefaultCheckboxOption(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_checkbox_default', true);
    }

    /**
     * 'donors_choice', 'admin_choice', 'custom' (The "Donor's Choice" option allows the donor to select the time period (commonly also referred as the "frequency") of their subscription. The "Preset Period" option provides only the selected period for the donor's subscription.)
     *
     * @unreleased
     */
    public function getRecurringPeriodFunctionality(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_period_functionality', true);
    }

    /**
     * @unreleased
     * @return string 'enabled', 'disabled'
     */
    public function getCustomAmountOption(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_custom_amount', true);
    }

    /**
     * @unreleased
     * @return string 'enabled', 'disabled'
     */
    public function isCustomAmountOptionEnabled(): string
    {
        return 'enabled' === $this->getCustomAmountOption();
    }

    /**
     * @unreleased
     */
    public function isRecurringPeriodFunctionalityDonorsChoice(): bool
    {
        return 'donors_choice' === $this->getRecurringPeriodFunctionality();
    }

    /**
     * @unreleased
     */
    public function isRecurringPeriodFunctionalityAdminChoice(): bool
    {
        return 'admin_choice' === $this->getRecurringPeriodFunctionality();
    }

    /**
     * This used when donation option is 'multi' and custom amount is 'enabled'
     *
     * @unreleased
     */
    public function getRecurringCustomAmountPeriod(): string
    {
        return give()->form_meta->get_meta($this->form->id, '_give_recurring_custom_amount_period', true);
    }

    /**
     * This used when donation option is 'multi' and custom amount is 'enabled'
     *
     * @unreleased
     */
    public function getRecurringCustomAmountInterval(): int
    {
        return give()->form_meta->get_meta($this->form->id, '_give_recurring_custom_amount_interval', true);
    }

    /**
     * This used when donation option is 'multi' and custom amount is 'enabled'
     *
     * @unreleased
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

    /**
     * @return array{offlineEnabled: bool, offlineUseGlobalInstructions: bool, offlineDonationInstructions: string}
     */
    public function getOfflineAttributes(): array
    {
        $customization = give_get_meta($this->form->id, '_give_customize_offline_donations', true);
        $instructions = give_get_meta($this->form->id, '_give_offline_checkout_notes', true);

        return [
            'offlineEnabled' => $customization !== 'disabled',
            'offlineUseGlobalInstructions' => $customization === 'global',
            'offlineDonationInstructions' => $instructions,
        ];
    }
}

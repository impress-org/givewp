<?php

namespace Give\FormMigration\Steps\FormTemplate;

use Give\DonationForms\Properties\FormSettings;
use Give\FormMigration\Actions\MapSettingsToDesignHeader;
use Give\FormMigration\Actions\MapSettingsToDonationSummary;
use Give\FormMigration\Contracts\FormMigrationStep;
use Give\FormMigration\DataTransferObjects\DesignHeaderSettings;
use Give\FormMigration\DataTransferObjects\DonationSummarySettings;

class SequoiaTemplateSettings extends FormMigrationStep
{
    public function canHandle(): bool
    {
        return 'sequoia' === $this->formV2->getFormTemplate();
    }

    public function process()
    {
        [
            'visual_appearance' => $visualAppearance,
            'introduction' => $introduction,
            'payment_amount' => $paymentAmount,
            'payment_information' => $paymentInformation,
            'thank-you' => $donationReceipt,
        ] = $this->formV2->getFormTemplateSettings();

        /** @var FormSettings $formSettings */
        $formSettings = $this->formV3->settings;
        $formSettings->designId = 'multi-step';

        $this->visualAppearance($visualAppearance);
        $this->introduction($introduction);
        $this->paymentAmount($paymentAmount);
        $this->paymentInformation($paymentInformation);
        $this->donationReceipt($donationReceipt);
    }

    protected function visualAppearance($settings)
    {
        [
            'primary_color' => $primaryColor, // '#28C77B'
            'google-fonts' => $googleFonts, // 'enabled'
            'decimals_enabled' => $decimalsEnabled, // 'disabled'
        ] = $settings;

        $this->formV3->settings->primaryColor = $primaryColor;

        // @note `google-fonts` is not supported in v3 forms (defers to the Form Design).

        // @note `decimals_enabled` is not supported in v3 forms (defers to the Form Design).
    }

    protected function introduction($settings)
    {
        [
            'enabled' => $enabled, // 'enabled',
            'headline' => $headline, // 'Tributes Form',
            'description' => $description, // 'Help our organization by donating today! All donations go directly to making a difference for our cause.',
            'image' => $image, // '',
            'donate_label' => $donateLabel, // 'Donate Now',
        ] = $settings;

        MapSettingsToDesignHeader::make($this->formV3)
            ->__invoke(new DesignHeaderSettings($enabled, $headline, $description));

        $this->formV3->settings->multiStepFirstButtonText = $donateLabel;

        // @note `image` is not supported in v3 forms (defers to the Form Design).

    }

    protected function paymentAmount($settings)
    {
        [
            'header_label' => $headerLabel, // 'Choose Amount',
            'content' => $content, // 'How much would you like to donate? As a contributor to WordPress we make sure your donation goes directly to supporting our cause. Thank you for your generosity!',
            'next_label' => $nextLabel, // 'Continue',
        ] = $settings;

        $this->fieldBlocks->findParentByChildName('givewp/donation-amount')
            ->setAttribute('title', $headerLabel)
            ->setAttribute('description', $content);

        $this->formV3->settings->multiStepNextButtonText = $nextLabel;
    }

    protected function paymentInformation($settings)
    {
        [
            'header_label' => $headerLabel, // 'Add Your Information',
            'headline' => $headline, // 'Who\'s giving today?',
            'description' => $description, // 'We’ll never share this information with anyone.',
            'donation_summary_enabled' => $donationSummaryEnabled, // 'enabled',
            'donation_summary_heading' => $donationSummaryHeading, // 'Here\'s what you\'re about to donate:',
            'donation_summary_location' => $donationSummaryLocation, // 'give_donation_form_before_submit',
            'checkout_label' => $checkoutLabel, // 'Donate Now',
        ] = $settings;

        $this->fieldBlocks->findParentByChildName('givewp/payment-gateways')
            ->setAttribute('title', $headline) // @note Should this be `headline` or `header_label`?
            ->setAttribute('description', $description);

        MapSettingsToDonationSummary::make($this->fieldBlocks)
            ->__invoke(DonationSummarySettings::make($settings));

        $this->formV3->settings->donateButtonCaption = $checkoutLabel;
    }

    protected function donationReceipt($settings)
    {
        [
            'headline' => $headline,
            'description' => $description,
        ] = $settings;

        $this->formV3->settings->receiptHeading = $headline;
        $this->formV3->settings->receiptDescription = $description;

        // @note `image`, `sharing`, `sharing_instruction`, `twitter_message` are not supported in v3 forms.
    }
}

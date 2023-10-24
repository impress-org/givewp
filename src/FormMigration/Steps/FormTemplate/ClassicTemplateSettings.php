<?php

namespace Give\FormMigration\Steps\FormTemplate;

use Give\FormMigration\Actions\MapSettingsToDesignHeader;
use Give\FormMigration\Actions\MapSettingsToDonationSummary;
use Give\FormMigration\Contracts\FormMigrationStep;
use Give\FormMigration\DataTransferObjects\DesignHeaderSettings;
use Give\FormMigration\DataTransferObjects\DonationSummarySettings;

class ClassicTemplateSettings extends FormMigrationStep
{
    public function canHandle(): bool
    {
        return 'classic' === $this->formV2->getFormTemplate();
    }

    public function process()
    {
        [
            'visual_appearance' => $visualAppearance,
            'donation_amount' => $donationAmount,
            'donor_information' => $donorInformation,
            'payment_information' => $paymentInformation,
            'donation_receipt' => $donationReceipt,
            'introduction' => $introduction,
        ] = $this->formV2->getFormTemplateSettings();

        $this->visualAppearance($visualAppearance);
        $this->donationAmount($donationAmount);
        $this->donorInformation($donorInformation);
        $this->paymentInformation($paymentInformation);
        $this->donationReceipt($donationReceipt);

        // @note the values for 'introduction' are not configurable for this form template.
    }

    protected function visualAppearance($settings)
    {
        [
            'primary_color' => $primaryColor, //'#1E8CBE',
            'container_style' => $containerStyle, // 'boxed',
            'primary_font' => $primaryFont, // 'Montserrat',
            'display_header' => $displayHeader, //'enabled',
            'main_heading' => $mainHeading, // 'Support Our Cause',
            'description' => $description, // 'Help our organization by donating today! All donations go directly to making a difference for our cause.',
            'header_background_image' => $headerBackgroundImage, // 'http://wordpress.test/wp-content/uploads/2023/04/kbjohnson90_vector_illustration_simple_a_friendly_olive_smiling_4d93434b-e43a-4c90-bcf8-c8fafeb3b9fb.png',
            'header_background_color' => $headerBackgroundColor, //'#1E8CBE',
            'secure_badge' => $secureBadge, // 'enabled',
            'secure_badge_text' => $secureBadgeText, // '100% Secure Donation',
        ] = $settings;

        MapSettingsToDesignHeader::make($this->formV3)
            ->__invoke(new DesignHeaderSettings($displayHeader, $mainHeading, $description));

        $this->formV3->settings->primaryColor = $primaryColor;

        // @note What do we do with `secondaryColor` in v3 (which is not a feature of v2)?
    }

    protected function donationAmount($donationAmount)
    {
        [
            'headline' => $headline,
            'description' => $description,
        ] = $donationAmount;

        $this->fieldBlocks->findParentByChildName('givewp/donation-amount')
            ->setAttribute('title', $headline)
            ->setAttribute('description', $description);
    }

    protected function donorInformation($settings)
    {
        [
            'headline' => $headline,
            'description' => $description,
        ] = $settings;

        $this->fieldBlocks->findParentByChildName('givewp/donor-name')
            ->setAttribute('title', $headline)
            ->setAttribute('description', $description);
    }

    protected function paymentInformation($settings)
    {
        [
            'headline' => $headline,
            'description' => $description,
            'donation_summary_enabled' => $donationSummaryEnabled,
            'donation_summary_heading' => $donationSummaryHeading,
            'donation_summary_location' => $donationSummaryLocation,
        ] = $settings;

        $this->fieldBlocks->findParentByChildName('givewp/payment-gateways')
            ->setAttribute('title', $headline)
            ->setAttribute('description', $description);

        MapSettingsToDonationSummary::make($this->fieldBlocks)
            ->__invoke(DonationSummarySettings::make($settings));
    }

    protected function donationReceipt($settings)
    {
        [
            'headline' => $headline,
            'description' => $description,
        ] = $settings;

        $this->formV3->settings->receiptHeading = $headline;
        $this->formV3->settings->receiptDescription = $description;

        // @note `social_sharing`, `sharing_instructions`, `twitter_message` are not supported in v3 forms.
    }
}

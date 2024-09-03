<?php

namespace Give\Campaigns\Migrations;

use DateTime;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
 */
class MigrateFormsToCampaignForms extends Migration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'migrate_forms_to_campaign_forms';
    }

    /**
     * @inheritDoc
     */
    public static function timestamp(): int
    {
        return strtotime('2024-08-21');
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function run()
    {
        foreach(DonationForm::query()->getAll() ?? [] as $form) {
            $this->createParentCampaignForDonationForm($form);
        }
    }

    /**
     * @unreleased
     */
    public function createParentCampaignForDonationForm(DonationForm $form)
    {
        $campaign = Campaign::create([
            'type' => CampaignType::CORE(),
            'title' => $form->title,
            'shortDescription' => $form->settings->formExcerpt,
            'longDescription' => $form->settings->description,
            'logo' => $form->settings->designSettingsLogoUrl,
            'image' => $form->settings->designSettingsImageUrl,
            'primaryColor' => $form->settings->primaryColor,
            'secondaryColor' => $form->settings->secondaryColor,
            'goal' => (int) $form->settings->goalAmount,
            'status' => $this->mapFormStatusToCampaignStatus($form->status),
            'startDate' => new DateTime($form->settings->goalStartDate),
            'endDate' => new DateTime($form->settings->goalEndDate),
        ]);

        DB::table('give_campaign_forms')
            ->insert([
                'form_id' => $form->id,
                'campaign_id' => $campaign->id,
            ]);
    }

   /**
     * @unreleased
     */
    public function mapFormStatusToCampaignStatus(DonationFormStatus $status)
    {
        switch ($status) {
            case DonationFormStatus::PUBLISHED():
            case DonationFormStatus::UPGRADED(): // TODO: How do we handle upgraded, non-upgraded forms?
            case DonationFormStatus::PRIVATE(): // TODO: How do we handle Private forms?
                return CampaignStatus::ACTIVE();

            case DonationFormStatus::PENDING():
                return CampaignStatus::PENDING();

            case DonationFormStatus::DRAFT():
                return CampaignStatus::DRAFT();

            case DonationFormStatus::TRASH():
                return CampaignStatus::INACTIVE();
        }
    }
}

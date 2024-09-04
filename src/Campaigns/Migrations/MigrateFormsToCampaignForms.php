<?php

namespace Give\Campaigns\Migrations;

use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\QueryBuilder\JoinQueryBuilder;

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
        return strtotime('2024-08-26 00:00:01');
    }

    /**
     * @unreleased
     * @inheritDoc
     */
    public function run()
    {
        DB::transaction(function() {
            try {
                foreach($this->getFormData() as $formData) {
                    $this->createParentCampaignForDonationForm($formData);
                }
            } catch (DatabaseQueryException $exception) {
                DB::rollback();
                throw new DatabaseMigrationException('An error occurred while creating initial campaigns', 0, $exception);
            }
        });
    }

    /**
     * @unreleased
     */
    protected function getFormData(): array
    {
        return DB::table('posts', 'forms')
            ->select(
                ['ID', 'id'],
                ['post_title', 'title'],
                ['post_status', 'status'],
                ['meta_value', 'settings']
            )
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_formmeta', 'formmeta')
                    ->on('formmeta.form_id', 'forms.ID');
            })
            ->where('forms.post_type', 'give_forms')
            ->where('formmeta.meta_key', 'formBuilderSettings')
            ->getAll();
    }

    /**
     * @unreleased
     */
    public function createParentCampaignForDonationForm($formData): void
    {
        $formId = $formData['id'];
        $formTitle = $formData['title'];
        $formStatus = $formData['status'];
        $formSettings = json_decode($formData['settings']);

        $campaignId = DB::table('give_campaigns')
            ->insert([
                'type' => CampaignType::CORE(),
                'title' => $formTitle,
                'status' => $this->mapFormToCampaignStatus($formStatus),
                'shortDescription' => $formSettings->formExcerpt,
                'longDescription' => $formSettings->description,
                'logo' => $formSettings->designSettingsLogoUrl,
                'image' => $formSettings->designSettingsImageUrl,
                'primaryColor' => $formSettings->primaryColor,
                'secondaryColor' => $formSettings->secondaryColor,
                'goal' => $formSettings->goalAmount,
                'startDate' => $formSettings->goalStartDate,
                'endDate' => $formSettings->goalEndDate,
            ]);

        DB::table('give_campaign_forms')
            ->insert([
                'form_id' => $formId,
                'campaign_id' => $campaignId,
            ]);
    }

    /**
     * @unreleased
     */
    public function mapFormToCampaignStatus(string $status): string
    {
        switch (new DonationFormStatus($status)) {

            case DonationFormStatus::PENDING():
                return CampaignStatus::PENDING()->getValue();

            case DonationFormStatus::DRAFT():
                return CampaignStatus::DRAFT()->getValue();

            case DonationFormStatus::TRASH():
                return CampaignStatus::INACTIVE()->getValue();

            case DonationFormStatus::PUBLISHED():
            case DonationFormStatus::UPGRADED(): // TODO: How do we handle upgraded, non-upgraded forms?
            case DonationFormStatus::PRIVATE(): // TODO: How do we handle Private forms?
                return CampaignStatus::ACTIVE()->getValue();

            default: // TODO: How do we handle an unknown form status?
                return CampaignStatus::INACTIVE()->getValue();
        }
    }
}

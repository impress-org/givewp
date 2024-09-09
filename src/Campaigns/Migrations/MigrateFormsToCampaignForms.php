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
        $query = DB::table('posts', 'forms')
            ->select(
                ['forms.ID', 'id'],
                ['forms.post_title', 'title'],
                ['forms.post_status', 'status'],
                ['forms.post_date', 'createdAt']
            )
            ->where('forms.post_type', 'give_forms');

        $query->select(['formmeta.meta_value', 'settings'])
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_formmeta', 'formmeta')
                    ->on('formmeta.form_id', 'forms.ID');
            })
            ->where('formmeta.meta_key', 'formBuilderSettings');

        $query->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_campaigns', 'campaigns')
                    ->on('campaigns.form_id', 'forms.ID');
            })
            ->whereIsNull('campaigns.id');

        return $query->getAll();
    }

    /**
     * @unreleased
     */
    public function createParentCampaignForDonationForm($formData): void
    {
        $formId = $formData->id;
        $formTitle = $formData->title;
        $formStatus = $formData->status;
        $formCreatedAt = $formData->createdAt;
        $formSettings = json_decode($formData->settings);

        $campaignId = DB::table('give_campaigns')
            ->insert([
                'campaign_type' => CampaignType::CORE()->getValue(),
                'campaign_title' => $formTitle,
                'status' => $this->mapFormToCampaignStatus($formStatus),
                'short_desc' => $formSettings->formExcerpt,
                'long_desc' => $formSettings->description,
                'campaign_logo' => $formSettings->designSettingsLogoUrl,
                'campaign_image' => $formSettings->designSettingsImageUrl,
                'primary_color' => $formSettings->primaryColor,
                'secondary_color' => $formSettings->secondaryColor,
                'campaign_goal' => $formSettings->goalAmount,
                'start_date' => $formSettings->goalStartDate,
                'end_date' => $formSettings->goalEndDate,
                'date_created' => $formCreatedAt,
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
        switch ($status) {

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

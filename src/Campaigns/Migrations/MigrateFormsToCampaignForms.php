<?php

namespace Give\Campaigns\Migrations;

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
     * @throws \Exception
     */
    public function run()
    {
        DB::transaction(function() {
            try {
                array_map([$this, 'createCampaignForForm'], $this->getFormData());
                array_map([$this, 'addUpgradedFormToCampaign'], $this->getUpgradedFormData());
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

        // Exclude forms already associated with a campaign (ie Peer-to-peer).
        $query->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->leftJoin('give_campaigns', 'campaigns')
                    ->on('campaigns.form_id', 'forms.ID');
            })
            ->whereIsNull('campaigns.id');

        // Exclude forms with an `upgraded` status, which are archived.
        $query->where('forms.post_status', 'upgraded', '!=');

        return $query->getAll();
    }

    /**
     * @unreleased
     * @return array [{formId, campaignId, migratedFormId}]
     */
    protected function getUpgradedFormData(): array
    {
        return DB::table('posts', 'forms')
            ->select(['forms.ID', 'formId'], ['campaign_forms.campaign_id', 'campaignId'])
            ->attachMeta('give_formmeta', 'ID', 'form_id', 'migratedFormId')
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->rightJoin('give_campaign_forms', 'campaign_forms')
                    ->on('campaign_forms.form_id', 'forms.ID');
            })
            ->where('forms.post_type', 'give_forms')
            ->whereIsNotNull('give_formmeta_attach_meta_migratedFormId.meta_value')
            ->getAll();
    }

    /**
     * @unreleased
     */
    public function createCampaignForForm($formData): void
    {
        $formId = $formData->id;
        $formTitle = $formData->title;
        $formStatus = $formData->status;
        $formCreatedAt = $formData->createdAt;
        $formSettings = json_decode($formData->settings);

        $campaignId = DB::table('give_campaigns')
            ->insert([
                'campaign_type' => 'core',
                'campaign_title' => $formTitle,
                'status' => $this->mapFormToCampaignStatus($formStatus),
                'short_desc' => $formSettings->formExcerpt,
                'long_desc' => $formSettings->description,
                'campaign_logo' => $formSettings->designSettingsLogoUrl,
                'campaign_image' => $formSettings->designSettingsImageUrl,
                'primary_color' => $formSettings->primaryColor,
                'secondary_color' => $formSettings->secondaryColor,
                'campaign_goal' => $formSettings->goalAmount,
                'goal_type' => $formSettings->goalType,
                'start_date' => $formSettings->goalStartDate,
                'end_date' => $formSettings->goalEndDate,
                'date_created' => $formCreatedAt,
            ]);

        DB::table('give_campaign_forms')
            ->insert([
                'form_id' => $formId,
                'campaign_id' => $campaignId,
                'is_default' => true,
            ]);
    }

    /**
     * @param $data
     */
    protected function addUpgradedFormToCampaign($data): void
    {
        DB::table('give_campaign_forms')
            ->insert([
                'form_id' => $data->migratedFormId,
                'campaign_id' => $data->campaignId,
            ]);
    }

    /**
     * @unreleased
     */
    public function mapFormToCampaignStatus(string $status): string
    {
        switch ($status) {

            case 'pending':
                return 'pending';

            case 'draft':
                return 'draft';

            case 'trash':
                return 'inactive';

            case 'publish':
            case 'private':
                return 'active';

            default: // TODO: How do we handle an unknown form status?
                return 'inactive';
        }
    }
}

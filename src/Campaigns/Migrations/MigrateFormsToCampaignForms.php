<?php

namespace Give\Campaigns\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Helpers\Form\Utils;
use stdClass;

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
                //array_map([$this, 'addUpgradedFormToCampaign'], $this->getUpgradedFormData());
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
        $query = DB::table('posts', 'forms')->distinct()
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
                    ->on('formmeta.form_id', 'forms.ID')->joinRaw("AND formmeta.meta_key = 'formBuilderSettings'");
            });
        //->where('formmeta.meta_key', 'formBuilderSettings');

        // Exclude forms already associated with a campaign (ie Peer-to-peer).
        $query->join(function (JoinQueryBuilder $builder) {
            $builder
                ->leftJoin('give_campaigns', 'campaigns')
                ->on('campaigns.form_id', 'forms.ID');
        })
            ->whereIsNull('campaigns.id');

        $query->where('forms.post_status', 'auto-draft', '!=');

        $query->orderBy('forms.ID', 'DESC');

        // Exclude forms with an `upgraded` status, which are archived.
        //$query->where('forms.post_status', 'upgraded', '!=');

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
            ->where('forms.post_status', 'publish')
            ->whereIsNotNull('give_formmeta_attach_meta_migratedFormId.meta_value')
            ->getAll();
    }

    /**
     * @unreleased
     */
    public function createCampaignForForm($formData): void
    {
        $formId = $formData->id;
        $formStatus = $formData->status;
        $formTitle = $formData->title;
        $formCreatedAt = $formData->createdAt;

        $isV3Form = Utils::isV3Form($formId);

        /**
         * The V2 forms with upgraded status should be skipped because their correspondent V3 version already was migrated at this point.
         */
        if ( ! $isV3Form && 'upgraded' == $formStatus) {
            return;
        }

        $formSettings = $isV3Form ? json_decode($formData->settings) : $this->getV2FormSettings($formId);

        DB::table('give_campaigns')
            ->insert([
                'form_id' => $formId,
                'campaign_type' => 'core',
                'campaign_title' => $formTitle,
                'status' => $this->mapFormToCampaignStatus($formData->status),
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

        $campaignId = DB::last_insert_id();

        $this->addCampaignFormRelationship($formId, $campaignId);
    }

    /**
     * @param $data
     */
    protected function addUpgradedFormToCampaign($data): void
    {
        $this->addCampaignFormRelationship($data->migratedFormId, $data->campaignId);
    }

    /**
     * @unreleased
     */
    protected function addCampaignFormRelationship($formId, $campaignId)
    {
        DB::table('give_campaign_forms')
            ->insert([
                'form_id' => $formId,
                'campaign_id' => $campaignId,
            ]);
    }

    /**
     * @unreleased
     */
    protected function mapFormToCampaignStatus(string $status): string
    {
        switch ($status) {

            case 'pending':
                return 'pending';

            case 'draft':
            case 'upgraded':
                return 'draft';

            case 'trash':
                return 'archived';

            case 'publish':
            case 'private':
                return 'active';

            default: // TODO: How do we handle an unknown form status?
                return 'inactive';
        }
    }

    /**
     * @unreleased
     */
    protected function getV2FormSettings(int $formId): stdClass
    {
        $template = give_get_meta($formId, '_give_form_template', true);
        $templateSettings = give_get_meta($formId, "_give_{$template}_form_template_settings", true);

        if ( ! empty($templateSettings['introduction']['image'])) {
            // Sequoia Template (Multi-Step)
            $featuredImage = $templateSettings['introduction']['image'];
        } elseif ( ! empty($templateSettings['visual_appearance']['header_background_image'])) {
            // Classic Template - it doesn't use the featured image from the WP default setting as a fallback
            $featuredImage = $templateSettings['visual_appearance']['header_background_image'];
        } elseif ( ! isset($templateSettings['visual_appearance']['header_background_image'])) {
            // Legacy Template or Sequoia Template without the ['introduction']['image'] setting
            $featuredImage = get_the_post_thumbnail_url($formId, 'full');
        } else {
            $featuredImage = null;
        }

        $formSettings = (object)[
            'formExcerpt' => get_the_excerpt($formId),
            'description' => '',
            'designSettingsLogoUrl' => '',
            'designSettingsImageUrl' => $featuredImage,
            'primaryColor' => '',
            'secondaryColor' => '',
            'goalAmount' => '',
            'goalType' => '',
            'goalStartDate' => '',
            'goalEndDate' => '',
        ];

        return $formSettings;
    }
}

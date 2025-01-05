<?php

namespace Give\Campaigns\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
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
                array_map([$this, 'createCampaignForForm'], $this->getAllFormsData());
                array_map([$this, 'addUpgradedV2FormToCampaign'], $this->getUpgradedV2FormsData());
            } catch (DatabaseQueryException $exception) {
                DB::rollback();
                throw new DatabaseMigrationException('An error occurred while creating initial campaigns', 0, $exception);
            }
        });
    }

    /**
     * @unreleased
     */
    protected function getAllFormsData(): array
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

        // Exclude forms already associated with a campaign (ie Peer-to-peer).
        $query->join(function (JoinQueryBuilder $builder) {
            $builder
                ->leftJoin('give_campaigns', 'campaigns')
                ->on('campaigns.form_id', 'forms.ID');
        })
            ->whereIsNull('campaigns.id');

        /**
         * Exclude forms with an "auto-draft" status, which are WP revisions.
         *
         * @see https://wordpress.org/documentation/article/post-status/#auto-draft
         */
        $query->where('forms.post_status', 'auto-draft', '!=');

        /**
         * Excluded upgraded V2 forms as their corresponding V3 version will be used to create the campaign - later the V2 form will be added to the proper campaign as a non-default form through the addUpgradedV2FormToCampaign() method.
         */
        $query->whereNotIn('forms.ID', function (QueryBuilder $builder) {
            $builder
                ->select('meta_value')
                ->from('give_formmeta')
                ->where('meta_key', 'migratedFormId');
        });

        // Ensure campaigns will be displayed in the same order on the list table
        $query->orderBy('forms.ID');

        return $query->getAll();
    }

    /**
     * @unreleased
     * @return array [{formId, campaignId, migratedFormId}]
     */
    protected function getUpgradedV2FormsData(): array
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
        $formStatus = $formData->status;
        $formTitle = $formData->title;
        $formCreatedAt = $formData->createdAt;
        $isV3Form = ! is_null($formData->settings);
        $formSettings = $isV3Form ? json_decode($formData->settings) : $this->getV2FormSettings($formId);

        DB::table('give_campaigns')
            ->insert([
                'form_id' => $formId,
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

        $campaignId = DB::last_insert_id();

        $this->addCampaignFormRelationship($formId, $campaignId);
    }

    /**
     * @param $data
     */
    protected function addUpgradedV2FormToCampaign($data): void
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
            case 'upgraded': // Some V3 forms can have the 'upgraded' status after being migrated from a V2 form
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
        $templateSettings = is_array($templateSettings) ? $templateSettings : [];

        return (object)[
            'formExcerpt' => get_the_excerpt($formId),
            'description' => $this->getV2FormDescription($templateSettings),
            'designSettingsLogoUrl' => '',
            'designSettingsImageUrl' => $this->getV2FormFeaturedImage($templateSettings, $formId),
            'primaryColor' => $this->getV2FormPrimaryColor($templateSettings),
            'secondaryColor' => '',
            'goalAmount' => $this->getV2FormGoalAmount($formId),
            'goalType' => $this->getV2FormGoalType($formId),
            'goalStartDate' => '',
            'goalEndDate' => '',
        ];
    }

    /**
     * @unreleased
     */
    protected function getV2FormFeaturedImage(array $templateSettings, int $formId): string
    {
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
            $featuredImage = '';
        }

        return $featuredImage;
    }

    /**
     * @unreleased
     */
    protected function getV2FormDescription(array $templateSettings): string
    {
        if ( ! empty($templateSettings['introduction']['description'])) {
            // Sequoia Template (Multi-Step)
            $description = $templateSettings['introduction']['description'];
        } elseif ( ! empty($templateSettings['visual_appearance']['description'])) {
            // Classic Template
            $description = $templateSettings['visual_appearance']['description'];
        } else {
            $description = '';
        }

        return $description;
    }

    /**
     * @unreleased
     */
    protected function getV2FormPrimaryColor(array $templateSettings): string
    {
        if ( ! empty($templateSettings['introduction']['primary_color'])) {
            // Sequoia Template (Multi-Step)
            $primaryColor = $templateSettings['introduction']['primary_color'];
        } elseif ( ! empty($templateSettings['visual_appearance']['primary_color'])) {
            // Classic Template
            $primaryColor = $templateSettings['visual_appearance']['primary_color'];
        } else {
            $primaryColor = '';
        }

        return $primaryColor;
    }

    /**
     * @unreleased
     */
    protected function getV2FormGoalAmount(int $formId)
    {
        return give_get_form_goal($formId);
    }

    /**
     * @unreleased
     */
    protected function getV2FormGoalType(int $formId): string
    {
        $onlyRecurringEnabled = filter_var(give_get_meta($formId, '_give_recurring_goal_format', true),
            FILTER_VALIDATE_BOOLEAN);

        switch (give_get_form_goal_format($formId)) {
            case 'donors':
                return $onlyRecurringEnabled ? 'donorsFromSubscriptions' : 'donors';
            case 'donation':
                return $onlyRecurringEnabled ? 'subscriptions' : 'donations';
            case 'amount':
            case 'percentage':
            default:
                return $onlyRecurringEnabled ? 'amountFromSubscriptions' : 'amount';
        }
    }
}

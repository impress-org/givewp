<?php

namespace Give\Campaigns\Migrations;

use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\Contracts\ReversibleMigration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\QueryBuilder;
use stdClass;

/**
 * @since 4.0.0
 */
class MigrateFormsToCampaignForms extends Migration implements ReversibleMigration
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
    public static function title(): string
    {
        return 'Migrate Forms to Campaigns';
    }

    /**
     * @inheritDoc
     */
    public static function timestamp(): int
    {
        return strtotime('2024-08-26 00:00:02');
    }

    /**
     * @since 4.0.0
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
     * @inheritDoc
     */
    public function reverse(): void
    {
        // Delete core campaigns
        DB::table('give_campaigns')
            ->where('campaign_type', CampaignType::CORE)
            ->delete();

        // Truncate form relationships
        DB::table('give_campaign_forms')->truncate();
    }

    /**
     * @since 4.0.0
     */
    protected function getAllFormsData(): array
    {
        $query = DB::table('posts', 'forms')->distinct()
            ->select(
                ['forms.ID', 'id'],
                ['forms.post_title', 'title'],
                ['forms.post_name', 'name'], // unique slug
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

        $results = $query->getAll();

        if (!$results) {
            return [];
        }

        return $results;
    }

    /**
     * @since 4.3.0 Add DISTINCT, whereNotIn and new JOIN to retrieve only the migratedFormId associated with the highest form_id
     * @since 4.0.0
     * @return array [{formId, campaignId, migratedFormId}]
     */
    protected function getUpgradedV2FormsData(): array
    {
        $query = DB::table('posts', 'forms')->distinct()
            ->select(['forms.ID', 'formId'], ['campaign_forms.campaign_id', 'campaignId'],
                ['give_formmeta.migratedFormId', 'migratedFormId'])
            ->join(function (JoinQueryBuilder $builder) {
                $builder
                    ->rightJoin('give_campaign_forms', 'campaign_forms')
                    ->on('campaign_forms.form_id', 'forms.ID');
            })
            ->where('forms.post_type', 'give_forms');

        /**
         * Sometimes the user starts upgrading a form but gives up and puts the migrated form in the trash. However,
         * the migratedFormId keeps on DB, which can make this query return the same migratedFormId for multiple
         * campaigns, so we need to use this join statement to ensure we are NOT adding the same migratedFormId
         * for multiple campaigns, since it forces the query to retrieve only the migratedFormId associated with
         * the highest form_id which is the last upgrade attempt.
         *
         * @see https://github.com/impress-org/givewp/pull/7901#issuecomment-2854905488
         */
        $table = DB::prefix('give_formmeta');
        $query->joinRaw("INNER JOIN (
                    SELECT
                        meta_value AS migratedFormId,
                        MAX(form_id) AS max_form_id
                    FROM {$table}
                    WHERE meta_key = 'migratedFormId'
                    GROUP BY meta_value
                ) AS give_formmeta ON forms.ID = give_formmeta.max_form_id");

        /**
         * When someone re-runs the migration, it can return a duplicated entry error if the upgraded forms were
         * already added previously in the first time the migration was run, so this whereNotIn prevents these
         * errors by excluding upgraded forms already added to the give_campaign_forms table previously.
         *
         * @see https://github.com/impress-org/givewp/pull/7901#discussion_r2073600045
         */
        $query->whereNotIn('give_formmeta.migratedFormId', function (QueryBuilder $builder) {
            $builder
                ->select('form_id')
                ->from('give_campaign_forms')
                ->whereRaw('WHERE form_id = give_formmeta.migratedFormId');
        });


        $results = $query->getAll();

        if (!$results) {
            return [];
        }

        return $results;
    }

    /**
     * @since 4.0.0
     */
    public function createCampaignForForm($formData): void
    {
        $formId = $formData->id;
        $formStatus = $formData->status;
        $formName = $formData->name;
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
                'start_date' => $formCreatedAt,
                'end_date' => null,
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
     * @since 4.0.0
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
     * @since 4.0.0
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
     * @since 4.0.0
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
     * @since 4.0.0
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
     * @since 4.0.0
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
     * @since 4.0.0
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
     * @since 4.0.0
     */
    protected function getV2FormGoalAmount(int $formId)
    {
        return give_get_form_goal($formId);
    }

    /**
     * @since 4.0.0
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

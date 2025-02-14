<?php

namespace Give\Campaigns\Repositories;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;

/**
 * @unreleased
 */
class CampaignRepository
{
    /**
     * @unreleased
     *
     * @var string[]
     */
    private $requiredProperties = [
        'title',
        'status',
    ];

    /**
     * @unreleased
     *
     * Get Campaign by ID
     *
     * @unreleased
     */
    public function getById(int $id)
    {
        return $this->prepareQuery()
            ->where('id', $id)
            ->get();
    }

    /**
     * @unreleased
     *
     * Get Campaign by Form ID using a lookup table
     */
    public function getByFormId(int $formId)
    {
        return $this->queryByFormId($formId)->get();
    }

    /**
     * @unreleased
     *
     * @param int $formId
     *
     * @return ModelQueryBuilder<Campaign>
     */
    public function queryByFormId(int $formId): ModelQueryBuilder
    {
        return $this->prepareQuery()
            ->leftJoin('give_campaign_forms', 'campaigns.id', 'forms.campaign_id', 'forms')
            ->where('forms.form_id', $formId);
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function insert(Campaign $campaign): void
    {
        $this->validateProperties($campaign);

        Hooks::doAction('givewp_campaign_creating', $campaign);

        $dateCreated = Temporal::withoutMicroseconds($campaign->createdAt ?: Temporal::getCurrentDateTime());
        $dateCreatedFormatted = Temporal::getFormattedDateTime($dateCreated);
        $startDateFormatted = Temporal::getFormattedDateTime($campaign->startDate);
        $endDateFormatted = Temporal::getFormattedDateTime($campaign->endDate);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_campaigns')
                ->insert([
                    'campaign_type' => $campaign->type->getValue(),
                    'enable_campaign_page' => $campaign->enableCampaignPage,
                    'campaign_title' => wp_strip_all_tags($campaign->title, true),
                    'short_desc' => wp_strip_all_tags($campaign->shortDescription),
                    'long_desc' => wp_strip_all_tags($campaign->longDescription),
                    'campaign_logo' => $campaign->logo,
                    'campaign_image' => $campaign->image,
                    'primary_color' => $campaign->primaryColor,
                    'secondary_color' => $campaign->secondaryColor,
                    'campaign_goal' => $campaign->goal,
                    'goal_type' => $campaign->goalType->getValue(),
                    'status' => $campaign->status->getValue(),
                    'start_date' => $startDateFormatted,
                    'end_date' => $endDateFormatted,
                    'date_created' => $dateCreatedFormatted,
                ]);

            $campaignId = DB::last_insert_id();

        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a campaign', compact('campaign'));

            throw new $exception('Failed creating a campaign');
        }

        DB::query('COMMIT');

        $campaign->id = $campaignId;
        $campaign->createdAt = $dateCreated;

        Hooks::doAction('givewp_campaign_created', $campaign);
    }

    /**
     * @unreleased
     *
     * @throws Exception|InvalidArgumentException
     */
    public function update(Campaign $campaign): void
    {
        $this->validateProperties($campaign);

        $startDateFormatted = Temporal::getFormattedDateTime($campaign->startDate);
        $endDateFormatted = Temporal::getFormattedDateTime($campaign->endDate);

        Hooks::doAction('givewp_campaign_updating', $campaign);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_campaigns')
                ->where('id', $campaign->id)
                ->update([
                    'campaign_type' => $campaign->type->getValue(),
                    'enable_campaign_page' => $campaign->enableCampaignPage,
                    'campaign_page_id' => $campaign->pageId,
                    'campaign_title' => wp_strip_all_tags($campaign->title, true),
                    'short_desc' => wp_strip_all_tags($campaign->shortDescription),
                    'long_desc' => wp_strip_all_tags($campaign->longDescription),
                    'campaign_logo' => $campaign->logo,
                    'campaign_image' => $campaign->image,
                    'primary_color' => $campaign->primaryColor,
                    'secondary_color' => $campaign->secondaryColor,
                    'campaign_goal' => $campaign->goal,
                    'goal_type' => $campaign->goalType->getValue(),
                    'status' => $campaign->status->getValue(),
                    'start_date' => $startDateFormatted,
                    'end_date' => $endDateFormatted,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a campaign', compact('campaign'));

            throw new $exception('Failed updating a campaign');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_updated', $campaign);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function addCampaignForm(Campaign $campaign, int $donationFormId, bool $isDefault = false)
    {
        Hooks::doAction('givewp_campaign_form_relationship_creating', $campaign, $donationFormId, $isDefault);

        DB::query('START TRANSACTION');

        try {
            if ($isDefault) {
                DB::table('give_campaigns')
                    ->where('id', $campaign->id)
                    ->update([
                        'form_id' => $donationFormId,
                    ]);

                $campaign->defaultFormId = $donationFormId;
            }

            DB::table('give_campaign_forms')
                ->insert([
                    'form_id' => $donationFormId,
                    'campaign_id' => $campaign->id,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a campaign form relationship', compact('campaign'));

            throw new $exception('Failed creating a campaign form relationship');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_form_relationship_created', $campaign, $donationFormId, $isDefault);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function updateDefaultCampaignForm(Campaign $campaign, int $donationFormId)
    {
        Hooks::doAction('givewp_campaign_default_form_updating', $campaign, $donationFormId);

        DB::query('START TRANSACTION');

        try {
            DB::table('give_campaigns')
                ->where('id', $campaign->id)
                ->update([
                    'form_id' => $donationFormId
                ]);

            $campaign->defaultFormId = $donationFormId;
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating the campaign default form', compact('campaign'));

            throw new $exception('Failed updating the campaign default form');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_default_form_updated', $campaign, $donationFormId);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function delete(Campaign $campaign): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_campaign_deleting', $campaign);

        try {
            DB::table('give_campaigns')
                ->where('id', $campaign->id)
                ->delete();

        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a campaign', compact('campaign'));

            throw new $exception('Failed deleting a campaign');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_deleted', $campaign);

        return true;
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function mergeCampaigns(Campaign $destinationCampaign, Campaign ...$campaignsToMerge): bool
    {
        // Make sure the destination campaign ID will not be included into $campaignsToMergeIds
        $campaignsToMergeIds = array_column($campaignsToMerge, 'id');
        if ($key = array_search($destinationCampaign->id, $campaignsToMergeIds)) {
            unset($campaignsToMergeIds[$key]);
        }

        Hooks::doAction('givewp_campaigns_merging', $destinationCampaign, $campaignsToMergeIds);

        DB::query('START TRANSACTION');

        try {
            // Convert $campaignsToMergeIds to string to use it in the queries
            $campaignsToMergeIdsString = implode(', ', $campaignsToMergeIds);

            // Migrate revenue entries from campaigns to merge to the destination campaign
            DB::query(
                DB::prepare("UPDATE " . DB::prefix('give_revenue') . " SET campaign_id = %d WHERE campaign_id IN ($campaignsToMergeIdsString)",
                    [
                        $destinationCampaign->id,
                    ])
            );

            // Migrate forms from campaigns to merge to the destination campaign
            DB::query(
                DB::prepare("UPDATE " . DB::prefix('give_campaign_forms') . " SET campaign_id = %d WHERE campaign_id IN ($campaignsToMergeIdsString)",
                    [
                        $destinationCampaign->id,
                    ])
            );

            // Delete campaigns to merge now that we already migrated the necessary data to the destination campaign
            DB::query("DELETE FROM " . DB::prefix('give_campaigns') . " WHERE id IN ($campaignsToMergeIdsString)");
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed merging campaigns into destination campaign', [
                'campaignsToMergeIds' => $campaignsToMergeIds,
                'destinationCampaign' => compact('destinationCampaign'),
            ]);

            throw new $exception('Failed merging campaigns into destination campaign');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaigns_merged', $destinationCampaign, $campaignsToMergeIds);

        return true;
    }

    /**
     * @unreleased
     */
    private function validateProperties(Campaign $campaign): void
    {
        foreach ($this->requiredProperties as $key) {
            if ( ! isset($campaign->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<Campaign>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(Campaign::class);

        return $builder->from('give_campaigns', 'campaigns')
            ->select(
                ['campaigns.id', 'id'],
                ['campaigns.form_id', 'defaultFormId'], // Prefix the `form_id` column to avoid conflicts with the `give_campaign_forms` table.
                ['campaign_type', 'type'],
                ['campaign_page_id', 'pageId'],
                ['enable_campaign_page', 'enableCampaignPage'],
                ['campaign_title', 'title'],
                ['short_desc', 'shortDescription'],
                ['long_desc', 'longDescription'],
                ['campaign_logo', 'logo'],
                ['campaign_image', 'image'],
                ['primary_color', 'primaryColor'],
                ['secondary_color', 'secondaryColor'],
                ['campaign_goal', 'goal'],
                ['goal_type', 'goalType'],
                'status',
                ['start_date', 'startDate'],
                ['end_date', 'endDate'],
                ['date_created', 'createdAt']
            )
            // Exclude Peer to Peer campaign type until it is fully supported.
            ->where('campaigns.campaign_type', CampaignType::PEER_TO_PEER, '!=');
    }
}

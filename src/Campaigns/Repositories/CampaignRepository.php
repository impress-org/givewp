<?php

namespace Give\Campaigns\Repositories;

use Give\Campaigns\Models\Campaign;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
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
     * Get Campaign by ID
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
                    'campaign_page_id' => $campaign->pageId,
                    'campaign_type' => $campaign->type->getValue(),
                    'campaign_title' => $campaign->title,
                    'short_desc' => $campaign->shortDescription,
                    'long_desc' => $campaign->longDescription,
                    'campaign_logo' => $campaign->logo,
                    'campaign_image' => $campaign->image,
                    'primary_color' => $campaign->primaryColor,
                    'secondary_color' => $campaign->secondaryColor,
                    'campaign_goal' => $campaign->goal,
                    'status' => $campaign->status->getValue(),
                    'start_date' => $startDateFormatted,
                    'end_date' => $endDateFormatted,
                    'date_created' => $dateCreatedFormatted
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
                    'campaign_page_id' => $campaign->pageId,
                    'campaign_title' => $campaign->title,
                    'short_desc' => $campaign->shortDescription,
                    'long_desc' => $campaign->longDescription,
                    'campaign_logo' => $campaign->logo,
                    'campaign_image' => $campaign->image,
                    'primary_color' => $campaign->primaryColor,
                    'secondary_color' => $campaign->secondaryColor,
                    'campaign_goal' => $campaign->goal,
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

        return $builder->from('give_campaigns')
            ->select(
                'id',
                ['campaign_page_id', 'pageId'],
                ['campaign_type', 'type'],
                ['campaign_title', 'title'],
                ['short_desc', 'shortDescription'],
                ['long_desc', 'longDescription'],
                ['campaign_logo', 'logo'],
                ['campaign_image', 'image'],
                ['primary_color', 'primaryColor'],
                ['secondary_color', 'secondaryColor'],
                ['campaign_goal', 'goal'],
                'status',
                ['start_date', 'startDate'],
                ['end_date', 'endDate'],
                ['date_created', 'createdAt']
            );
    }
}

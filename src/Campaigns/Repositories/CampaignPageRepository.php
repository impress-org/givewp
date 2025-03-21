<?php

namespace Give\Campaigns\Repositories;

use Give\Campaigns\Actions\CreateDefaultLayoutForCampaignPage;
use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\ValueObjects\CampaignPageStatus;
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
class CampaignPageRepository
{
    /**
     * @unreleased
     */
    protected $requiredProperties = [
        'campaignId',
    ];

    /**
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
     */
    public function findByCampaignId(int $campaignId): ?CampaignPage
    {
        return $this->prepareQuery()
            ->where('postmeta_attach_meta_campaignId.meta_value', $campaignId)
            ->get();
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function insert(CampaignPage $campaignPage): void
    {
        $this->validate($campaignPage);

        Hooks::doAction('givewp_campaign_page_creating', $campaignPage);

        $dateCreated = Temporal::withoutMicroseconds($campaignPage->createdAt ?: Temporal::getCurrentDateTime());
        $dateCreatedFormatted = Temporal::getFormattedDateTime($dateCreated);
        $dateUpdated = $campaignPage->updatedAt ?? $dateCreated;
        $dateUpdatedFormatted = Temporal::getFormattedDateTime($dateUpdated);
        $status = $campaignPage->status ?? CampaignPageStatus::PUBLISH();
        $campaign = $campaignPage->campaign();

        DB::query('START TRANSACTION');

        try {
            $campaignPage->id = wp_insert_post([
                'post_title' => $campaign->title,
                'post_name' => sanitize_title($campaign->title), // Slug
                'post_date' => $dateCreatedFormatted,
                'post_modified' => $dateUpdatedFormatted,
                'post_status' => 'publish',
                'post_type' => 'give_campaign_page',
                'post_content' => give(CreateDefaultLayoutForCampaignPage::class)($campaign->id,
                    $campaign->shortDescription),
            ]);

            if ( ! $campaignPage->id) {
                throw new Exception('Failed creating a campaign page');
            }

            $campaignPage->createdAt = $dateCreated;
            $campaignPage->updatedAt = $dateUpdated;
            $campaignPage->status = $status;

            DB::table('postmeta')
                ->insert([
                    'post_id' => $campaignPage->id,
                    'meta_key' => 'campaignId',
                    'meta_value' => $campaignPage->campaignId,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a campaign page', [$campaignPage]);

            throw new $exception('Failed creating a campaign page');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_page_created', $campaignPage);
    }

    /**
     * @unreleased
     */
    public function update(CampaignPage $campaignPage): void
    {
        $this->validate($campaignPage);

        Hooks::doAction('givewp_campaign_page_updating', $campaignPage);

        $now = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());
        $nowFormatted = Temporal::getFormattedDateTime($now);
        $status = $campaignPage->status ?? CampaignPageStatus::PUBLISH();

        DB::query('START TRANSACTION');

        try {
            DB::table('posts')
                ->where('ID', $campaignPage->id)
                ->update([
                    'post_modified' => $nowFormatted,
                    'post_modified_gmt' => get_gmt_from_date($nowFormatted),
                    'post_status' => $status->getValue(),
                    'post_type' => 'give_campaign_page',
                ]);

            $campaignPage->updatedAt = $now;
            $campaignPage->status = $status;

            DB::table('postmeta')
                ->where('post_id', $campaignPage->id)
                ->where('meta_key', 'campaignId')
                ->update([
                    'meta_value' => $campaignPage->campaignId,
                ]);
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed updating a campaign page', [$campaignPage]);

            throw new $exception('Failed updating a campaign page');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_page_updated', $campaignPage);
    }

    /**
     * @unreleased
     */
    public function delete(CampaignPage $campaignPage): bool
    {
        DB::query('START TRANSACTION');

        Hooks::doAction('givewp_campaign_page_deleting', $campaignPage);

        try {
            DB::table('posts')
                ->where('id', $campaignPage->id)
                ->delete();

            DB::table('postmeta')
                ->where('post_id', $campaignPage->id)
                ->delete();
        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed deleting a campaign page', [$campaignPage]);

            throw new $exception('Failed deleting a campaign page');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_page_deleted', $campaignPage);

        return true;
    }

    /**
     * @unreleased
     *
     * @return ModelQueryBuilder<CampaignPage>
     */
    public function prepareQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(CampaignPage::class);

        return $builder->from('posts')
            ->select(
                ['ID', 'id'],
                ['post_date', 'createdAt'],
                ['post_modified', 'updatedAt'],
                ['post_status', 'status']
            )
            ->attachMeta(
                'postmeta',
                'ID',
                'post_id',
                'campaignId'
            )
            ->where('post_type', 'give_campaign_page');
    }

    /**
     * @unreleased
     */
    public function validate(CampaignPage $campaignPage)
    {
        foreach ($this->requiredProperties as $key) {
            if ( ! isset($campaignPage->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }
}

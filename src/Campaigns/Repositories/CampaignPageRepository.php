<?php

namespace Give\Campaigns\Repositories;

use Give\Campaigns\Models\CampaignPage;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Hooks;
use Give\Log\Log;

class CampaignPageRepository
{
    protected $requiredProperties = [
        'campaignId',
    ];

    public function findByCampaignId(int $campaignId): ?CampaignPage
    {
        return $this->prepareQuery()
            ->where('postmeta_attach_meta_campaignId.meta_value', $campaignId)
            ->get();
    }

    public function insert(CampaignPage $campaignPage): void
    {
        $this->validate($campaignPage);

        Hooks::doAction('givewp_campaign_page_creating', $campaignPage);

        $dateCreated = Temporal::withoutMicroseconds($campaignPage->createdAt ?: Temporal::getCurrentDateTime());
        $dateCreatedFormatted = Temporal::getFormattedDateTime($dateCreated);
        $dateUpdated = $campaignPage->updatedAt ?? $dateCreated;
        $dateUpdatedFormatted = Temporal::getFormattedDateTime($dateUpdated);

        DB::query('START TRANSACTION');

        try {
            DB::table('posts')
                ->insert([
                    'post_date' => $dateCreatedFormatted,
                    'post_date_gmt' => get_gmt_from_date($dateCreatedFormatted),
                    'post_modified' => $dateUpdatedFormatted,
                    'post_modified_gmt' => get_gmt_from_date($dateUpdatedFormatted),
                    'post_status' => 'publish', // TODO: Update to value object
                    'post_type' => 'give_campaign_page',
                ]);

            $campaignPage->id = DB::last_insert_id();;
            $campaignPage->createdAt = $dateCreated;
            $campaignPage->updatedAt = $dateUpdated;

            DB::table('postmeta')
                ->insert([
                    'post_id' => $campaignPage->id,
                    'meta_key' => 'campaignId',
                    'meta_value' => $campaignPage->campaignId,
                ]);

        } catch (Exception $exception) {
            DB::query('ROLLBACK');

            Log::error('Failed creating a campaign page', [$campaignPage]);

            throw new $exception('Failed creating a donation');
        }

        DB::query('COMMIT');

        Hooks::doAction('givewp_campaign_page_created', $campaignPage);
    }

    public function update(CampaignPage $campaignPage): void
    {
        $this->validate($campaignPage);

        Hooks::doAction('givewp_campaign_page_updating', $campaignPage);

        $now = Temporal::withoutMicroseconds(Temporal::getCurrentDateTime());
        $nowFormatted = Temporal::getFormattedDateTime($now);

        DB::query('START TRANSACTION');

        try {
            DB::table('posts')
                ->where('ID', $campaignPage->id)
                ->update([
                    'post_modified' => $nowFormatted,
                    'post_modified_gmt' => get_gmt_from_date($nowFormatted),
                    'post_status' => 'publish', // TODO: Update to value object
                    'post_type' => 'give_campaign_page',
                ]);

            $campaignPage->updatedAt = $now;

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

    public function validate(CampaignPage $campaignPage)
    {
        foreach ($this->requiredProperties as $key) {
            if (!isset($campaignPage->$key)) {
                throw new InvalidArgumentException("'$key' is required.");
            }
        }
    }
}

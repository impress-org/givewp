<?php

namespace Give\Campaigns\Migrations;

use Give\Campaigns\CampaignsDataQuery;
use Give\Campaigns\ValueObjects\CampaignType;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\Framework\Migrations\Contracts\ReversibleMigration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 4.8.0
 */
class CacheCampaignsData extends BatchMigration implements ReversibleMigration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'cache_campaign_data';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Cache campaign data';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-07-25 00:00:00');
    }

    /**
     * Base query
     *
     * @since 4.8.0
     */
    protected function query(): QueryBuilder
    {
        return DB::table('give_campaigns')->where('campaign_type', CampaignType::CORE);
    }

    /**
     * @inheritDoc
     *
     * @since 4.8.0
     *
     * @throws DatabaseMigrationException
     */
    public function runBatch($firstId, $lastId)
    {
        try {
            $query = $this->query();

            // Migration Runner will pass null for lastId in the last step
            if (is_null($lastId)) {
                $query->where('id', $firstId, '>');
            } else {
                $query->whereBetween('id', $firstId, $lastId);
            }

            $campaigns = $query->getAll();
            $campaignIds = array_map(function ($campaign) {
                return $campaign->id;
            }, $campaigns);

            $donations = CampaignsDataQuery::donations($campaignIds);

            $campaignsData = get_option('give_campaigns_data', []);

            update_option('give_campaigns_data', [
                'amounts' => array_merge(
                    $campaignsData['amounts'] ?? [],
                    $donations->collectIntendedAmounts()
                ),
                'donationsCount' => array_merge(
                    $campaignsData['donationsCount'] ?? [],
                    $donations->collectDonations()
                ),
                'donorsCount' => array_merge(
                    $campaignsData['donationsCount'] ?? [],
                    $donations->collectDonors()
                ),
            ]);

            // Set subscriptions data
            if (defined('GIVE_RECURRING_VERSION')) {
                $subscriptionsData = get_option('give_campaigns_data', []);
                $subscriptions = CampaignsDataQuery::subscriptions($campaignIds);

                update_option('give_campaigns_subscriptions_data', [
                    'amounts' => array_merge(
                        $subscriptionsData['amounts'] ?? [],
                        $subscriptions->collectInitialAmounts()
                    ),
                    'donationsCount' => array_merge(
                        $subscriptionsData['donationsCount'] ?? [],
                        $subscriptions->collectDonations()
                    ),
                    'donorsCount' => array_merge(
                        $subscriptionsData['donorsCount'] ?? [],
                        $subscriptions->collectDonors()
                    ),
                ]);
            }

        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while caching campaign data", 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function getItemsCount(): int
    {
        return $this->query()->count();
    }

    /**
     * @inheritDoc
     */
    public function getBatchItemsAfter($lastId): ?array
    {
        $item = DB::get_row(sprintf(
            'SELECT MIN(id) AS first_id, MAX(id) AS last_id FROM (SELECT id FROM %1s WHERE id > %d ORDER BY id ASC LIMIT %d) as batch',
            DB::prefix('give_campaigns'),
            $lastId,
            $this->getBatchSize()
        ));

        if ( ! $item) {
            return null;
        }

        return [
            $item->first_id,
            $item->last_id,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getBatchSize(): int
    {
        return 10;
    }

    /**
     * @inheritDoc
     */
    public function hasMoreItemsToBatch($lastProcessedId): ?bool
    {
        return $this->query()
            ->where('id', $lastProcessedId, '>')
            ->limit(1)
            ->count();
    }

    /**
     * @inheritDoc
     *
     * @since 4.8.0
     */
    public function reverse(): void
    {
        delete_option('give_campaigns_data');
        delete_option('give_campaigns_subscriptions_data');
    }
}

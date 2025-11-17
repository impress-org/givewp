<?php

namespace Give\Campaigns\Migrations\Donations;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\Framework\Migrations\Contracts\ReversibleMigration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 4.0.0
 */
class AddCampaignId extends BatchMigration implements ReversibleMigration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'add_campaign_id_to_donations';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Add campaign id to donations';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2024-11-22 00:00:00');
    }

    /**
     * Base query
     *
     * @since 4.0.0
     */
    protected function query(): QueryBuilder
    {
        return DB::table('posts')->where('post_type', 'give_payment');
    }

    /**
     * @inheritDoc
     *
     * @since 4.0.0
     *
     * @throws DatabaseMigrationException
     */
    public function runBatch($firstId, $lastId)
    {
        $relationships = [];

        try {
            $data = DB::table('give_campaign_forms')
                ->select('campaign_id', 'form_id')
                ->getAll();

            foreach ($data as $relationship) {
                $relationships[$relationship->campaign_id][] = $relationship->form_id;
            }

            $query = $this->query()
                ->select('ID')
                ->attachMeta(
                    'give_donationmeta',
                    'ID',
                    'donation_id',
                    [DonationMetaKeys::FORM_ID(), 'formId']
                );

            // Migration Runner will pass null for lastId in the last step
            if (is_null($lastId)) {
                $query->where('ID', $firstId, '>');
            } else {
                $query->whereBetween('ID', $firstId, $lastId);
            }

            $donations = $query->getAll();

            $donationMeta = [];

            foreach ($donations as $donation) {
                foreach ($relationships as $campaignId => $formIds) {
                    if (in_array($donation->formId, $formIds)) {
                        $donationMeta[] = [
                            'donation_id' => $donation->ID,
                            'meta_key' => DonationMetaKeys::CAMPAIGN_ID,
                            'meta_value' => $campaignId,
                        ];

                        break;
                    }
                }
            }

            if ( ! empty($donationMeta)) {
                DB::table('give_donationmeta')
                    ->insert($donationMeta, ['%d', '%s', '%d']);
            }
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException("An error occurred while adding campaign ID to the donation meta table",
                0, $exception);
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
            'SELECT MIN(ID) AS first_id, MAX(ID) AS last_id FROM (SELECT ID FROM %1s WHERE ID > %d ORDER BY ID ASC LIMIT %d) as batch',
            DB::prefix('posts'),
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
        return 100;
    }

    /**
     * @inheritDoc
     */
    public function hasMoreItemsToBatch($lastProcessedId): ?bool
    {
        return $this->query()
            ->where('ID', $lastProcessedId, '>')
            ->limit(1)
            ->count();
    }

    /**
     * @inheritDoc
     *
     * @since 4.6.0
     */
    public function reverse(): void
    {
        DB::table('give_donationmeta')
            ->where('meta_key', DonationMetaKeys::CAMPAIGN_ID)
            ->delete();
    }
}

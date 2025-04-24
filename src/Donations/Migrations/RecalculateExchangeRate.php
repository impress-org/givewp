<?php

namespace Give\Donations\Migrations;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use Give\Framework\Migrations\Contracts\BatchMigration;
use Give\Framework\Migrations\Exceptions\DatabaseMigrationException;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @unreleased
 */
class RecalculateExchangeRate extends BatchMigration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'recalculate_exchange_rate';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Recalculate exchange rate';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-04-24 00:00:00');
    }

    /**
     * Base query to find donations that need exchange rate recalculation
     *
     * @unreleased
     */
    protected function query(): QueryBuilder
    {
        // Select donations where base currency is not empty,
        // base currency is different from payment currency,
        // and either the exchange rate does not exist or is equal to 1

        return DB::table('posts', 'posts')
            ->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                [DonationMetaKeys::AMOUNT, 'amount'],
                [DonationMetaKeys::CURRENCY, 'currency'],
                [DonationMetaKeys::BASE_AMOUNT, 'baseAmount'],
                [DonationMetaKeys::EXCHANGE_RATE, 'exchangeRate'],
                ['_give_cs_base_currency', 'baseCurrency']
            )
            ->where('posts.post_type', 'give_payment')
            ->whereIsNotNull('give_donationmeta_attach_meta_baseAmount.meta_value')
            ->whereRaw('AND give_donationmeta_attach_meta_baseCurrency.meta_value != give_donationmeta_attach_meta_currency.meta_value')
            ->where(function($query) {
                $query->where('give_donationmeta_attach_meta_exchangeRate.meta_value', '1')
                    ->orWhereIsNull('give_donationmeta_attach_meta_exchangeRate.meta_value');
            });
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function runBatch($firstId, $lastId): void
    {
        try {
            $query = $this->query()
                ->select('posts.ID')
                ->orderBy('posts.ID', 'ASC');

            // Migration Runner will pass null for lastId in the last step
            if (is_null($lastId)) {
                $query->where('posts.ID', $firstId, '>');
            } else {
                $query->whereBetween('posts.ID', $firstId, $lastId);
            }

            $donations = $query->getAll();

            foreach ($donations as $donation) {
                try {
                    $amount = Money::fromDecimal($donation->amount, $donation->currency);
                    $baseAmount = Money::fromDecimal($donation->baseAmount, $donation->baseCurrency);

                    if ($baseAmount->formatToDecimal() === '0') {
                        throw new DatabaseMigrationException(
                            sprintf(
                                'Invalid base amount (0) for donation ID %d',
                                $donation->ID
                            )
                        );
                    }

                    /** @var Money $exchangeRate */
                    $exchangeRate = $amount->divide($baseAmount->formatToDecimal());

                    give()->payment_meta->update_meta($donation->ID, DonationMetaKeys::EXCHANGE_RATE, $exchangeRate->formatToDecimal());
                } catch (\Exception $e) {
                    throw new DatabaseMigrationException(
                        sprintf(
                            'Failed to process donation ID %d: %s',
                            $donation->ID,
                            $e->getMessage()
                        ),
                        0,
                        $e
                    );
                }
            }
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException(
                'An error occurred while updating donation exchange rates',
                0,
                $exception
            );
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
        $item = clone $this->query()
            ->select('MIN(posts.ID) AS first_id, MAX(posts.ID) AS last_id')
            ->where('posts.ID', $lastId, '>')
            ->orderBy('posts.ID', 'ASC')
            ->limit($this->getBatchSize())
            ->get();

        if (!$item) {
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
            ->where('posts.ID', $lastProcessedId, '>')
            ->limit(1)
            ->count();
    }
}

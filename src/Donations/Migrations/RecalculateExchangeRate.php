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
 * @since 4.2.0
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
     * @since 4.2.0
     */
    protected function query(): QueryBuilder
    {
        return DB::table('posts', 'posts')
            ->where('posts.post_type', 'give_payment')
            ->orderBy('posts.ID', 'ASC');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function runBatch($firstId, $lastId): void
    {
        try {
            // Select donations where base currency is not empty,
            // base currency is different from payment currency,
            // and either the exchange rate does not exist or is equal to 1

            $query = $this->query()
                ->select('posts.ID')
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
                ->whereIsNotNull('give_donationmeta_attach_meta_baseAmount.meta_value')
                ->whereRaw('AND give_donationmeta_attach_meta_baseCurrency.meta_value != give_donationmeta_attach_meta_currency.meta_value')
                ->where(function ($query) {
                    $query->where('give_donationmeta_attach_meta_exchangeRate.meta_value', '1')
                        ->orWhereIsNull('give_donationmeta_attach_meta_exchangeRate.meta_value');
                });

            // Migration Runner will pass null for lastId in the last step
            if (is_null($lastId)) {
                $query->where('posts.ID', $firstId, '>');
            } else {
                $query->whereBetween('posts.ID', $firstId, $lastId);
            }

            $donations = $query->getAll();

            foreach ($donations as $donation) {
                $amount = Money::fromDecimal($donation->amount, $donation->currency);
                $baseAmount = Money::fromDecimal($donation->baseAmount, $donation->baseCurrency);

                if ($baseAmount->formatToDecimal() === '0') {
                    continue;
                }

                /** @var Money $exchangeRate */
                $exchangeRate = $amount->divide($baseAmount->formatToDecimal());

                give()->payment_meta->update_meta($donation->ID, DonationMetaKeys::EXCHANGE_RATE, $exchangeRate->formatToDecimal());
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
        $subquerySQL = $this->query()
            ->where('ID', $lastId, '>')
            ->limit($this->getBatchSize())
            ->getSQL();

        $item = DB::get_row("SELECT MIN(ID) AS first_id, MAX(ID) AS last_id FROM ($subquerySQL) as batch");

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

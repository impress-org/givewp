<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use DateTimeInterface;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\DateFormatter;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donor>
 */
class LatestDonationColumn extends ModelColumn
{
    /**
     * The latest donation date by donor id, so the raw value reuses the query of the cell.
     *
     * @since TBD
     *
     * @var array<int, DateTimeInterface|null>
     */
    private $latestDates = [];

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'latestDonation';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Latest Donation', 'give');
    }

    /**
     * @since TBD Format with the shared date formatter; no donations show the dash.
     * @since 4.13.0 updated date format
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model): string
    {
        return DateFormatter::dateTime($this->latestDate($model));
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::DATE;
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getRawValue($model)
    {
        return DateFormatter::iso($this->latestDate($model));
    }

    /**
     * Finds the latest donation date once per donor.
     *
     * @since TBD
     */
    private function latestDate(Donor $donor): ?DateTimeInterface
    {
        if (!array_key_exists($donor->id, $this->latestDates)) {
            $latestDonation = give()->donors->getDonorLatestDonationDate($donor->id);

            $this->latestDates[$donor->id] = $latestDonation ? Temporal::toDateTime($latestDonation) : null;
        }

        return $this->latestDates[$donor->id];
    }
}

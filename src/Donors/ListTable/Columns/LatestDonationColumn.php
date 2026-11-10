<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use DateTimeInterface;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorsDataRepository;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\DateFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donor>
 */
class LatestDonationColumn extends ModelColumn
{
    /**
     * Data loaded here, one donor at a time, when the list table has no page data.
     *
     * @since TBD
     *
     * @var array<int, DonorsDataRepository>
     */
    private $fallbackData = [];

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
     * @since TBD Read the date from the data the endpoint loads for the whole page.
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
     * Reads the latest donation date from the data the endpoint loaded for the whole page.
     *
     * @since TBD
     */
    private function latestDate(Donor $donor): ?DateTimeInterface
    {
        return $this->dataFor($donor)->getLatestDonationDate($donor->id);
    }

    /**
     * The page data, or data for this donor alone when the column is used without page data.
     *
     * @since TBD
     */
    private function dataFor(Donor $donor): DonorsDataRepository
    {
        $data = $this->getListTableData();

        if ($data instanceof DonorsDataRepository) {
            return $data;
        }

        if (!isset($this->fallbackData[$donor->id])) {
            $this->fallbackData[$donor->id] = DonorsDataRepository::donors([$donor->id]);
        }

        return $this->fallbackData[$donor->id];
    }
}

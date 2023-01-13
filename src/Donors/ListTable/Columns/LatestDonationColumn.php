<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Helpers\Date;

/**
 * @unreleased
 *
 * @extends ModelColumn<Donor>
 */
class LatestDonationColumn extends ModelColumn
{

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'latestDonation';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Latest Donation', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model): string
    {
        $latestDonation = give()->donors->getDonorLatestDonationDate($model->id);

        if (!$latestDonation) {
            return '-';
        }

        $format = _x('m/d/Y \a\t g:i a', 'human-readable datetime format', 'give');

        return Temporal::toDateTime($latestDonation)->format($format);
    }
}

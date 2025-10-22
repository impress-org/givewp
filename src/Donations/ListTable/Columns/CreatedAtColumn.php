<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donation>
 */
class CreatedAtColumn extends ModelColumn
{

    protected $sortColumn = 'createdAt';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'createdAt';
    }

    /**
     * @since 4.10.0 Updated column label
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Date', 'give');
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');
        $format = sprintf('%s \a\t %s', $dateFormat, $timeFormat);

        return wp_date($format, $model->createdAt->getTimestamp(), wp_timezone());
    }
}

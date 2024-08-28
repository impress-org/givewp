<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class DonationsCountColumn extends ModelColumn
{
    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'donationsCount';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Donations', 'give');
    }

    /**
     * @unreleased
     */
    public function getCellValue($model): string
    {
        return (string)$model->query()->count(); //Temp count
    }
}

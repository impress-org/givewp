<?php

declare(strict_types=1);

namespace Give\DonationForms\ListTable\Columns;

use Give\DonationForms\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<DonationForm>
 */
class IdColumn extends ModelColumn
{

    protected $sortColumn = 'id';

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'id';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('ID', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model): int
    {
        return $model->id;
    }
}

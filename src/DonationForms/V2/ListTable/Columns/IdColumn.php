<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<DonationForm>
 */
class IdColumn extends ModelColumn
{

    protected $sortColumn = 'id';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'id';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('ID', 'give');
    }

    /**
     * @since 2.24.0
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

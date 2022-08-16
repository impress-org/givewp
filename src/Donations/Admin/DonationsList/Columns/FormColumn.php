<?php

declare(strict_types=1);

namespace Give\Donations\Admin\DonationsList\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class FormColumn extends ModelColumn
{
    public $sortColumn = 'formTitle';

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return 'form';
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donation Form', 'give');
    }

    /**
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        return $model->formTitle;
    }
}

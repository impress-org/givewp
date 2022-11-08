<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Donation>
 */
class AmountColumn extends ModelColumn
{

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'amount';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Amount', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return sprintf(
            '<div class="amount"><span>%s</span></div>',
            $model->amount->formatToLocale($locale)
        );
    }
}

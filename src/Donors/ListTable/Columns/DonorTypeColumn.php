<?php

declare(strict_types=1);

namespace Give\Donors\ListTable\Columns;

use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorType;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Donor>
 */
class DonorTypeColumn extends ModelColumn
{
    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'donorType';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donor Type', 'give');
    }

    /**
     * @since 4.10.0 Removed icon
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Donor $model
     */
    public function getCellValue($model): string
    {
        $donorType = give()->donors->getDonorType($model->id);

        $template = '<div class="badge badge--%1$s"><p>%2$s</p></div>';

        return sprintf(
            $template,
            $donorType->getValue(),
            $donorType->label()
        );
    }
}

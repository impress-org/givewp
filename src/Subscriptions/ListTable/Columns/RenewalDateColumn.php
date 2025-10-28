<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Subscriptions\Models\Subscription;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Subscription>
 */
class RenewalDateColumn extends ModelColumn
{

    protected $sortColumn = 'renewsAt';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'renewalDate';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Next payment date', 'give');
    }

    /**
     * @unreleased updated the date format
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Subscription $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return Temporal::getFormattedDateTimeUsingTimeZoneAndFormatSettings($model->renewsAt);
    }
}

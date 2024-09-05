<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class StatusColumn extends ModelColumn
{
    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'status';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Status', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model)
    {
        switch ($model->status->getValue()) {
            case 'active':
                $status = __('Active', 'give');
                break;
            case 'inactive':
                $status = __('Inactive', 'give');
                break;
            case 'draft':
                $status = __('Draft', 'give');
                break;
            case 'pending':
                $status = __('Pending', 'give');
                break;
            case 'processing':
                $status = __('Processing', 'give');
                break;
            case 'failed':
                $status = __('Failed', 'give');
                break;
            default:
                $status = __('Draft', 'give');
        }

        return $status;
    }
}

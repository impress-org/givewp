<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.0.0
 */
class StatusColumn extends ModelColumn
{
    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'status';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('Status', 'give');
    }

    /**
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model)
    {
        switch ($model->status->getValue()) {
            case 'active':
                $statusLabel = __('Active', 'give');
                break;
            case 'inactive':
                $statusLabel = __('Inactive', 'give');
                break;
            case 'archived':
                $statusLabel = __('Archived', 'give');
                break;
            case 'draft':
                $statusLabel = __('Draft', 'give');
                break;
            case 'pending':
                $statusLabel = __('Pending', 'give');
                break;
            case 'processing':
                $statusLabel = __('Processing', 'give');
                break;
            case 'failed':
                $statusLabel = __('Failed', 'give');
                break;
            default:
                $statusLabel = __('Draft', 'give');
        }

        return sprintf(
            '<div class="statusBadge statusBadge--%1$s"><p>%2$s</p></div>',
            $model->status->getValue(),
            $statusLabel
        );
    }
}

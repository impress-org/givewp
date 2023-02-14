<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Framework\ListTable\ModelColumn;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Subscription>
 */
class FormColumn extends ModelColumn
{
    protected $sortColumn = 'donationFormId';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'form';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donation form', 'give');
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Subscription $model
     */
    public function getCellValue($model): string
    {
        $form = give()->donationForms->getById($model->donationFormId);

        if ( ! $form) {
            return sprintf( __( 'Form #%d', 'give' ), $model->donationFormId );
        }

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("post.php?post={$model->donationFormId}&action=edit"),
            __('Visit donation form page', 'give'),
            $form->title
        );
    }
}

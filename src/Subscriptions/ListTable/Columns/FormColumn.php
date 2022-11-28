<?php

declare(strict_types=1);

namespace Give\Subscriptions\ListTable\Columns;

use Give\Subscriptions\Models\Subscription;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Subscription>
 */
class FormColumn extends ModelColumn
{

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'form';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Donation form', 'give');
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     *
     * @param Subscription $model
     */
    public function getCellValue($model): string
    {
        $form = give()->donationForms->getById($model->donationFormId);

        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("post.php?post={$model->donationFormId}&action=edit"),
            __('Visit donation form page', 'give'),
            $form->title
        );
    }
}

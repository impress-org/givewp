<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @extends ModelColumn<Donation>
 */
class FormColumn extends ModelColumn
{

    protected $sortColumn = 'formTitle';

    /**
     * @inheritDoc
     */
    public static function getId(): string
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
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url( "post.php?post={$model->formId}&action=edit" ),
            __( 'Visit donation form page', 'give' ),
            $model->formTitle
        );
    }
}

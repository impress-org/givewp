<?php

declare(strict_types=1);

namespace Give\Donations\ListTable\Columns;

use Give\Donations\Models\Donation;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since TBD
 *
 * @extends ModelColumn<Donation>
 */
class FormColumn extends ModelColumn
{
    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'form';
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Form', 'give');
    }

    /**
     * The title comes from the donation record, so no query runs per row. post.php redirects v3 forms to the
     * form builder, so one edit link covers both form types.
     *
     * @since TBD
     *
     * @inheritDoc
     *
     * @param Donation $model
     */
    public function getCellValue($model): string
    {
        $title = esc_html(wp_strip_all_tags((string)$model->formTitle));

        if ( ! $model->formId || ! current_user_can('edit_give_forms')) {
            return $title;
        }

        return sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url("post.php?post={$model->formId}&action=edit")),
            $title
        );
    }
}

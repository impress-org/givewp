<?php

namespace Give\Framework\ListTable\FilterOptions;

use Give\Framework\Database\DB;
use Give\Framework\Permissions\Facades\UserPermissions;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * Donation forms that are published, draft, pending or private. A draft form has a "(Draft)" suffix.
 *
 * @since TBD
 */
class FormFilterOptions extends AbstractFilterOptionSource
{
    /**
     * @since TBD
     */
    public function currentUserCanView(): bool
    {
        return UserPermissions::donationForms()->canView();
    }

    /**
     * @since TBD
     */
    protected function query(): QueryBuilder
    {
        return DB::table('posts')
            ->where('post_type', 'give_forms')
            ->whereIn('post_status', ['publish', 'draft', 'pending', 'private']);
    }

    /**
     * @since TBD
     */
    protected function idColumn(): string
    {
        return 'ID';
    }

    /**
     * @since TBD
     */
    protected function labelColumn(): string
    {
        return 'post_title';
    }

    /**
     * @since TBD
     */
    protected function selectColumns(QueryBuilder $query): QueryBuilder
    {
        return parent::selectColumns($query)->select('post_status');
    }

    /**
     * @since TBD
     */
    protected function optionLabel(string $label, object $row): string
    {
        $title = parent::optionLabel($label, $row);

        return $row->post_status === 'draft'
            ? sprintf(__('%s (Draft)', 'give'), $title)
            : $title;
    }
}

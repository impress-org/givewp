<?php

namespace Give\Framework\ListTable\FilterOptions;

use Give\Framework\Database\DB;
use Give\Framework\Permissions\Facades\UserPermissions;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * Every donor, by name. Only the id and the name are read: never the email, phone or address.
 *
 * @since TBD
 */
class DonorFilterOptions extends AbstractFilterOptionSource
{
    /**
     * @since TBD
     */
    public function currentUserCanView(): bool
    {
        return UserPermissions::donors()->canView();
    }

    /**
     * @since TBD
     */
    protected function query(): QueryBuilder
    {
        return DB::table('give_donors');
    }

    /**
     * @since TBD
     */
    protected function idColumn(): string
    {
        return 'id';
    }

    /**
     * @since TBD
     */
    protected function labelColumn(): string
    {
        return 'name';
    }
}

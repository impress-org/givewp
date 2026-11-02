<?php

namespace Give\Framework\ListTable\FilterOptions;

use Give\Framework\Database\DB;
use Give\Framework\Permissions\Facades\UserPermissions;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * Every campaign, in any status.
 *
 * @since TBD
 */
class CampaignFilterOptions extends AbstractFilterOptionSource
{
    /**
     * @since TBD
     */
    public function currentUserCanView(): bool
    {
        return UserPermissions::campaigns()->canView();
    }

    /**
     * @since TBD
     */
    protected function query(): QueryBuilder
    {
        return DB::table('give_campaigns');
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
        return 'campaign_title';
    }
}

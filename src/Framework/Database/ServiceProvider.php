<?php

declare(strict_types=1);

namespace Give\Framework\Database;

use Give\Framework\Database\Actions\EnableBigSqlSelects;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;

class ServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('givewp_db_pre_query', EnableBigSqlSelects::class);
    }
}

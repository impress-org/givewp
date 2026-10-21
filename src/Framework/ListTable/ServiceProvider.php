<?php

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Actions\RegisterListTableAssets;
use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @since TBD
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @since TBD
     */
    public function register()
    {
    }

    /**
     * Registers before the list screens enqueue their scripts, which happens at the default priority.
     *
     * @since TBD
     */
    public function boot()
    {
        Hooks::addAction('admin_enqueue_scripts', RegisterListTableAssets::class, '__invoke', 5);
    }
}

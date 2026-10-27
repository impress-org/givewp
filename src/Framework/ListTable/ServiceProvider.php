<?php

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Actions\RegisterListTableAssets;
use Give\Framework\ListTable\Layouts\ListTableLayoutController;
use Give\Framework\ListTable\Layouts\ListTableLayoutStore;
use Give\Framework\ListTable\Layouts\ListTableLayoutValidator;
use Give\Framework\ListTable\Layouts\ListTableScreens;
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
        give()->singleton(ListTableScreens::class);
        give()->singleton(ListTableLayoutStore::class);
        give()->singleton(ListTableLayoutValidator::class);
    }

    /**
     * Registers before the list screens enqueue their scripts, which happens at the default priority.
     *
     * @since TBD
     */
    public function boot()
    {
        Hooks::addAction('admin_enqueue_scripts', RegisterListTableAssets::class, '__invoke', 5);
        Hooks::addAction('rest_api_init', ListTableLayoutController::class, 'registerRoutes');
    }
}

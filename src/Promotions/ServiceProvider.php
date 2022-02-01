<?php

namespace Give\Promotions;

use Give\Helpers\Hooks;
use Give\Promotions\FreeAddonModal\Controller as FreeAddonModalController;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;

class ServiceProvider implements ServiceProviderContract
{
    public function register()
    {
    }

    public function boot()
    {
        $this->bootFreeAddonModal();
    }

    private function bootFreeAddonModal()
    {
        Hooks::addAction('admin_enqueue_scripts', FreeAddonModalController::class, 'enqueueScripts');
    }
}

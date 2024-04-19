<?php

namespace Give\NotificationsHandler;

use Give\Helpers\Hooks;
use Give\NotificationsHandler\Routes\DismissNotification;
use Give\NotificationsHandler\Routes\GetNotifications;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface
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
        Hooks::addAction('rest_api_init', GetNotifications::class, 'registerRoute');
        Hooks::addAction('rest_api_init', DismissNotification::class, 'registerRoute');

        // Localize scripts
        Hooks::addAction( 'admin_enqueue_scripts', Assets::class );
    }
}

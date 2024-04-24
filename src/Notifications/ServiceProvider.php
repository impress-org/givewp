<?php

namespace Give\Notifications;

use Give\Helpers\Hooks;
use Give\Notifications\Routes\DismissibleNotifications;
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
    public function boot(): void
    {
        // Dismissible notifications
        Hooks::addAction('rest_api_init', DismissibleNotifications\GetNotifications::class, 'registerRoute');
        Hooks::addAction('rest_api_init', DismissibleNotifications\DismissNotification::class, 'registerRoute');

        // Load assets
        Hooks::addAction('admin_enqueue_scripts', Assets::class);
    }
}

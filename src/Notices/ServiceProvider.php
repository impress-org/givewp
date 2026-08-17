<?php

declare(strict_types=1);

namespace Give\Notices;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;

/**
 * @since TBD
 */
class ServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     *
     * @since TBD
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     *
     * @since TBD
     */
    public function boot()
    {
        Hooks::addAction('admin_init', WordPressCoreUpdateNotice::class, 'handleDismissal');
        Hooks::addAction('admin_init', WordPressCoreUpdateNotice::class, 'registerNotice');
    }
}

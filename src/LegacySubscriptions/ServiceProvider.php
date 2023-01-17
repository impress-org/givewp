<?php

namespace Give\LegacySubscriptions;

use Closure;
use Give\Helpers\Hooks;
use Give\LegacySubscriptions\Actions\EnsureSubscriptionHasPaymentMode;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * Class ServiceProvider - LegacySubscriptions
 *
 * This handles the loading of all the legacy codebase included in the LegacySubscriptions /includes directory.
 * DO NOT EXTEND THIS WITH NEW CODE as it is intended to shrink over time as we migrate over
 * to the new ways of doing things.
 *
 * @since 2.19.0
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        $recurringIsInstalled = defined('GIVE_RECURRING_VERSION') && GIVE_RECURRING_VERSION;
        $recurringMeetsRequirements = $recurringIsInstalled && version_compare(GIVE_RECURRING_VERSION, '1.14.1', '>');

        if ($recurringMeetsRequirements || !$recurringIsInstalled) {
            $this->includeLegacyFiles();
            $this->bindClasses();
        }

        $this->includeLegacyHelpers();
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('give_subscription_post_create', EnsureSubscriptionHasPaymentMode::class, '__invoke', 10, 2);
    }

    /**
     * Load all the legacy class files since they don't have autoloading
     *
     * @since 2.19.0
     */
    private function includeLegacyFiles()
    {
        require_once __DIR__ . '/includes/give-subscriptions-db.php';
        require_once __DIR__ . '/includes/give-recurring-db-subscription-meta.php';
        require_once __DIR__ . '/includes/give-recurring-cache.php';
        require_once __DIR__ . '/includes/give-subscription.php';
        require_once __DIR__ . '/includes/give-subscriptions-api.php';
        require_once __DIR__ . '/includes/give-recurring-subscriber.php';
        require_once __DIR__ . '/includes/give-recurring-cron.php';
    }

    /**
     * Load all the legacy helpers
     *
     * @since 2.19.0
     */
    private function includeLegacyHelpers()
    {
        require_once __DIR__ . '/includes/give-recurring-helpers.php';
    }

    /**
     * Binds the legacy classes to the service provider
     *
     * @since 2.19.0
     */
    private function bindClasses()
    {
        $this->bindInstance(
            'subscription_meta',
            'Give_Recurring_DB_Subscription_Meta',
            'give-recurring-db-subscription-meta.php'
        );
    }

    /**
     * A helper for loading legacy classes that do not use autoloading, then binding their instance
     * to the container.
     *
     * @since 2.19.0
     *
     * @param string $alias
     * @param string|Closure $class
     * @param string $includesPath
     * @param bool $singleton
     */
    private function bindInstance($alias, $class, $includesPath, $singleton = false)
    {
        require_once __DIR__ . "/includes/$includesPath";

        if ($class instanceof Closure) {
            give()->instance($alias, $class());
        } elseif ($singleton) {
            give()->instance($alias, $class::get_instance());
        } else {
            give()->instance($alias, new $class());
        }
    }
}

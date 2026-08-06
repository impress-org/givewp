<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Harbor\Actions;

use Give\Vendors\LiquidWeb\Harbor\Config;
use Give\Vendors\LiquidWeb\Harbor\Premium_Plugin_Registry;

/**
 * Whether Harbor should load its premium licensing experience.
 *
 * Mirrors Harbor::init() checking Premium_Plugin_Registry::any().
 *
 * @since @unreleased
 */
class HarborPremiumPluginsExist
{
    /**
     * @since @unreleased
     */
    public function __invoke(): bool
    {
        if (! class_exists(Premium_Plugin_Registry::class) || ! Config::has_container()) {
            return false;
        }

        return Config::get_container()->get(Premium_Plugin_Registry::class)->any();
    }
}

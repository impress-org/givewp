<?php

/**
 * Freezes time() for the prefixed stellarwp/banners library, which calls it unqualified.
 * Load it before any banner renders: PHP resolves each call site once.
 */

namespace Give\Tests\Unit\Promotions\NexcessBanner {

    /**
     * @since 4.19.0
     */
    class BannersClock
    {
        /**
         * The Unix timestamp the library sees, or null for the real time.
         *
         * @since 4.19.0
         *
         * @var int|null
         */
        public static $now = null;
    }
}

namespace Give\Vendors\Nexcess\Banners {

    use Give\Tests\Unit\Promotions\NexcessBanner\BannersClock;

    /**
     * @since 4.19.0
     */
    function time(): int
    {
        return BannersClock::$now ?? \time();
    }
}

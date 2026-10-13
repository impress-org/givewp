<?php

/**
 * A clock for the vendored stellarwp/banners library, which reads the time through unqualified
 * time() calls in its prefixed namespace. Declaring time() in that namespace shadows PHP's, the
 * technique Symfony's ClockMock uses. GiveWP's test environment has no uopz to mock time() itself.
 *
 * PHP resolves each call site once, so this file must load before any banner renders: the test
 * requires it when PHPUnit loads the test file.
 */

namespace Give\Tests\Unit\Promotions\NexcessBanner {

    /**
     * @since TBD
     */
    class BannersClock
    {
        /**
         * The Unix timestamp the library sees, or null for the real time.
         *
         * @since TBD
         *
         * @var int|null
         */
        public static $now = null;
    }
}

namespace Give\Vendors\Nexcess\Banners {

    use Give\Tests\Unit\Promotions\NexcessBanner\BannersClock;

    /**
     * @since TBD
     */
    function time(): int
    {
        return BannersClock::$now ?? \time();
    }
}

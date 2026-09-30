<?php

declare(strict_types=1);

namespace Give\Tests\Unit\VendorOverrides\Harbor;

use Give\Tests\TestCase;
use Give\VendorOverrides\Harbor\HarborServiceProvider;

/**
 * @since TBD
 * @coversDefaultClass \Give\VendorOverrides\Harbor\HarborServiceProvider
 */
class TestHarborServiceProvider extends TestCase
{
    /**
     * Plugin activation loads the service providers after wp_loaded, as the test suite does.
     *
     * @since TBD
     * @covers ::register
     * @covers ::boot
     */
    public function testLoadingAfterWpLoadedDoesNotRaiseDoingItWrong(): void
    {
        $this->assertGreaterThan(0, did_action('wp_loaded'));

        $incorrectUsages = [];
        $collect = static function ($function) use (&$incorrectUsages) {
            $incorrectUsages[] = $function;
        };
        add_action('doing_it_wrong_run', $collect);

        $provider = new HarborServiceProvider();
        $provider->register();
        $provider->boot();

        remove_action('doing_it_wrong_run', $collect);

        $this->assertSame([], $incorrectUsages);
    }
}

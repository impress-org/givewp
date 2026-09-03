<?php

namespace Give\Tests\TestTraits;

use Error;

/**
 * Runs code up to the redirect it issues and hands back where it was going.
 *
 * wp_redirect() is followed by exit, so the only seam is the wp_redirect filter. What it throws must
 * not be an Exception: the legacy adapter, the donate route and GatewayRoute all catch Exception on
 * the way out and would turn it into an error response.
 *
 * @since 4.16.8
 */
trait InterruptsRedirects
{
    /**
     * @since 4.16.8
     */
    protected function captureRedirect(callable $callback): string
    {
        $interrupt = static function (string $location) {
            throw new class($location) extends Error {
                public $location;

                public function __construct(string $location)
                {
                    parent::__construct("Redirect to $location");
                    $this->location = $location;
                }
            };
        };

        add_filter('wp_redirect', $interrupt);
        $bufferLevel = ob_get_level();

        try {
            $callback();
        } catch (Error $error) {
            if (property_exists($error, 'location')) {
                return $error->location;
            }

            throw $error;
        } finally {
            remove_filter('wp_redirect', $interrupt);

            // The legacy processor opens a buffer around the gateway call that the interrupt skips closing.
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }

        $this->fail('Expected a redirect.');
    }
}

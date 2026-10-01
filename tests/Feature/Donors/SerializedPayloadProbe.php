<?php

namespace Give\Tests\Feature\Donors;

/**
 * Records whether PHP ever built an instance of it. Restoring a stored meta value must never reach
 * these magic methods, so the flag staying false is what DonorWallTest asserts.
 *
 * @since TBD
 */
class SerializedPayloadProbe
{
    /**
     * @var bool
     */
    public static $instantiated = false;

    public function __wakeup()
    {
        self::$instantiated = true;
    }

    public function __destruct()
    {
        self::$instantiated = true;
    }
}

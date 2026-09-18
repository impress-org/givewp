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
     * @since TBD
     *
     * @var bool
     */
    public static $instantiated = false;

    /**
     * @since TBD
     */
    public function __wakeup()
    {
        self::$instantiated = true;
    }

    /**
     * @since TBD
     */
    public function __destruct()
    {
        self::$instantiated = true;
    }
}

<?php

namespace Give\Framework\Shims;

/**
 * @since 2.19.5
 */
class Shim
{
    /**
     * @since 2.19.5
     *
     * @param string $filename
     * @return void
     */
    public static function load( $filename )
    {
        require_once trailingslashit(__DIR__) . "{$filename}.php";
    }
}

<?php

namespace Give\Framework\Shims;

class Shim
{
    /**
     * @param string $filename
     * @return void
     */
    public static function load( $filename )
    {
        require_once trailingslashit(__DIR__) . "{$filename}.php";
    }
}

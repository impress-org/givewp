<?php 

namespace Give\Helpers;

use ReflectionClass;
use ReflectionProperty;

/**
 * Class Utils
 *
 * @package Give\Helpers
 */

final class ReflectionUtil 
{
    public static function getProperty(ReflectionClass $reflection, string $name): ReflectionProperty
    {
        $prop = $reflection->getProperty($name);
        if(PHP_VERSION_ID < 80100){
            $prop->setAccessible(true);
        }
        return $prop;
    }
}
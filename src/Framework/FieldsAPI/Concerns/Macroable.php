<?php

namespace Give\Framework\FieldsAPI\Concerns;

use BadMethodCallException;
use Closure;

/**
 * @since 2.17.0
 */
trait Macroable
{

    /** @var array */
    protected static $macros = [];

    /**
     * Add a macro to the class.
     *
     * @since 2.17.0
     *
     * @param string   $name
     * @param callable $macro
     *
     * @return void
     */
    public static function macro($name, callable $macro)
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Check if the class has the named macro.
     *
     * @since 2.17.0
     *
     * @param string $name
     *
     * @return bool
     */
    public static function hasMacro($name)
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Call the macro
     *
     * @since 2.17.0
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($method, array $parameters)
    {
        if ( ! static::hasMacro($method)) {
            throw new BadMethodCallException(
                sprintf(
                    'Method %s::%s does not exist',
                    $method,
                    static::class
                )
            );
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }
}

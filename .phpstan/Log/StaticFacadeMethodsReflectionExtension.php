<?php

namespace Give\PHPStan\Log;

use Give\Log\Log;
use Give\PHPStan\Log\Methods\MagicCallStaticMethodReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;

/**
 * A custom extension for PHPStan that adds support for the \Give\Log\Log static facade methods.
 * These dynamic static methods are not otherwise discovered by PHPStan.
 *
 * @link https://phpstan.org/developing-extensions/class-reflection-extensions
 *       Classes in PHP can expose "magic" properties and methods decided in run-time using class methods
 *       like __get, __set, and __call. Because PHPStan is all about static analysis (testing code for errors without running it),
 *       it has to know about those properties and methods beforehand.
 *
 * @since 2.27.0
 */
class StaticFacadeMethodsReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if ($classReflection->getName() !== Log::class) {
            return false;
        }

        return in_array($methodName, [
            'error',
            'warning',
            'notice',
            'success',
            'info',
            'http',
            'spam',
            'debug',
        ]);
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new MagicCallStaticMethodReflection($classReflection, $methodName);
    }
}


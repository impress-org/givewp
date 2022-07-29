<?php

namespace Give\PHPStan\Reflection;

use Give\Log\Log;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;

class LogMethodsReflectionExtension implements MethodsClassReflectionExtension
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
        return new LogStaticMethodReflection($classReflection, $methodName);
    }
}


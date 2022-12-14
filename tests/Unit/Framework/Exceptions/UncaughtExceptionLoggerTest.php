<?php

namespace Give\Tests\Unit\Framework\Exceptions;

use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\UncaughtExceptionLogger;
use Give\Log\Log;
use Give\Tests\TestCase;
use PHPUnit_Framework_MockObject_MockBuilder;

class UncaughtExceptionLoggerTest extends TestCase
{
    public function testShouldLogException()
    {
        $logger = new UncaughtExceptionLogger();

        $this->mock(Log::class, function (PHPUnit_Framework_MockObject_MockBuilder $builder) {
            $mock = $builder->setMethods(['error'])->getMock();

            $mock->expects($this->once())
                ->method('error')
                ->with('', []);

            return $mock;
        });

        $this->expectException(ExceptionLogged::class);

        $logger->handleException(new ExceptionLogged());
    }
}

class ExceptionLogged extends \Exception implements LoggableException
{
    public function getLogMessage(): string
    {
        return '';
    }

    public function getLogContext(): array
    {
        return [];
    }
}

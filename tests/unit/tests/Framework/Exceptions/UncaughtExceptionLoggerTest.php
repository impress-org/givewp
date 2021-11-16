<?php

use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\UncaughtExceptionLogger;
use Give\Log\Helpers\Environment;
use Give\Log\Log;

class UncaughtExceptionLoggerTest extends Give_Unit_Test_Case
{
    public function testShouldLogException()
    {
        $logger = new UncaughtExceptionLogger();

        $this->mock(Environment::class, function (PHPUnit_Framework_MockObject_MockBuilder $builder) {
            $mock = $builder->setMethods(['isLogEnabled'])->getMock();

            $mock->expects($this->once())
                ->method('isLogEnabled')
                ->willReturn(true);

            return $mock;
        });

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

	public function testShouldNotLogException() {
		$logger = new UncaughtExceptionLogger();

		$this->mock( Log::class, function ( PHPUnit_Framework_MockObject_MockBuilder $builder ) {
			$mock = $builder->setMethods( [ 'error' ] )->getMock();

			$mock->expects( $this->never() )
				 ->method( 'error' );

			return $mock;
		} );

		$this->expectException(ExceptionNotLogged::class);

		$logger->handleException( new ExceptionNotLogged() );
	}
}

class ExceptionLogged extends Exception implements LoggableException {
	public function getLogMessage() {
		return '';
	}

	public function getLogContext() {
		return [];
	}
}

class ExceptionNotLogged extends Exception {
}

<?php

use Give\Framework\Exceptions\Contracts\LoggableException;
use Give\Framework\Exceptions\UncaughtExceptionLogger;
use Give\Log\Log;

class UncaughtExceptionLoggerTest extends Give_Unit_Test_Case {
	public function testShouldLogException() {
		$logger = new UncaughtExceptionLogger();

		$mock = $this->mock( Log::class, function(PHPUnit_Framework_MockObject_MockBuilder $builder) {
			return $builder->setMethods(['error'])->getMock();
		} );

		$mock->expects( $this->once() )
			->method( 'error' )
			->with( '', [] );

		$logger->handleException( new ExceptionLogged() );
	}

	public function testShouldNotLogException() {

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

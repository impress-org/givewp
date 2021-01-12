<?php

use Give\Framework\Migrations\MigrationsRegister;
use Give\Framework\Migrations\MigrationsRunner;
use PHPUnit\Framework\TestCase;

/**
 * Class MigrationsRunnerTest
 * @package src\Framework\Migrations
 * @coversDefaultClass MigrationsRunner
 */
class MigrationsRunnerTest extends TestCase {
	/**
	 * @see https://github.com/impress-org/givewp/issues/5454
	 */
	public function testNoMigrationsDoesNotThrowError() {
		// Mock get_option to return the default.
		function get_option( $option, $default = false ) {
			return $default;
		}

		$runner = new MigrationsRunner( new MigrationsRegister );

		self::assertFalse(
			$runner->hasMigrationToRun()
		);
	}
}

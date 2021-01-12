<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ShortcodeTest extends TestCase {

	public function setUp(): void {
		function shortcode_atts( $pairs, $attributes, $tag ) {
			return $attributes;
		}
	}

	public function testParsedAttributes(): void {
		$shortcodeClass = new \Give\MultiFormGoals\MultiFormGoal\Shortcode();

        $class = new \ReflectionClass( $shortcodeClass );
        $method = $class->getMethod( 'parseAttributes' ) ;
        $method->setAccessible(true);

		$pairs = [
			'string' => 'default',
			'array' => [],
			'listSingle' => [],
			'listMultiple' => [],
		];

		$attributes = [
			'string' => 'this is a string.',
			'array' => [],
			'listSingle' => '1', //Should be parsed into an array.
			'listMultiple' => '1,2,3', // Should be parsed into an array.
		];

		$attributes = $method->invokeArgs( $shortcodeClass, [ $pairs, $attributes ] );
		$this->assertTrue( is_string( $attributes[ 'string' ] ) );
		$this->assertTrue( is_array( $attributes[ 'array' ] ) );
		$this->assertTrue( is_array( $attributes[ 'listSingle' ] ) );
		$this->assertTrue( is_array( $attributes[ 'listMultiple' ] ) );
	}
}

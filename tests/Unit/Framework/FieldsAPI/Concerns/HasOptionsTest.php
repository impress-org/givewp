<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasOptions;
use Give\Framework\FieldsAPI\Option;
use PHPUnit\Framework\TestCase;

final class HasOptionsTest extends TestCase
{

    public function testSetOptions()
    {
        /** @var HasOptions $mock */
        $mock = $this->getMockForTrait(HasOptions::class);

        $mock->options( [ 'aye', 'Aye' ] );
        $this->assertCount( 1, $mock->getOptions() );

        $mock->options(
			[ 'aye', 'Aye' ],
			[ 'bee', 'bee' ]
		);
        $this->assertCount( 2, $mock->getOptions() );
    }

    public function testSetOptionsNormalizesInput() {
	    /** @var HasOptions $mock */
    	$mock = $this->getMockForTrait( HasOptions::class );

	    $mock->options(
	    	'foo',
	    	['bar', 'Bar'],
	    	Option::make( 'aye', 'Aye' ),
			Option::make( 'bee' )
	    );

		$this->assertContainsOnlyInstancesOf( Option::class, $mock->getOptions() );
    }
}

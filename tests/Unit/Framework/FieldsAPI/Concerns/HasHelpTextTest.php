<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasHelpText;
use PHPUnit\Framework\TestCase;

final class HasHelpTextTest extends TestCase
{

    public function testHasHelpText()
    {
        /** @var HasHelpText $mock */
        $mock = $this->getMockForTrait(HasHelpText::class);
        $mock->helpText('Help text');
		$this->assertSame( 'Help text', $mock->getHelpText() );
	}
}

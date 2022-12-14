<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasLabel;
use PHPUnit\Framework\TestCase;

final class HasLabelTest extends TestCase
{

    public function testHasLabel()
    {
        /** @var HasLabel $mock */
        $mock = $this->getMockForTrait(HasLabel::class);
        $mock->label('Label');
        $this->assertEquals( 'Label' , $mock->getLabel() );
    }
}

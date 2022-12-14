<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasEmailTag;
use PHPUnit\Framework\TestCase;

final class HasEmailTagTest extends TestCase
{

    public function testHasEmailTag()
    {
        /** @var HasEmailTag $mock */
        $mock = $this->getMockForTrait(HasEmailTag::class);
        $mock->emailTag('myTextField');
        $this->assertEquals( 'myTextField', $mock->getEmailTag() );
    }
}

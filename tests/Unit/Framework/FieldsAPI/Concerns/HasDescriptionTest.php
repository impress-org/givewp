<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasDescription;
use PHPUnit\Framework\TestCase;

final class HasDescriptionTest extends TestCase
{

    /**
     * @since 2.32.0 
     */
    public function testHasDescriptionTag()
    {
        /** @var HasDescription $mock */
        $mock = $this->getMockForTrait(HasDescription::class);
        $mock->description('This is a description for my field.');
        $this->assertEquals( 'This is a description for my field.', $mock->getDescription() );
    }
}

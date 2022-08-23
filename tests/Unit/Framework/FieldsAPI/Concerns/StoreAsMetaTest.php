<?php
namespace GiveTests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\StoreAsMeta;
use PHPUnit\Framework\TestCase;

final class StoreAsMetaTest extends TestCase
{

    public function testStoreAsDonorMeta()
    {
        /** @var StoreAsMeta $mock */
        $mock = $this->getMockForTrait(StoreAsMeta::class);
        $mock->storeAsDonorMeta();
        $this->assertTrue( $mock->shouldStoreAsDonorMeta() );
    }

    public function testNotStoreAsDonorMeta() {
	    /** @var StoreAsMeta $mock */
	    $mock = $this->getMockForTrait( StoreAsMeta::class );
	    $mock->storeAsDonorMeta();
	    $mock->storeAsDonorMeta( false );
        $this->assertFalse( $mock->shouldStoreAsDonorMeta() );
    }

    public function testStoreAsMetaMethodEnforcesBooleanType() {
	    /** @var StoreAsMeta $mock */
	    $mock = $this->getMockForTrait( StoreAsMeta::class );
        $mock->storeAsDonorMeta( 'foo' );
        $this->assertInternalType( 'bool', $mock->shouldStoreAsDonorMeta() );
    }
}

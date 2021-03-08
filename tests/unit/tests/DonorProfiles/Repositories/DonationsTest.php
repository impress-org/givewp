<?php

use Give\DonorProfiles\Repositories\Donations as DonationsRepository;

final class DonationsTest extends Give_Unit_Test_Case {
    
    public function testReceiptInfoContainsDonationData() {
        $class = new ReflectionClass( DonationsRepository::class );
        $getReceiptInfo = $class->getMethod( 'getReceiptInfo' );
        $getReceiptInfo->setAccessible(true);

        $payment = get_post(
            Give_Helper_Payment::create_simple_payment()
        );

        $receiptInfo = $getReceiptInfo->invokeArgs( new DonationsRepository, [ $payment ] );
        $receiptInfo = json_encode( $receiptInfo );

        // Expected values provided by sample data.
        $this->assertContains( '$20.00', $receiptInfo );
        $this->assertContains( 'admin@example.org', $receiptInfo );
    }
}

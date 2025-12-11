<?php

namespace Give\Tests\Unit\DonorProfiles\Repositories;

use Give\Donations\Models\Donation;
use Give\DonorDashboards\Repositories\Donations as DonationsRepository;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Helper_Payment;
use Give\Tests\TestCase;
use Give_Payment;
use ReflectionClass;

final class DonationsTest extends TestCase
{
    use RefreshDatabase;

    public function testReceiptInfoContainsDonationData()
    {
        $class = new ReflectionClass(DonationsRepository::class);
        $getReceiptInfo = $class->getMethod('getReceiptInfo');
        $getReceiptInfo->setAccessible(true);

        $donation = Donation::factory()->create([
            'email' => 'admin@example.org',
            'amount' => new Money('2000', 'USD'),
        ]);

        $payment = new Give_Payment( $donation->id );

        $receiptInfo = $getReceiptInfo->invokeArgs(new DonationsRepository(), [$payment]);
        $receiptInfo = json_encode($receiptInfo);

        // Expected values provided by sample data.
        $this->assertStringContainsString('$20.00', $receiptInfo);
        $this->assertStringContainsString('admin@example.org', $receiptInfo);
    }
}

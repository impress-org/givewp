<?php

namespace Give\Tests\Unit\License\DataTransferObjects;

use Give\License\DataTransferObjects\License;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;
use Give\Tests\TestCase;
use Give\Tests\Unit\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class TestLicense extends TestCase
{
    use HasLicenseData;
    use RefreshDatabase;
    /**
     * @unreleased
     */
    public function testFromDataReturnsLicenseObject(): void
    {
        $data = $this->getRawLicenseData();

        $license = License::fromData($data);

        $this->assertTrue($license->isValid);
        $this->assertTrue($license->success);
        $this->assertEquals('valid', $license->license);
        $this->assertEquals('Basic Plan', $license->itemName);
        $this->assertEquals('checksum-1234567890', $license->checksum);
        $this->assertEquals('2025-06-19 23:59:59', $license->expires);
        $this->assertEquals(1603817, $license->paymentId);
        $this->assertEquals('Bill Murray', $license->customerName);
        $this->assertEquals('bill_murray@stellarwp.com', $license->customerEmail);
        $this->assertEquals(1, $license->licenseLimit);
        $this->assertEquals(1, $license->siteCount);
        $this->assertEquals(0, $license->activationsLeft);
        $this->assertFalse($license->priceId);
        $this->assertEquals('license-key-1234567890', $license->licenseKey);
        $this->assertEquals(123456, $license->licenseId);
        $this->assertFalse($license->isAllAccessPass);
        $this->assertCount(3, $license->downloads);
    }
}

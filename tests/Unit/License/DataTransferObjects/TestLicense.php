<?php

namespace Give\Tests\Unit\License\DataTransferObjects;

use Give\License\DataTransferObjects\Download;
use Give\License\DataTransferObjects\License;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.3.0
 */
class TestLicense extends TestCase
{
    use HasLicenseData;
    use RefreshDatabase;
    /**
     * @since 4.3.0
     */
    public function testFromDataReturnsLicenseObject(): void
    {
        $data = $this->getRawLicenseData();

        $license = License::fromData($data);

        $this->assertTrue($license->isActive);
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
        $this->assertEquals(0, $license->priceId);
        $this->assertEquals('license-key-1234567890', $license->licenseKey);
        $this->assertEquals(123456, $license->licenseId);
        $this->assertFalse($license->isAllAccessPass);
        $this->assertCount(3, $license->downloads);
    }

    /**
     * @since 4.3.0
     */
    public function testFromDataFormatsDownloads(): void
    {
        $data = $this->getRawLicenseData([
            'download' => 'https://example.com/download.zip',
            'readme' => 'https://example.com/readme.txt',
            'current_version' => '1.0.0',
            'plugin_slug' => 'example-plugin',
        ]);

        $license = License::fromData($data);

        $download = Download::fromData([
            'file' => 'https://example.com/download.zip',
            'plugin_slug' => 'example-plugin',
            'readme' => 'https://example.com/readme.txt',
            'current_version' => '1.0.0',
        ]);

        $this->assertEquals([
            $download,
        ], $license->downloads);
    }
}

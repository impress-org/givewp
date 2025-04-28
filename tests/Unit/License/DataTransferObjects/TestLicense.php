<?php

namespace Give\Tests\License\DataTransferObjects;

use Give\License\DataTransferObjects\License;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class TestLicense extends TestCase
{
    /**
     * @unreleased
     */
    public function testFromDataReturnsLicenseObject(): void
    {
        $data = [
            "success" => true,
            "license" => "valid",
            "item_id" => false,
            "item_name" => "Basic Plan",
            "checksum" => "checksum-1234567890",
            "expires" => "2025-06-19 23:59:59",
            "payment_id" => 1603817,
            "customer_name" => "Bill Murray",
            "customer_email" => "bill_murray@stellarwp.com",
            "license_limit" => 1,
            "site_count" => 1,
            "activations_left" => 0,
            "price_id" => false,
            "license_key" => "license-key-1234567890",
            "license_id" => 123456,
            "download" => [
                [
                    "index" => "0",
                    "attachment_id" => "0",
                    "thumbnail_size" => "",
                    "name" => "Manual Donations",
                    "file" => "https://givewp.com",
                    "condition" => "all",
                    "array_index" => 1,
                    "plugin_slug" => "give-manual-donations",
                    "readme" => "https://givewp.com/downloads/plugins/give-manual-donations/readme.txt",
                    "current_version" => "1.8.0",
                ],
                [
                    "index" => "0",
                    "attachment_id" => "0",
                    "thumbnail_size" => "",
                    "name" => "PDF Receipts",
                    "file" => "https://givewp.com",
                    "condition" => "all",
                    "array_index" => 0,
                    "plugin_slug" => "give-pdf-receipts",
                    "readme" => "https://givewp.com/downloads/plugins/give-pdf-receipts/readme.txt",
                    "current_version" => "3.2.1",
                ],
                [
                    "index" => "0",
                    "attachment_id" => "0",
                    "thumbnail_size" => "",
                    "name" => "Stripe Gateway",
                    "file" => "https://givewp.com",
                    "condition" => "all",
                    "array_index" => 0,
                    "plugin_slug" => "give-stripe",
                    "readme" => "https://givewp.com/downloads/plugins/give-stripe/readme.txt",
                    "current_version" => "2.7.0",
                ],
            ],
            "is_all_access_pass" => false,
        ];

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

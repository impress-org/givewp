<?php

namespace Give\Tests\Unit\License\TestTraits;

/**
 * @since 4.3.0
 */
trait HasLicenseData
{

    /**
     * @since 4.3.0
     */
    public function getRawLicenseData(array $data = []): array
    {
        return array_merge([
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
        ], $data);
    }
}

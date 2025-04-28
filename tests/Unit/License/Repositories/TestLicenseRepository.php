<?php

namespace Give\Tests\Resources\License;

use Give\License\DataTransferObjects\License;
use Give\License\Repositories\LicenseRepository;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 * @coversDefaultClass \Give\License\Repositories\LicenseRepository
 */
class TestLicenseRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @var LicenseRepository
     */
    protected LicenseRepository $repository;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new LicenseRepository();
    }

    /**
     * @unreleased
     */
    public function testHasLicenseReturnsFalseWhenNoLicenseIsStored(): void
    {
        $this->assertFalse($this->repository->hasLicense());
    }

    /**
     * @unreleased
     */
    public function testHasLicenseReturnsTrueWhenLicenseIsStored(): void
    {
        update_option('give_licenses', $this->getRawLicenseData());
        $this->assertTrue($this->repository->hasLicense());
    }

    /**
     * @unreleased
     */
    public function testGetStoredLicensesReturnsEmptyArrayWhenNoLicenseIsStored(): void
    {
        $this->assertSame([], $this->repository->getStoredLicenses());
    }

    /**
     * @unreleased
     */
    public function testGetStoredLicensesReturnsArrayWhenLicenseIsStored(): void
    {
        update_option(
            'give_licenses',
            [
                'licence-key-1' => $this->getRawLicenseData(),
                'licence-key-2' => $this->getRawLicenseData([
                    'license' => 'invalid'
                ]),
            ]
        );

        $this->assertSame(
            [
                'licence-key-1' => $this->getRawLicenseData(),
                'licence-key-2' => $this->getRawLicenseData([
                    'license' => 'invalid'
                ]),
            ],
            $this->repository->getStoredLicenses()
        );
    }

    /**
     * @unreleased
     */
    public function testGetLicenseReturnsLicenseDataWhenLicenseIsStored(): void
    {
        $data = $this->getRawLicenseData();

        update_option(
            'give_licenses',
            ['licence-key-1' => $data]
        );

        $licenseData = License::fromData($data);

        $license = $this->repository->getLicense();

        $this->assertEquals($licenseData, $license);
    }

    /**
     * @unreleased
     */
    public function testIsLicenseValidReturnsFalseWhenLicenseIsNotValid(): void
    {
        update_option(
            'give_licenses',
            ['licence-key-1' => $this->getRawLicenseData(['license' => 'invalid'])]
        );

        $this->assertFalse($this->repository->isLicenseValid());
    }

    /**
     * @unreleased
     */
    public function testIsValidReturnsTrueWhenLicenseIsValid(): void
    {
        update_option(
            'give_licenses',
            ['licence-key-1' => $this->getRawLicenseData()]
        );

        $this->assertTrue($this->repository->isLicenseValid());
    }

    /**
     * @unreleased
     */
    public function testGetGatewayFeeReturnsDefaultWhenNoLicenseIsStored(): void
    {
        $this->assertSame(2.0, $this->repository->getGatewayFeePercentage());
    }

    /**
     * @unreleased
     * @dataProvider gatewayFeeDataProvider
     */
    public function testGetGatewayFeeReturnsFee($fee): void
    {
        update_option(
            'give_licenses',
            ['licence-key-1' => $this->getRawLicenseData(['gateway_fee' => $fee])]
        );

        $this->assertSame((float)$fee, $this->repository->getGatewayFeePercentage());
    }

    /**
     * @unreleased
     */
    protected function getRawLicenseData(array $data = []): array
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

    /**
     * @unreleased
     */
    public function gatewayFeeDataProvider(): array
    {
        return
            [
                [0],
                [1.8],
                [2],
            ];
    }
}

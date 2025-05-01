<?php

namespace Give\Tests\Unit\Resources\License;

use Give\License\DataTransferObjects\License;
use Give\License\Repositories\LicenseRepository;
use Give\License\ValueObjects\LicenseOptionKeys;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;

/**
 * @unreleased
 * @coversDefaultClass \Give\License\Repositories\LicenseRepository
 */
class TestLicenseRepository extends TestCase
{
    use RefreshDatabase;
    use HasLicenseData;

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
    public function testHasLicensesReturnsFalseWhenNoLicenseIsStored(): void
    {
        $this->assertFalse($this->repository->hasLicenses());
    }

    /**
     * @unreleased
     */
    public function testHasLicensesReturnsTrueWhenLicenseIsStored(): void
    {
        update_option(LicenseOptionKeys::LICENSES, $this->getRawLicenseData());
        $this->assertTrue($this->repository->hasLicenses());
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
        $data = [
            'licence-key-1' => $this->getRawLicenseData(),
            'licence-key-2' => $this->getRawLicenseData([
                'license' => 'expired'
            ]),
        ];

        update_option(
            LICenseOptionKeys::LICENSES,
            $data
        );

        $this->assertSame(
            $data,
            $this->repository->getStoredLicenses()
        );
    }

    /**
     * @unreleased
     */
    public function testGetLicensesReturnsLicenseDataWhenLicenseIsStored(): void
    {
        $license1 = $this->getRawLicenseData([
            'license_key' => 'licence-key-1',
            'license_id' => 123456,
        ]);

        $license2 = $this->getRawLicenseData([
            'license_key' => 'licence-key-2',
            'license_id' => 123457,
        ]);

        update_option(
            'give_licenses',
            [
                'licence-key-1' => $license1,
                'licence-key-2' => $license2
            ],
        );

        $licenses = $this->repository->getLicenses();

        $this->assertEquals([
            License::fromData($license1),
            License::fromData($license2),
        ], $licenses);
    }

    /**
     * @unreleased
     */
    public function testHasActiveLicensesReturnsFalseWhenNoLicenseIsValid(): void
    {
        update_option(
            LICenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData(['license' => 'expired']),
                'licence-key-2' => $this->getRawLicenseData(['license' => 'expired']),
            ]
        );

        $this->assertFalse($this->repository->hasActiveLicense());
    }

    /**
     * @unreleased
     */
    public function testHasActiveLicensesReturnsTrueWhenValidLicenseIsFound(): void
    {
        update_option(
            LICenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData([
                    'license' => 'expired'
                ]),
                'licence-key-2' => $this->getRawLicenseData([
                    'license' => 'valid'
                ]),
            ]
        );

        $this->assertTrue($this->repository->hasActiveLicense());
    }

    /**
     * @unreleased
     */
    public function testGetGatewayFeeReturnsDefaultWhenNoLicenseIsStored(): void
    {
        $this->assertSame(2.0, $this->repository->getPlatformFeePercentage());
    }

    /**
     * @unreleased
     * @dataProvider gatewayFeeDataProvider
     */
    public function testGetPlatformFeePercentageReturnsFeeWhenLicenseIsActive($fee): void
    {
        update_option(
            LicenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData([
                    'license' => 'valid'
                ]),
            ]
        );

        update_option(LicenseOptionKeys::PLATFORM_FEE_PERCENTAGE, $fee);

        $this->assertSame((float)$fee, $this->repository->getPlatformFeePercentage());
    }

    /**
     * @unreleased
     * @dataProvider gatewayFeeDataProvider
     */
    public function testGetPlatformFeePercentageReturnsZeroWhenLicenseIsActiveAndOptionIsMissing($fee): void
    {
        update_option(
            LicenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData([
                    'license' => 'valid'
                ]),
            ]
        );

        $this->assertSame(0.0, $this->repository->getPlatformFeePercentage());
    }

    /**
     * @unreleased
     * @dataProvider gatewayFeeDataProvider
     */
    public function testGetPlatformFeePercentageReturnsDefaultFeeWhenNoLicensesAreActive(): void
    {
        update_option(
            LicenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData([
                    'license' => 'expired'
                ]),
            ]
        );

        update_option(LicenseOptionKeys::PLATFORM_FEE_PERCENTAGE, 1.8);

        $this->assertSame(2.0, $this->repository->getPlatformFeePercentage());
    }

    /**
     * @unreleased
     * @dataProvider gatewayFeeDataProvider
     */
    public function testGetPlatformFeePercentageReturnsDefaultFeeWhenNoLicensesAreFound(): void
    {
        update_option(LicenseOptionKeys::PLATFORM_FEE_PERCENTAGE, 1.8);

        $this->assertSame(2.0, $this->repository->getPlatformFeePercentage());
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

<?php

namespace Give\Tests\Unit\Resources\License;

use Give\License\DataTransferObjects\License;
use Give\License\Repositories\LicenseRepository;
use Give\License\ValueObjects\LicenseOptionKeys;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;

/**
 * @since 4.3.0
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
     * @since 4.3.0
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->repository = new LicenseRepository();
    }

    /**
     * @since 4.3.0
     */
    public function testHasLicensesReturnsFalseWhenNoLicenseIsStored(): void
    {
        $this->assertFalse($this->repository->hasStoredLicenses());
    }

    /**
     * @since 4.3.0
     */
    public function testHasLicensesReturnsTrueWhenLicenseIsStored(): void
    {
        update_option(LicenseOptionKeys::LICENSES, $this->getRawLicenseData());
        $this->assertTrue($this->repository->hasStoredLicenses());
    }

    /**
     * @since 4.3.0
     */
    public function testGetStoredLicensesReturnsEmptyArrayWhenNoLicenseIsStored(): void
    {
        $this->assertSame([], $this->repository->getStoredLicenses());
    }

    /**
     * @since 4.3.0
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
     * @since 4.3.0
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
     * @since 4.3.0
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

        $this->assertFalse($this->repository->hasActiveLicenses());
    }

    /**
     * @since 4.3.0
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

        $this->assertTrue($this->repository->hasActiveLicenses());
    }

    /**
     * @since 4.3.0
     */
    public function testGetActiveLicensesReturnsEmptyArrayWhenNoLicenseIsValid(): void
    {
        update_option(
            LicenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData(['license' => 'expired']),
                'licence-key-2' => $this->getRawLicenseData(['license' => 'expired']),
            ]
        );

        $this->assertSame([], $this->repository->getActiveLicenses());
    }

    /**
     * @since 4.3.0
     */
    public function testGetActiveLicensesReturnsArrayWhenValidLicenseIsFound(): void
    {
        $license1 = $this->getRawLicenseData([
            'license' => 'valid',
            'license_key' => 'licence-key-1',
            'license_id' => 123456,
        ]);

        $license2 = $this->getRawLicenseData([
            'license' => 'valid',
            'license_key' => 'licence-key-2',
            'license_id' => 123457,
        ]);

        $license3 = $this->getRawLicenseData([
            'license' => 'expired',
            'license_key' => 'licence-key-3',
            'license_id' => 123458,
        ]);

        update_option(
            LicenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $license1,
                'licence-key-2' => $license2,
                'licence-key-3' => $license3,
            ],
        );

        $licenses = $this->repository->getActiveLicenses();

        $this->assertEquals([
            License::fromData($license1),
            License::fromData($license2),
        ], $licenses);
    }

    /**
     * @since 4.3.0
     */
    public function testGetGatewayFeeReturnsDefaultWhenNoLicenseIsStored(): void
    {
        $this->assertSame(2.0, $this->repository->getPlatformFeePercentage());
    }

    /**
     * @since 4.3.0
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
     * @since 4.3.0
     */
    public function testGetPlatformFeePercentageReturnsZeroWhenLicenseIsActiveAndOptionIsMissing(): void
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
        $this->assertFalse($this->repository->hasPlatformFeePercentage());
    }

    /**
     * @since 4.3.0
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
     * @since 4.3.0
     * @dataProvider gatewayFeeDataProvider
     */
    public function testGetPlatformFeePercentageReturnsDefaultFeeWhenNoLicensesAreFound(): void
    {
        update_option(LicenseOptionKeys::PLATFORM_FEE_PERCENTAGE, 1.8);

        $this->assertSame(2.0, $this->repository->getPlatformFeePercentage());
    }

    /**
     * @since 4.3.0
     */
    public function testHasPlatformFeePercentageShouldReturnTrue(): void
    {
        update_option(
            LicenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData([
                    'license' => 'valid'
                ]),
            ]
        );

        update_option(LicenseOptionKeys::PLATFORM_FEE_PERCENTAGE, 1.8);

        $this->assertTrue($this->repository->hasPlatformFeePercentage());
    }

    /**
     * @since 4.3.0
     */
    public function testHasPlatformFeePercentageShouldReturnTrueWhenLicenseIsExpired(): void
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

        $this->assertTrue($this->repository->hasPlatformFeePercentage());
    }

    /**
     * @since 4.3.0
     */
    public function testFindLowestPlatformFeePercentageFromLicensesReturnsNullWhenNoLicensesAreFound(): void
    {
        $this->assertNull($this->repository->findLowestPlatformFeePercentageFromActiveLicenses());
    }

    /**
     * @since 4.3.0
     */
    public function testFindLowestPlatformFeePercentageFromLicensesReturnsNullWhenNoLicensesAreActive(): void
    {
        update_option(
            LicenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData([
                    'license' => 'expired'
                ]),
                'licence-key-2' => $this->getRawLicenseData([
                    'license' => 'expired'
                ]),
            ]
        );

        $this->assertNull($this->repository->findLowestPlatformFeePercentageFromActiveLicenses());
    }

    /**
     * @since 4.3.0
     */
    public function testFindLowestPlatformFeePercentageFromLicensesReturnsLowestFeeWhenMultipleLicensesAreFound(): void
    {
        update_option(
            LicenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData([
                    'license' => 'valid',
                    'gateway_fee' => 1.8
                ]),
                'licence-key-2' => $this->getRawLicenseData([
                    'license' => 'valid',
                    'gateway_fee' => 2.0
                ]),
                'licence-key-3' => $this->getRawLicenseData([
                    'license' => 'expired',
                    'gateway_fee' => 1.7
                ]),
            ]
        );

        $this->assertSame(1.8, $this->repository->findLowestPlatformFeePercentageFromActiveLicenses());
    }

    /**
     * @since 4.3.0
     */
    public function testFindLowestPlatformFeePercentageFromLicensesReturnsZeroWhenGatewayFeeIsZero(): void
    {
        update_option(
            LicenseOptionKeys::LICENSES,
            [
                'licence-key-1' => $this->getRawLicenseData([
                    'license' => 'valid',
                    'gateway_fee' => 1.8
                ]),
                'licence-key-2' => $this->getRawLicenseData([
                    'license' => 'valid',
                    'gateway_fee' => 0.0
                ]),
            ]
        );

        $this->assertSame(0.0, $this->repository->findLowestPlatformFeePercentageFromActiveLicenses());
    }

    /**
     * @since 4.3.0
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

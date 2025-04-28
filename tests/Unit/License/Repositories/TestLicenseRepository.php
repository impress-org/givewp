<?php

namespace Give\Tests\Unit\Resources\License;

use Give\License\DataTransferObjects\License;
use Give\License\Repositories\LicenseRepository;
use Give\Tests\Unit\License\TestTraits\HasLicenseData;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

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

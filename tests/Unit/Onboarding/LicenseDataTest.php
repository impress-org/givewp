<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Onboarding;

use Faker\Factory;
use Give\Onboarding\LicenseData;
use Give\Tests\TestCase;

/**
 * Covers the decision logic LicenseData owns: when it offers an activation URL and
 * when it stays quiet. The Harbor-facing calls it wraps (global functions, the
 * final Product_Collection/Product_Entry classes) are exercised through Harbor's
 * own suite; these tests fix the branching around them so it holds whatever version
 * of Harbor a build happens to bundle.
 *
 * @since TBD
 */
final class LicenseDataTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testGetActivationUrlIsEmptyWhenHarborCannotBuildOne(): void
    {
        $faker = Factory::create();

        $licenseData = $this->getMockBuilder(LicenseData::class)
            ->onlyMethods(['canBuildActivationUrl', 'isActivated', 'buildActivationUrl'])
            ->getMock();

        $licenseData->method('canBuildActivationUrl')->willReturn(false);
        $licenseData->expects($this->never())->method('buildActivationUrl');

        $this->assertSame('', $licenseData->getActivationUrl($faker->url()));
    }

    /**
     * @since TBD
     */
    public function testGetActivationUrlIsEmptyWhenAlreadyActivated(): void
    {
        $faker = Factory::create();

        $licenseData = $this->getMockBuilder(LicenseData::class)
            ->onlyMethods(['canBuildActivationUrl', 'isActivated', 'buildActivationUrl'])
            ->getMock();

        $licenseData->method('canBuildActivationUrl')->willReturn(true);
        $licenseData->method('isActivated')->willReturn(true);
        $licenseData->expects($this->never())->method('buildActivationUrl');

        $this->assertSame('', $licenseData->getActivationUrl($faker->url()));
    }

    /**
     * @since TBD
     */
    public function testGetActivationUrlReturnsBuiltUrlWhenActionableAndNotActivated(): void
    {
        $faker = Factory::create();

        $returnUrl = $faker->url();
        $expectedUrl = $faker->url();

        $licenseData = $this->getMockBuilder(LicenseData::class)
            ->onlyMethods(['canBuildActivationUrl', 'isActivated', 'buildActivationUrl'])
            ->getMock();

        $licenseData->method('canBuildActivationUrl')->willReturn(true);
        $licenseData->method('isActivated')->willReturn(false);
        $licenseData->expects($this->once())
            ->method('buildActivationUrl')
            ->with($returnUrl)
            ->willReturn($expectedUrl);

        $this->assertSame($expectedUrl, $licenseData->getActivationUrl($returnUrl));
    }
}

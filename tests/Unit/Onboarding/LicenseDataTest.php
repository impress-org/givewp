<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Onboarding;

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
        $licenseData = $this->getMockBuilder(LicenseData::class)
            ->onlyMethods(['canBuildActivationUrl', 'isActivated', 'buildActivationUrl'])
            ->getMock();

        $licenseData->method('canBuildActivationUrl')->willReturn(false);
        $licenseData->expects($this->never())->method('buildActivationUrl');

        $this->assertSame('', $licenseData->getActivationUrl('https://example.test/return'));
    }

    /**
     * @since TBD
     */
    public function testGetActivationUrlIsEmptyWhenAlreadyActivated(): void
    {
        $licenseData = $this->getMockBuilder(LicenseData::class)
            ->onlyMethods(['canBuildActivationUrl', 'isActivated', 'buildActivationUrl'])
            ->getMock();

        $licenseData->method('canBuildActivationUrl')->willReturn(true);
        $licenseData->method('isActivated')->willReturn(true);
        $licenseData->expects($this->never())->method('buildActivationUrl');

        $this->assertSame('', $licenseData->getActivationUrl('https://example.test/return'));
    }

    /**
     * @since TBD
     */
    public function testGetActivationUrlReturnsBuiltUrlWhenActionableAndNotActivated(): void
    {
        $returnUrl = 'https://example.test/return';
        $expectedUrl = 'https://portal.example.test/activate';

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

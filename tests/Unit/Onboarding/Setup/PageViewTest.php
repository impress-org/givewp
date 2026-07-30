<?php

namespace Give\Tests\Unit\Onboarding\Setup;

use Faker\Factory;
use Give\Framework\Http\ConnectServer\Client\ConnectClient;
use Give\Onboarding\FormRepository;
use Give\Onboarding\LicenseData;
use Give\Onboarding\Setup\PageView;
use Give\Tests\TestCase;

final class PageViewTest extends TestCase
{

    /**
     * @link https://github.com/impress-org/givewp/issues/5575
     * @link https://github.com/impress-org/givewp/issues/5575#issuecomment-770950149
     */
    public function testContentSurroundedByUnmergedTagIsNotScrubbed()
    {
        $connectClient = give(ConnectClient::class);
        $pageView = new PageView(
            $this->createMock(FormRepository::class), $connectClient
        );

        $this->assertStringContainsString(
            '<article id="" class="setup-item foo" data-givewp-test="">',
            $pageView->render_template('row-item', ['class' => 'foo'])
        );
    }

    /**
     * @since TBD
     */
    public function testLicenseStepIsShownOnlyWithPremiumAddonsAndACapableHarbor(): void
    {
        $this->bindLicenseData([
            'hasActivePremiumAddons' => true,
            'canBuildActivationUrl' => true,
        ]);
        $this->assertTrue($this->makePageView()->shouldShowLicenseStep());

        $this->bindLicenseData([
            'hasActivePremiumAddons' => true,
            'canBuildActivationUrl' => false,
        ]);
        $this->assertFalse($this->makePageView()->shouldShowLicenseStep());
    }

    /**
     * GiveWP is free, so a site running no premium add-on must not be shown a
     * license step — even when Harbor is perfectly able to build the URL.
     *
     * @since TBD
     */
    public function testLicenseStepIsHiddenWithoutActivePremiumAddons(): void
    {
        $this->bindLicenseData([
            'hasActivePremiumAddons' => false,
            'canBuildActivationUrl' => true,
        ]);

        $this->assertFalse($this->makePageView()->shouldShowLicenseStep());
    }

    /**
     * @since TBD
     */
    public function testLicenseActivationStateFollowsLicenseData(): void
    {
        $this->bindLicenseData(['isActivated' => true]);
        $this->assertTrue($this->makePageView()->isLicenseActivated());

        $this->bindLicenseData(['isActivated' => false]);
        $this->assertFalse($this->makePageView()->isLicenseActivated());
    }

    /**
     * @since TBD
     */
    public function testLicenseStepUrlUsesTheManagerOnceActivated(): void
    {
        $managementUrl = Factory::create()->url();

        $this->bindLicenseData([
            'isActivated' => true,
            'getManagementUrl' => $managementUrl,
        ]);

        $this->assertSame(
            $managementUrl,
            $this->makePageView()->licenseStepUrl()
        );
    }

    /**
     * @since TBD
     */
    public function testLicenseStepUrlPointsToActivationWhenNotActivated(): void
    {
        $activationUrl = Factory::create()->url();

        $this->bindLicenseData([
            'isActivated' => false,
            'buildActivationUrl' => $activationUrl,
        ]);

        $this->assertSame(
            $activationUrl,
            $this->makePageView()->licenseStepUrl()
        );
    }

    /**
     * Bind a LicenseData test double into the container so PageView resolves it in
     * place of the real service. Only the passed methods are stubbed; the rest keep
     * their PHPUnit defaults.
     *
     * @since TBD
     *
     * @param array<string, mixed> $returns method name => value it should return
     */
    private function bindLicenseData(array $returns): void
    {
        $licenseData = $this->createMock(LicenseData::class);

        foreach ($returns as $method => $value) {
            $licenseData->method($method)->willReturn($value);
        }

        give()->instance(LicenseData::class, $licenseData);
    }

    /**
     * @since TBD
     */
    private function makePageView(): PageView
    {
        return new PageView(
            $this->createMock(FormRepository::class),
            give(ConnectClient::class)
        );
    }
}

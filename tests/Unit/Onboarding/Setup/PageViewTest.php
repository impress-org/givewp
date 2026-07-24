<?php

namespace Give\Tests\Unit\Onboarding\Setup;

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
    public function testLicenseStepAvailabilityFollowsLicenseData(): void
    {
        $this->bindLicenseData(['canBuildActivationUrl' => true]);
        $this->assertTrue($this->makePageView()->isLicenseStepAvailable());

        $this->bindLicenseData(['canBuildActivationUrl' => false]);
        $this->assertFalse($this->makePageView()->isLicenseStepAvailable());
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
        $this->bindLicenseData([
            'isActivated' => true,
            'getManagementUrl' => 'https://example.test/wp-admin/manage',
        ]);

        $this->assertSame(
            'https://example.test/wp-admin/manage',
            $this->makePageView()->licenseStepUrl()
        );
    }

    /**
     * @since TBD
     */
    public function testLicenseStepUrlPointsToActivationWhenNotActivated(): void
    {
        $this->bindLicenseData([
            'isActivated' => false,
            'buildActivationUrl' => 'https://portal.example.test/activate',
        ]);

        $this->assertSame(
            'https://portal.example.test/activate',
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

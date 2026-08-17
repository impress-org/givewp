<?php

namespace Give\Tests\Unit\Notices;

use Give\Notices\WordPressCoreUpdateNotice;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
final class WordPressCoreUpdateNoticeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        require_once ABSPATH . 'wp-admin/includes/update.php';
    }

    public function tearDown(): void
    {
        delete_option(WordPressCoreUpdateNotice::DISMISSED_OPTION);
        delete_site_transient('update_core');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testDisplaysWhenCoreUpdateIsAvailable()
    {
        $this->setCoreUpdateResponse('upgrade');

        $this->assertTrue((new WordPressCoreUpdateNotice())->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testDoesNotDisplayWhenCoreIsUpToDate()
    {
        $this->setCoreUpdateResponse('latest');

        $this->assertFalse((new WordPressCoreUpdateNotice())->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testDoesNotDisplayWhenCoreUpdateDataIsMissing()
    {
        $this->assertFalse((new WordPressCoreUpdateNotice())->shouldDisplay());
    }

    /**
     * The dismissal flag is shared with the other StellarWP plugins, so a value written by any of
     * them suppresses the notice here as well.
     *
     * @since TBD
     */
    public function testDoesNotDisplayOnceTheSharedDismissalFlagIsSet()
    {
        $this->setCoreUpdateResponse('upgrade');
        update_option(WordPressCoreUpdateNotice::DISMISSED_OPTION, true, false);

        $this->assertFalse((new WordPressCoreUpdateNotice())->shouldDisplay());
    }

    /**
     * @since TBD
     */
    public function testRenderIncludesTheCopyAndADismissLink()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));

        $output = (new WordPressCoreUpdateNotice())->renderNotice();

        $this->assertStringContainsString(
            'Keep your site protected. Update to the latest version of WordPress.',
            $output
        );
        $this->assertStringContainsString(WordPressCoreUpdateNotice::DISMISS_ACTION, $output);
        $this->assertStringContainsString('_wpnonce', $output);
    }

    /**
     * Stores the offer WordPress caches for the installed version. Only the properties
     * get_core_updates() reads are set.
     *
     * @since TBD
     *
     * @param string $response The update response WordPress reports for the installed version.
     */
    private function setCoreUpdateResponse(string $response)
    {
        set_site_transient(
            'update_core',
            (object)[
                'updates' => [
                    (object)[
                        'response' => $response,
                        'locale' => 'en_US',
                        'current' => get_bloginfo('version'),
                    ],
                ],
            ]
        );
    }
}

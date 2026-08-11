<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\IsolateEnqueuedFormViewAssets;
use Give\Tests\TestCase;
use WP_Scripts;
use WP_Styles;

final class TestIsolateEnqueuedFormViewAssets extends TestCase
{
    /**
     * @var WP_Scripts
     */
    private $originalScripts;

    /**
     * @var WP_Styles
     */
    private $originalStyles;

    /**
     * Registrations survive WP_Dependencies::reset(), and a re-registered handle keeps its original
     * dependencies, so each test needs its own queues.
     *
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->originalScripts = $GLOBALS['wp_scripts'] ?? null;
        $this->originalStyles = $GLOBALS['wp_styles'] ?? null;

        $GLOBALS['wp_scripts'] = new WP_Scripts();
        $GLOBALS['wp_styles'] = new WP_Styles();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $GLOBALS['wp_scripts'] = $this->originalScripts;
        $GLOBALS['wp_styles'] = $this->originalStyles;

        remove_all_filters('print_scripts_array');
        remove_all_filters('print_styles_array');
        remove_all_filters('givewp_donation_form_view_allowed_asset_handles');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testShouldKeepScriptsEnqueuedBeforeTheQueueIsPinned()
    {
        wp_enqueue_script('givewp-test-form-app', 'https://example.test/form-app.js', [], '1.0', true);

        (new IsolateEnqueuedFormViewAssets())();

        $this->assertSame(
            ['givewp-test-form-app'],
            apply_filters('print_scripts_array', ['givewp-test-form-app'])
        );
    }

    /**
     * @since TBD
     */
    public function testShouldKeepDependenciesOfScriptsEnqueuedBeforeTheQueueIsPinned()
    {
        wp_register_script('givewp-test-registrars', 'https://example.test/registrars.js', [], '1.0', true);
        wp_enqueue_script(
            'givewp-test-form-app',
            'https://example.test/form-app.js',
            ['givewp-test-registrars'],
            '1.0',
            true
        );

        (new IsolateEnqueuedFormViewAssets())();

        $this->assertSame(
            ['givewp-test-registrars', 'givewp-test-form-app'],
            apply_filters('print_scripts_array', ['givewp-test-registrars', 'givewp-test-form-app'])
        );
    }

    /**
     * A third-party plugin enqueueing from wp_print_footer_scripts is what breaks the rendered form.
     *
     * @since TBD
     */
    public function testShouldDropScriptsEnqueuedAfterTheQueueIsPinned()
    {
        wp_enqueue_script('givewp-test-form-app', 'https://example.test/form-app.js', [], '1.0', true);

        (new IsolateEnqueuedFormViewAssets())();

        wp_enqueue_script('third-party-test-script', 'https://example.test/third-party.js', [], '1.0', true);

        $this->assertSame(
            ['givewp-test-form-app'],
            apply_filters('print_scripts_array', ['givewp-test-form-app', 'third-party-test-script'])
        );
    }

    /**
     * @since TBD
     */
    public function testShouldDropStylesEnqueuedAfterTheQueueIsPinned()
    {
        wp_enqueue_style('givewp-test-form-styles', 'https://example.test/form.css', [], '1.0');

        (new IsolateEnqueuedFormViewAssets())();

        wp_enqueue_style('third-party-test-style', 'https://example.test/third-party.css', [], '1.0');

        $this->assertSame(
            ['givewp-test-form-styles'],
            apply_filters('print_styles_array', ['givewp-test-form-styles', 'third-party-test-style'])
        );
    }

    /**
     * @since TBD
     */
    public function testShouldAllowHandlesAddedThroughTheFilter()
    {
        wp_enqueue_script('givewp-test-form-app', 'https://example.test/form-app.js', [], '1.0', true);

        add_filter(
            'givewp_donation_form_view_allowed_asset_handles',
            static function (array $handles, string $type): array {
                if ('scripts' === $type) {
                    $handles[] = 'third-party-test-script';
                }

                return $handles;
            },
            10,
            2
        );

        (new IsolateEnqueuedFormViewAssets())();

        $this->assertSame(
            ['givewp-test-form-app', 'third-party-test-script'],
            apply_filters('print_scripts_array', ['givewp-test-form-app', 'third-party-test-script'])
        );
    }
}

<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\IsolateEnqueuedFormViewAssets;
use Give\Tests\TestCase;

final class TestIsolateEnqueuedFormViewAssets extends TestCase
{
    /**
     * @var string[]
     */
    private $originalScriptQueue = [];

    /**
     * @var string[]
     */
    private $originalStyleQueue = [];

    /**
     * Constructing WP_Styles fires wp_default_styles, which reaches wp_is_block_theme() before the
     * test suite has registered the theme directory. Warming the globals up front keeps that notice
     * out of the incorrect-usage assertions the individual tests run.
     *
     * @since 4.16.7
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        wp_scripts();
        wp_styles();
    }

    /**
     * @since 4.16.7
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->originalScriptQueue = wp_scripts()->queue;
        $this->originalStyleQueue = wp_styles()->queue;

        wp_scripts()->queue = [];
        wp_styles()->queue = [];
    }

    /**
     * @since 4.16.7
     */
    public function tearDown(): void
    {
        wp_scripts()->queue = $this->originalScriptQueue;
        wp_styles()->queue = $this->originalStyleQueue;

        remove_all_filters('print_scripts_array');
        remove_all_filters('print_styles_array');
        remove_all_filters('givewp_donation_form_view_allowed_asset_handles');

        parent::tearDown();
    }

    /**
     * @since 4.16.7
     */
    public function testShouldKeepScriptsEnqueuedBeforeTheQueueIsPinned()
    {
        wp_enqueue_script('givewp-keep-form-app', 'https://example.test/form-app.js', [], '1.0', true);

        (new IsolateEnqueuedFormViewAssets())();

        $this->assertSame(
            ['givewp-keep-form-app'],
            apply_filters('print_scripts_array', ['givewp-keep-form-app'])
        );
    }

    /**
     * Registrations survive between tests, and a re-registered handle keeps the dependencies it was
     * first registered with, so each test needs handles of its own.
     *
     * @since 4.16.7
     */
    public function testShouldKeepDependenciesOfScriptsEnqueuedBeforeTheQueueIsPinned()
    {
        wp_register_script('givewp-deps-registrars', 'https://example.test/registrars.js', [], '1.0', true);
        wp_enqueue_script(
            'givewp-deps-form-app',
            'https://example.test/form-app.js',
            ['givewp-deps-registrars'],
            '1.0',
            true
        );

        (new IsolateEnqueuedFormViewAssets())();

        $this->assertSame(
            ['givewp-deps-registrars', 'givewp-deps-form-app'],
            apply_filters('print_scripts_array', ['givewp-deps-registrars', 'givewp-deps-form-app'])
        );
    }

    /**
     * A third-party plugin enqueueing from wp_print_footer_scripts is what breaks the rendered form.
     *
     * @since 4.16.7
     */
    public function testShouldDropScriptsEnqueuedAfterTheQueueIsPinned()
    {
        wp_enqueue_script('givewp-drop-form-app', 'https://example.test/form-app.js', [], '1.0', true);

        (new IsolateEnqueuedFormViewAssets())();

        wp_enqueue_script('third-party-drop-script', 'https://example.test/third-party.js', [], '1.0', true);

        $this->assertSame(
            ['givewp-drop-form-app'],
            apply_filters('print_scripts_array', ['givewp-drop-form-app', 'third-party-drop-script'])
        );
    }

    /**
     * @since 4.16.7
     */
    public function testShouldDropStylesEnqueuedAfterTheQueueIsPinned()
    {
        wp_enqueue_style('givewp-drop-form-styles', 'https://example.test/form.css', [], '1.0');

        (new IsolateEnqueuedFormViewAssets())();

        wp_enqueue_style('third-party-drop-style', 'https://example.test/third-party.css', [], '1.0');

        $this->assertSame(
            ['givewp-drop-form-styles'],
            apply_filters('print_styles_array', ['givewp-drop-form-styles', 'third-party-drop-style'])
        );
    }

    /**
     * @since 4.16.7
     */
    public function testShouldAllowHandlesAddedThroughTheFilter()
    {
        wp_enqueue_script('givewp-filter-form-app', 'https://example.test/form-app.js', [], '1.0', true);

        add_filter(
            'givewp_donation_form_view_allowed_asset_handles',
            static function (array $handles, string $type): array {
                if ('scripts' === $type) {
                    $handles[] = 'third-party-filter-script';
                }

                return $handles;
            },
            10,
            2
        );

        (new IsolateEnqueuedFormViewAssets())();

        $this->assertSame(
            ['givewp-filter-form-app', 'third-party-filter-script'],
            apply_filters('print_scripts_array', ['givewp-filter-form-app', 'third-party-filter-script'])
        );
    }
}

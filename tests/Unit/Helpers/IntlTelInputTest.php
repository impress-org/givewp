<?php

namespace Give\Tests\Unit\Helpers;

use Give\Helpers\IntlTelInput;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
final class IntlTelInputTest extends TestCase
{
    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        // The script and style registries are global, so clear what these tests enqueue.
        wp_dequeue_script('givewp-intl-tel-input');
        wp_deregister_script('givewp-intl-tel-input');
        wp_dequeue_style('givewp-intl-tel-input');
        wp_deregister_style('givewp-intl-tel-input');

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testGetHtmlInputEnqueuesAssetsWithVersionAndInlineScript(): void
    {
        $html = IntlTelInput::getHtmlInput('+15555550123', 'give_donor_phone_number');

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<link', $html);
        $this->assertStringContainsString('give_donor_phone_number--intl_tel_input', $html);

        $script = wp_scripts()->query('givewp-intl-tel-input');
        $this->assertNotFalse($script);
        $this->assertSame('21.2.4', $script->ver);
        $this->assertSame(IntlTelInput::getScriptUrl(), $script->src);
        $this->assertTrue(wp_script_is('givewp-intl-tel-input', 'enqueued'));
        $this->assertTrue(wp_style_is('givewp-intl-tel-input', 'enqueued'));
        $this->assertSame('21.2.4', wp_styles()->query('givewp-intl-tel-input')->ver);

        $inline = implode('', (array) ($script->extra['after'] ?? []));
        $this->assertStringContainsString('window.intlTelInput(input', $inline);
        $this->assertStringContainsString('give_donor_phone_number--intl_tel_input', $inline);
    }

    /**
     * @since TBD
     */
    public function testEachInitializationBlockHasItsOwnScope(): void
    {
        IntlTelInput::getHtmlInput('+15555550123', 'give_donor_phone_number');
        IntlTelInput::getHtmlInput('+15555550124', 'give_second_phone_number');

        $script = wp_scripts()->query('givewp-intl-tel-input');
        $this->assertNotFalse($script);

        $blocks = $script->extra['after'] ?? [];
        $inline = implode("\n", array_filter($blocks, 'is_string'));

        $this->assertGreaterThanOrEqual(2, substr_count($inline, 'window.intlTelInput(input'));
        $this->assertSame(substr_count($inline, 'function readyHandler()'), substr_count($inline, '(function () {'));
        $this->assertSame(substr_count($inline, '(function () {'), substr_count($inline, '})();'));
    }
}

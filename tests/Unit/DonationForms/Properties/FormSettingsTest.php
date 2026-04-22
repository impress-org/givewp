<?php

namespace Unit\DonationForms\Properties;

use Give\DonationForms\Properties\FormSettings;
use Give\Tests\TestCase;

/**
 * @since 3.11.0
 */
class FormSettingsTest extends TestCase
{
    public function testSanitizationRemovesHtmlTagsFromCustomCss()
    {
        $formSettings = FormSettings::fromArray([
            'customCss' => '<script>alert("hi!")</script>',
        ]);

        $this->assertEmpty($formSettings->customCss);
    }

    public function testSanitizationPreservesCssWhileRemovingHtmlTags()
    {
        $formSettings = FormSettings::fromArray([
            'customCss' => '.test { color: green; }</style><script>alert("hi!")</script><style>',
        ]);

        $this->assertSame('.test { color: green; }', $formSettings->customCss);
    }

    /**
     * @since 4.14.3
     */
    public function testDesignIdStripsHtmlBreakingCharacters()
    {
        $formSettings = FormSettings::fromArray([
            'designId' => 'x"><img src=x onerror=alert(document.domain)><div class="x',
        ]);

        $this->assertStringNotContainsString('"', $formSettings->designId);
        $this->assertStringNotContainsString('>', $formSettings->designId);
        $this->assertStringNotContainsString('<', $formSettings->designId);
    }

    /**
     * @since 4.14.3
     */
    public function testDesignIdPreservesValidSlug()
    {
        $formSettings = FormSettings::fromArray([
            'designId' => 'multi-step-form-design',
        ]);

        $this->assertSame('multi-step-form-design', $formSettings->designId);
    }

    /**
     * @since 4.14.3
     */
    public function testDesignIdIsNullWhenNotProvided()
    {
        $formSettings = FormSettings::fromArray([]);

        $this->assertNull($formSettings->designId);
    }

    /**
     * @unreleased
     */
    public function testPrimaryColorStripsXssPayload()
    {
        $formSettings = FormSettings::fromArray([
            'primaryColor' => 'red;</style><script>alert(document.domain)</script><style>.x{',
        ]);

        $this->assertStringNotContainsString('<', $formSettings->primaryColor);
        $this->assertStringNotContainsString('>', $formSettings->primaryColor);
        $this->assertStringNotContainsString('"', $formSettings->primaryColor);
        $this->assertStringNotContainsString('</style>', $formSettings->primaryColor);
        $this->assertStringNotContainsString('script', $formSettings->primaryColor);
        $this->assertSame('#2d802f', $formSettings->primaryColor);
    }

    /**
     * @unreleased
     */
    public function testPrimaryColorPreservesValidHexColor()
    {
        $this->assertSame('#aabbcc', FormSettings::fromArray(['primaryColor' => '#aabbcc'])->primaryColor);
        $this->assertSame('#abc', FormSettings::fromArray(['primaryColor' => '#abc'])->primaryColor);
    }

    /**
     * @unreleased
     */
    public function testPrimaryColorDefaultsWhenMissing()
    {
        $formSettings = FormSettings::fromArray([]);

        $this->assertSame('#2d802f', $formSettings->primaryColor);
    }

    /**
     * @unreleased
     */
    public function testSecondaryColorStripsXssPayload()
    {
        $formSettings = FormSettings::fromArray([
            'secondaryColor' => 'blue;</style><script>alert(1)</script><style>.x{',
        ]);

        $this->assertStringNotContainsString('<', $formSettings->secondaryColor);
        $this->assertStringNotContainsString('>', $formSettings->secondaryColor);
        $this->assertStringNotContainsString('"', $formSettings->secondaryColor);
        $this->assertStringNotContainsString('</style>', $formSettings->secondaryColor);
        $this->assertStringNotContainsString('script', $formSettings->secondaryColor);
        $this->assertSame('#f49420', $formSettings->secondaryColor);
    }

    /**
     * @unreleased
     */
    public function testSecondaryColorPreservesValidHexColor()
    {
        $this->assertSame('#112233', FormSettings::fromArray(['secondaryColor' => '#112233'])->secondaryColor);
        $this->assertSame('#123', FormSettings::fromArray(['secondaryColor' => '#123'])->secondaryColor);
    }

    /**
     * @unreleased
     */
    public function testSecondaryColorDefaultsWhenMissing()
    {
        $formSettings = FormSettings::fromArray([]);

        $this->assertSame('#f49420', $formSettings->secondaryColor);
    }
}

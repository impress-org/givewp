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
     * @since TBD
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
     * @since TBD
     */
    public function testDesignIdPreservesValidSlug()
    {
        $formSettings = FormSettings::fromArray([
            'designId' => 'multi-step-form-design',
        ]);

        $this->assertSame('multi-step-form-design', $formSettings->designId);
    }

    /**
     * @since TBD
     */
    public function testDesignIdIsNullWhenNotProvided()
    {
        $formSettings = FormSettings::fromArray([]);

        $this->assertNull($formSettings->designId);
    }
}

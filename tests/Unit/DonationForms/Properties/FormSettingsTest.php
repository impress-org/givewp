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
}

<?php

namespace Give\Tests\Unit\Helpers\Form;

use Give\Helpers\Form\Template;
use Give\Tests\TestCase;
use Give_Helper_Form;

class TemplateTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testSaveOptionsSavesSettingsWhenTemplateIsNotRegistered(): void
    {
        $templateId = 'not-a-registered-template';
        $settings = ['introduction' => ['headline' => 'Support our cause']];
        $form = Give_Helper_Form::create_simple_form();
        give()->form_meta->update_meta($form->ID, '_give_form_template', $templateId);

        Template::saveOptions($form->ID, $settings);

        $this->assertSame(
            $settings,
            give()->form_meta->get_meta($form->ID, "_give_{$templateId}_form_template_settings", true)
        );
    }
}

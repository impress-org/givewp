<?php

namespace Give\Tests\Unit\Views;

use Give\Tests\TestCase;
use Give\Views\IframeView;
use Give_Helper_Form;

class IframeViewTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testRenderReturnsEmptyStringWhenTemplateIsNotRegistered(): void
    {
        $form = Give_Helper_Form::create_simple_form();
        give()->form_meta->update_meta($form->ID, '_give_form_template', 'not-a-registered-template');

        $this->assertSame('', (new IframeView())->setFormId($form->ID)->render());
    }
}

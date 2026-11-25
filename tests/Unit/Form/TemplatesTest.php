<?php

namespace Give\Tests\Unit\Form;

use Give\Form\Templates;
use Give\Tests\TestCase;
use Give\Views\Form\Templates\Sequoia\Sequoia;
use Give_Helper_Form;

class TemplatesTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testGetTemplateReturnsNullWhenTemplateIdIsEmpty(): void
    {
        $templates = new Templates();
        $templates->load();

        $this->assertNull($templates->getTemplate(''));
    }

    /**
     * @since TBD
     */
    public function testGetTemplateReturnsNullWhenTemplateIdIsNotRegistered(): void
    {
        $templates = new Templates();
        $templates->load();

        $this->assertNull($templates->getTemplate('not-a-registered-template'));
    }

    /**
     * @since TBD
     */
    public function testGetTemplateReturnsNullWhenTemplateIdIsNotAString(): void
    {
        $templates = new Templates();
        $templates->load();

        $this->assertNull($templates->getTemplate(['sequoia']));
    }

    /**
     * @since TBD
     */
    public function testGetTemplateReturnsRegisteredTemplate(): void
    {
        global $post;

        $form = Give_Helper_Form::create_simple_form();
        $post = get_post($form->ID);

        $templates = new Templates();
        $templates->load();

        $this->assertInstanceOf(Sequoia::class, $templates->getTemplate('sequoia'));
    }
}

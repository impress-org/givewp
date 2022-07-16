<?php

namespace TestsNextGen\Unit\Framework\FormTemplates;

use Give\NextGen\Framework\FormTemplates\Contracts\FormTemplateInterface;
use Give\NextGen\Framework\FormTemplates\FormTemplate;
use TestsNextGen\TestCase;

/**
 * @unreleased
 */
class TestFormTemplate extends TestCase
{
    /**
     * @unreleased
     */
    public function testFormTemplateImplementsInterface()
    {
        $this->assertContains(
            FormTemplateInterface::class,
            class_implements(FormTemplate::class)
        );
    }
}

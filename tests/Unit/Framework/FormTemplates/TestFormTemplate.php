<?php

namespace TestsNextGen\Unit\Framework\FormDesigns;

use Give\NextGen\Framework\FormDesigns\Contracts\FormDesignInterface;
use Give\NextGen\Framework\FormDesigns\FormDesign;
use GiveTests\TestCase;

/**
 * @unreleased
 */
class TestFormDesign extends TestCase
{
    /**
     * @unreleased
     */
    public function testFormDesignImplementsInterface()
    {
        $this->assertContains(
            FormDesignInterface::class,
            class_implements(FormDesign::class)
        );
    }
}

<?php

namespace Give\Tests\Unit\Framework\FormDesigns;

use Give\NextGen\Framework\FormDesigns\Contracts\FormDesignInterface;
use Give\NextGen\Framework\FormDesigns\FormDesign;
use Give\Tests\TestCase;

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

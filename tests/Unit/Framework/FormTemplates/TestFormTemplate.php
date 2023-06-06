<?php

namespace Give\Tests\Unit\Framework\FormDesigns;

use Give\Framework\FormDesigns\Contracts\FormDesignInterface;
use Give\Framework\FormDesigns\FormDesign;
use Give\Tests\TestCase;

/**
 * @since 0.1.0
 */
class TestFormDesign extends TestCase
{
    /**
     * @since 0.1.0
     */
    public function testFormDesignImplementsInterface()
    {
        $this->assertContains(
            FormDesignInterface::class,
            class_implements(FormDesign::class)
        );
    }
}

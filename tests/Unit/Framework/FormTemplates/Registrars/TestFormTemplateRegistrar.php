<?php

namespace Give\Tests\Unit\Framework\FormDesigns\Registrars;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\FormDesigns\Exceptions\OverflowException;
use Give\Framework\FormDesigns\FormDesign;
use Give\Framework\FormDesigns\Registrars\FormDesignRegistrar;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class TestFormDesignRegistrar extends TestCase
{
    /** @var FormDesignRegistrar */
    public $registrar;

    /**
     * @since 3.0.0
     */
    public function setUp()
    {
        parent::setUp();
        $this->registrar = new FormDesignRegistrar();
    }

    /**
     * @since 3.0.0
     */
    public function testRegisterFormDesignShouldAddTemplate()
    {
        $this->registrar->registerDesign(MockFormDesign::class);

        $this->assertTrue($this->registrar->hasDesign(MockFormDesign::id()));
    }

    /**
     * @since 3.0.0
     */
    public function testUnRegisterFormDesignShouldRemoveTemplate()
    {
        $this->registrar->registerDesign(MockFormDesign::class);
        $this->registrar->unregisterDesign(MockFormDesign::id());

        $this->assertFalse($this->registrar->hasDesign(MockFormDesign::id()));
    }

    /**
     * @since 3.0.0
     */
    public function testShouldThrowInvalidArgumentExceptionIfNotExtendingFormDesignClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registrar->registerDesign(MockFormDesignDoesNotExtendFormDesign::class);
    }

    /**
     * @since 3.0.0
     */
    public function testShouldThrowOverFlowExceptionIfFormDesignIdIsTaken()
    {
        $this->expectException(OverflowException::class);
        $this->registrar->registerDesign(MockFormDesign::class);
        $this->registrar->registerDesign(MockFormDesign::class);
    }

    /**
     * @since 3.0.0
     */
    public function testGetFormDesignsShouldReturnArrayOfRegisteredTemplates()
    {
        $this->registrar->registerDesign(MockFormDesign::class);
        $this->assertSame(['mock-form-template' => MockFormDesign::class], $this->registrar->getDesigns());
    }
}

class MockFormDesign extends FormDesign
{

    public static function id(): string
    {
        return 'mock-form-template';
    }

    public static function name(): string
    {
        return 'Mock Form Template';
    }
}

class MockFormDesignDoesNotExtendFormDesign
{
    public static function id(): string
    {
        return 'mock-form-template';
    }

    public static function name(): string
    {
        return 'Mock Form Template';
    }
}

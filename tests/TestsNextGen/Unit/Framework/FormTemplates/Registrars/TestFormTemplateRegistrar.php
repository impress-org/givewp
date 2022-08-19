<?php

namespace TestsNextGen\Unit\Framework\FormTemplates\Registrars;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\NextGen\Framework\FormTemplates\Exceptions\OverflowException;
use Give\NextGen\Framework\FormTemplates\FormTemplate;
use Give\NextGen\Framework\FormTemplates\Registrars\FormTemplateRegistrar;
use GiveTests\TestCase;

/**
 * @unreleased
 */
class TestFormTemplateRegistrar extends TestCase
{
    /** @var FormTemplateRegistrar */
    public $registrar;

    /**
     * @unreleased
     */
    public function setUp()
    {
        parent::setUp();
        $this->registrar = new FormTemplateRegistrar();
    }

    /**
     * @unreleased
     */
    public function testRegisterFormTemplateShouldAddTemplate()
    {
        $this->registrar->registerTemplate(MockFormTemplate::class);

        $this->assertTrue($this->registrar->hasTemplate(MockFormTemplate::id()));
    }

    /**
     * @unreleased
     */
    public function testUnRegisterFormTemplateShouldRemoveTemplate()
    {
        $this->registrar->registerTemplate(MockFormTemplate::class);
        $this->registrar->unregisterTemplate(MockFormTemplate::id());

        $this->assertFalse($this->registrar->hasTemplate(MockFormTemplate::id()));
    }

    /**
     * @unreleased
     */
    public function testShouldThrowInvalidArgumentExceptionIfNotExtendingFormTemplateClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registrar->registerTemplate(MockFormTemplateDoesNotExtendFormTemplate::class);
    }

    /**
     * @unreleased
     */
    public function testShouldThrowOverFlowExceptionIfFormTemplateIdIsTaken()
    {
        $this->expectException(OverflowException::class);
        $this->registrar->registerTemplate(MockFormTemplate::class);
        $this->registrar->registerTemplate(MockFormTemplate::class);
    }

    /**
     * @unreleased
     */
    public function testGetFormTemplatesShouldReturnArrayOfRegisteredTemplates()
    {
        $this->registrar->registerTemplate(MockFormTemplate::class);
        $this->assertSame(['mock-form-template' => MockFormTemplate::class], $this->registrar->getTemplates());
    }
}

class MockFormTemplate extends FormTemplate {

    public static function id(): string
    {
        return 'mock-form-template';
    }

    public static function name(): string
    {
        return 'Mock Form Template';
    }
}

class MockFormTemplateDoesNotExtendFormTemplate {
    public static function id(): string
    {
        return 'mock-form-template';
    }

    public static function name(): string
    {
        return 'Mock Form Template';
    }
}

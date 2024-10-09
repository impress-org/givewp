<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\AddHoneyPotFieldToDonationForms;
use Give\Framework\FieldsAPI\DonationForm;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\FieldsAPI\Honeypot;
use Give\Framework\FieldsAPI\Section;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.16.2
 */
class TestAddHoneyPotFieldToDonationForms extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased updated to assert field attributes
     * @since 3.16.2
     * @throws NameCollisionException|EmptyNameException|TypeNotSupported
     */
    public function testShouldAddHoneyPotFieldToDonationForms(): void
    {
        $fieldName = 'myHoneypotFieldName';
        $formNode = new DonationForm('donation-form');
        $formNode->append(Section::make('section-1'), Section::make('section-2'), Section::make('section-3'));
        $action = new AddHoneyPotFieldToDonationForms();
        $action($formNode, $fieldName);

        /** @var Section $lastSection */
        $lastSection = $formNode->getNodeByName('section-3');

        /** @var Honeypot $field */
        $field = $lastSection->getNodeByName($fieldName);
        $this->assertNotNull($field);
        $this->assertInstanceOf(Honeypot::class, $field);
        $this->assertSame('My Honeypot Field Name', $field->getLabel());
        $this->assertTrue($field->hasRule('honeypot'));
        $this->assertFalse($field->shouldShowInAdmin());
        $this->assertFalse($field->shouldShowInReceipt());
    }
}

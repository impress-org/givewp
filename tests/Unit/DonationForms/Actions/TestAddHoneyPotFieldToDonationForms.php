<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\AddHoneyPotFieldToDonationForms;
use Give\Framework\FieldsAPI\Date;
use Give\Framework\FieldsAPI\DonationForm;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Exceptions\NameCollisionException;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\FieldsAPI\Section;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestAddHoneyPotFieldToDonationForms extends TestCase {
    use RefreshDatabase;

    /**
     * @unreleased
     * @throws NameCollisionException|EmptyNameException|TypeNotSupported
     */
    public function testShouldAddHoneyPotFieldToDonationForms() {
        $formNode = new DonationForm('donation-form');
        $formNode->append(Section::make('section-1'), Section::make('section-2'), Section::make('section-3'));
        $action = new AddHoneyPotFieldToDonationForms();
        $action($formNode);


        /** @var Section $lastSection */
        $lastSection = $formNode->getNodeByName('section-3');
        $this->assertNotNull($lastSection->getNodeByName('birthday'));
        $this->assertInstanceOf(Date::class, $lastSection->getNodeByName('birthday'));
    }
}

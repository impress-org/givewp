<?php

namespace Give\Tests\Feature\FormMigration;

use Give\FormMigration\FormMetaDecorator;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class TestFormMetaDecorator extends TestCase {
    use RefreshDatabase, LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testIsLastNameRequiredShouldReturnTrue(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => 'required',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isLastNameRequired());

        give_update_option( 'last_name_field_required', 'required' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => 'global',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertTrue($formMetaDecorator->isLastNameRequired());
    }

    /**
     * @unreleased
     */
    public function testIsLastNameRequiredShouldReturnFalse(): void
    {
        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => '',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isLastNameRequired());

        give_update_option( 'last_name_field_required', 'optional' );

        $formV2 = $this->createSimpleDonationForm(['meta' => [
            '_give_last_name_field_required' => 'global',
        ]]);

        $formMetaDecorator = new FormMetaDecorator($formV2);

        $this->assertFalse($formMetaDecorator->isLastNameRequired());
    }
}
